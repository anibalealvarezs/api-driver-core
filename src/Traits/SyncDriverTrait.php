<?php

namespace Anibalealvarezs\ApiDriverCore\Traits;

trait SyncDriverTrait
{
    /**
     * Get the common config key for this driver.
     * 
     * @return string|null
     */
    public static function getCommonConfigKey(): ?string
    {
        return null;
    }

    /**
     * Get the display icon for the channel (letter or icon name).
     * 
     * @return string
     */
    public static function getChannelIcon(): string
    {
        return substr(static::getChannelLabel(), 0, 1);
    }

    /**
     * @return array
     */
    public static function getPageTypes(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getAccountTypes(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getEntityPaths(): array
    {
        return [];
    }
}
