<?php

namespace Anibalealvarezs\ApiDriverCore\Classes;

class AssetRegistry
{
    private static array $patterns = [];

    public static function register(string $type, array $config): void
    {
        self::$patterns[$type] = $config;
    }

    public static function getAll(): array
    {
        return self::$patterns;
    }

    public static function findByHostname(string $hostname): ?array
    {
        foreach (self::$patterns as $type => $config) {
            foreach ($config['hostnames'] ?? [] as $pattern) {
                if (str_contains($hostname, $pattern)) {
                    return array_merge(['type' => $type], $config);
                }
            }
        }
        return null;
    }

    public static function findByType(string $type): ?array
    {
        return self::$patterns[$type] ?? null;
    }

    /**
     * Generates a canonical ID for an asset based on patterns.
     * Guaranteed universal consistency across providers and channels.
     */
    public static function getCanonicalId(string $url, string|int|null $platformId = null, string|null $type = null, string|null $hostname = null): string
    {
        $prefix = null;
        $urlIdRegex = null;

        // 1. Resolve asset configuration via Type or Hostname
        if ($type) {
            $assetPattern = self::findByType($type);
            if ($assetPattern) {
                $prefix = $assetPattern['prefix'] ?? null;
                $urlIdRegex = $assetPattern['url_id_regex'] ?? null;
            }
        }

        if (!$prefix && $hostname) {
            $assetPattern = self::findByHostname($hostname);
            if ($assetPattern) {
                $prefix = $assetPattern['prefix'] ?? null;
                $urlIdRegex = $assetPattern['url_id_regex'] ?? null;
            }
        }

        // 2. Universal Website Normalization (The "Spine")
        // If it's a website or unknown, we force the 'site:domain' pattern
        if (!$prefix || in_array($prefix, ['sc', 'site', 'web'])) {
            $normalizedHost = preg_replace('~^https?://(?:www\.)?~i', '', $url);
            $normalizedHost = strtolower(explode('/', $normalizedHost)[0]);
            return "site:domain:" . $normalizedHost;
        }

        // 3. Platform Asset Normalization
        $normalizedUrl = preg_replace('~^https?://(?:www\.)?~i', '', $url);
        $normalizedUrl = rtrim($normalizedUrl, '/');
        $normalizedUrl = strtolower($normalizedUrl);

        if (!$platformId && $urlIdRegex) {
            if (preg_match($urlIdRegex, $normalizedUrl, $matches)) {
                $platformId = $matches[1];
            }
        }

        if ($platformId) {
            return "$prefix:$platformId";
        }

        return "$prefix:" . md5($normalizedUrl);
    }
}
