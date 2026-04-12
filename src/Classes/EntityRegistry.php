<?php

namespace Anibalealvarezs\ApiDriverCore\Classes;

class EntityRegistry
{
    private static array $paths = [];

    /**
     * Register one or more entity paths.
     * 
     * @param string|array $path
     */
    public static function register(string|array $path): void
    {
        if (is_array($path)) {
            self::$paths = array_unique(array_merge(self::$paths, $path));
        } else {
            if (!in_array($path, self::$paths)) {
                self::$paths[] = $path;
            }
        }
    }

    /**
     * Get all registered paths.
     * 
     * @return array
     */
    public static function getAll(): array
    {
        return self::$paths;
    }
}
