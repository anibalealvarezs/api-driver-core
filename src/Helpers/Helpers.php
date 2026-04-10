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

        $configDir = getenv('CONFIG_DIR');
        if (!$configDir) {
            return [];
        }

        $filePath = $configDir . '/channels.yaml';
        if (file_exists($filePath)) {
            $config = Yaml::parseFile($filePath);
            self::$channelsConfig = is_array($config) ? $config : [];
        } else {
            self::$channelsConfig = [];
        }

        return self::$channelsConfig;
    }
}
