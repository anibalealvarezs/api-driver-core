<?php

namespace Anibalealvarezs\ApiDriverCore\Services;

use Anibalealvarezs\ApiDriverCore\Drivers\DriverFactory;
use Anibalealvarezs\ApiDriverCore\Helpers\Helpers;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Predis\ClientInterface;

class CacheStrategyService
{
    private const TTL_HISTORICAL = 604800; // 7 days in seconds
    private const TTL_RECENT = 86400; // 24 hours in seconds
    private const RECENT_THRESHOLD = '-3 days'; // Matches InstanceGeneratorService

    /**
     * @return ClientInterface
     */
    private static function getRedis(): ClientInterface
    {
        return Helpers::getRedisClient();
    }

    /**
     * Determine if an aggregation request should be cached based on channel toggle.
     *
     * @param string $channelKey
     * @return bool
     */
    public static function isCacheable(string $channelKey): bool
    {
        $normalizedChannel = strtolower(trim($channelKey));

        try {
            $allConfigs = Helpers::getChannelsConfig();
            $config = $allConfigs[$channelKey] ?? $allConfigs[$normalizedChannel] ?? [];

            // Case-insensitive fallback in case channel keys differ only by casing.
            if ($config === []) {
                foreach ($allConfigs as $key => $candidate) {
                    if (strtolower((string) $key) === $normalizedChannel) {
                        $config = $candidate;
                        break;
                    }
                }
            }

            if (array_key_exists('cache_aggregations', $config)) {
                return (bool) $config['cache_aggregations'];
            }

            // Keep driver validation as optional enrichment, not as a hard requirement.
            $driverConfig = DriverFactory::getChannelConfig($channelKey);
            if ($driverConfig === [] && $normalizedChannel !== $channelKey) {
                $driverConfig = DriverFactory::getChannelConfig($normalizedChannel);
            }

            $parent = $driverConfig['parent'] ?? null;
            if (is_string($parent) && $parent !== '') {
                $parentConfig = $allConfigs[$parent] ?? $allConfigs[strtolower($parent)] ?? [];
                if (array_key_exists('cache_aggregations', $parentConfig)) {
                    return (bool) $parentConfig['cache_aggregations'];
                }
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns 'historical' or 'recent' based on the request's end date.
     *
     * @param DateTimeInterface|string $endDate
     * @return string
     */
    public static function getTargetCacheType(DateTimeInterface|string $endDate): string
    {
        try {
            $end = ($endDate instanceof DateTimeInterface) ? $endDate : new DateTimeImmutable($endDate);
            $threshold = new DateTimeImmutable('today ' . self::RECENT_THRESHOLD);

            return ($end < $threshold) ? 'historical' : 'recent';
        } catch (Exception $e) {
            return 'recent'; // Default to safer/shorter TTL if date parsing fails
        }
    }

    /**
     * Get cached aggregation data.
     *
     * @param string $key
     * @param string $type
     * @return array|null
     */
    public static function get(string $key, string $type = 'historical'): ?array
    {
        $redis = self::getRedis();
        $data = $redis->get($key);
        if ($data) {
            // Sliding window: refresh TTL on access
            $ttl = ($type === 'recent') ? self::TTL_RECENT : self::TTL_HISTORICAL;
            $redis->expire($key, $ttl);

            return json_decode($data, true);
        }

        return null;
    }

    /**
     * Set aggregation data in cache.
     *
     * @param string $key
     * @param array $data
     * @param string $type
     */
    public static function set(string $key, array $data, string $type = 'historical'): void
    {
        $ttl = ($type === 'recent') ? self::TTL_RECENT : self::TTL_HISTORICAL;
        self::getRedis()->setex($key, $ttl, json_encode($data));
    }

    /**
     * Clear all aggregation cache for a specific channel.
     *
     * @param string $channelKey
     */
    public static function clearChannel(string $channelKey): void
    {
        $normalized = trim($channelKey);
        if ($normalized === '') {
            return;
        }

        $candidates = array_values(array_unique([
            $normalized,
            strtolower($normalized),
        ]));

        $patterns = array_map(static fn(string $candidate): string => "agg:{$candidate}:*", $candidates);
        self::clearByPatterns($patterns);
    }

    /**
     * Clear only recent aggregation cache for a specific channel.
     * Usually called after a sync job finishes.
     *
     * @param string $channelKey
     */
    public static function clearRecent(string $channelKey): void
    {
        $normalized = trim($channelKey);
        if ($normalized === '') {
            return;
        }

        $candidates = array_values(array_unique([
            $normalized,
            strtolower($normalized),
        ]));

        $patterns = array_map(static fn(string $candidate): string => "agg:{$candidate}:recent:*", $candidates);
        self::clearByPatterns($patterns);
    }

    /**
     * @param array<int, string> $patterns
     */
    private static function clearByPatterns(array $patterns): void
    {
        $redis = self::getRedis();
        $allKeys = [];

        foreach ($patterns as $pattern) {
            $cursor = '0';
            do {
                $scanResult = $redis->scan($cursor, ['MATCH' => $pattern, 'COUNT' => 1000]);
                if (!is_array($scanResult) || count($scanResult) !== 2) {
                    break;
                }

                $cursor = (string) $scanResult[0];
                $keys = (array) ($scanResult[1] ?? []);
                foreach ($keys as $key) {
                    if (is_string($key) && $key !== '') {
                        $allKeys[$key] = true;
                    }
                }
            } while ($cursor !== '0');
        }

        if ($allKeys === []) {
            return;
        }

        self::deleteKeys(array_keys($allKeys));
    }

    /**
     * @param array<int, string> $keys
     */
    private static function deleteKeys(array $keys): void
    {
        $redis = self::getRedis();

        foreach (array_chunk($keys, 500) as $chunk) {
            $redis->del($chunk);
        }
    }

    /**
     * Generate a unique cache key based on parameters and cache type.
     *
     * @param string $channelKey
     * @param array $params
     * @param string $type
     * @return string
     */
    public static function generateKey(string $channelKey, array $params, string $type = 'historical'): string
    {
        $normalized = self::normalizeForHash($params);
        $hash = md5(serialize($normalized));

        return "agg:{$channelKey}:{$type}:{$hash}";
    }

    /**
     * Normalize arrays recursively to produce deterministic cache keys.
     * Associative arrays are key-sorted; indexed arrays preserve order.
     *
     * @param mixed $value
     * @return mixed
     */
    private static function normalizeForHash(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $normalized = array_map(function ($item) {
            return self::normalizeForHash($item);
        }, $value);

        if (array_keys($normalized) !== range(0, count($normalized) - 1)) {
            ksort($normalized);
        }

        return $normalized;
    }
}
