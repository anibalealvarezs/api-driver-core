<?php

namespace Anibalealvarezs\ApiDriverCore\Classes;

class PageTypeRegistry
{
    private static array $types = [];

    /**
     * Register one or more page types.
     * 
     * @param string|array $type Key of the type or array of [key => label]
     * @param string|null $label Human-readable label
     */
    public static function register(string|array $type, ?string $label = null): void
    {
        if (is_array($type)) {
            self::$types = array_merge(self::$types, $type);
        } else {
            self::$types[$type] = $label ?: $type;
        }
    }

    /**
     * Get all registered types.
     * 
     * @return array [key => label]
     */
    public static function getAll(): array
    {
        return self::$types;
    }

    /**
     * Get a specific type label.
     * 
     * @param string $type
     * @return string|null
     */
    public static function getLabel(string $type): ?string
    {
        return self::$types[$type] ?? null;
    }
}
