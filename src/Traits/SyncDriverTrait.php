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
}
