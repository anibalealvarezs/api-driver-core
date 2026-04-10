<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Helpers;

use Predis\Client;
use Predis\ClientInterface;
use Symfony\Component\Yaml\Yaml;

class Helpers
{
    private static ?ClientInterface $redisClient = null;
    private static ?array $channelsConfig = null;

    public static function getRedisClient(): ClientInterface
    {
        if (self::$redisClient !== null) {
            return self::$redisClient;
        }

        $host = getenv('REDIS_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('REDIS_PORT') ?: 6379);
        $pass = getenv('REDIS_PASSWORD') ?: null;

        self::$redisClient = new Client([
            'scheme' => 'tcp',
            'host'   => $host,
            'port'   => $port,
            'password' => $pass,
        ]);

        return self::$redisClient;
    }

    public static function getChannelsConfig(): array
    {
        if (self::$channelsConfig !== null) {
            return self::$channelsConfig;
        }

        $config = [];
        $configDir = getenv('CONFIG_DIR');
        if ($configDir) {
            $filePath = $configDir . '/channels.yaml';
            if (file_exists($filePath)) {
                $yamlConfig = Yaml::parseFile($filePath);
                if (is_array($yamlConfig)) {
                    $config = $yamlConfig;
                }
            }
        }

        if ($envChannelsJson = getenv('CHANNELS_CONFIG')) {
            $envChannels = json_decode($envChannelsJson, true);
            if (is_array($envChannels)) {
                $config = array_replace_recursive($config, $envChannels);
            }
        }

        return self::$channelsConfig = $config;
    }

    public static function resetConfigs(): void
    {
        self::$channelsConfig = null;
        self::$redisClient = null;
    }

    public static function isAssetFiltered(?string $name, array $config, ?string $typeKey = null): bool
    {
        if (!$name) {
            return false;
        }

        $typeSpecific = $typeKey ? ($config[$typeKey] ?? []) : [];
        
        $include = $typeSpecific['cache_include'] ?? $config['cache_include'] ?? null;
        $exclude = $typeSpecific['cache_exclude'] ?? $config['cache_exclude'] ?? null;

        if ($include) {
            $matched = (str_contains($name, $include) || (@preg_match($include, "") !== false && preg_match($include, $name)));
            if (!$matched) {
                return true; // Filtered out because it's NOT included
            }
        }

        if ($exclude) {
            $matched = (str_contains($name, $exclude) || (@preg_match($exclude, "") !== false && preg_match($exclude, $name)));
            if ($matched) {
                return true; // Filtered out because it IS excluded
            }
        }

        return false;
    }
}
