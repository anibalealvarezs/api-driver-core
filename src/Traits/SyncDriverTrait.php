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

    /**
     * @return array
     */
    public function getDateFilterMapping(): array
    {
        return [];
    }

    /**
     * @param array $credentials
     * @return void
     */
    public static function storeCredentials(array $credentials): void
    {
        // Default: No-op
    }

    /**
     * @return array
     */
    public static function getPublicResources(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getRoutes(): array
    {
        return [];
    }

    /**
     * @param mixed $seeder
     * @param array $config
     * @return void
     */
    public function seedDemoData(mixed $seeder, array $config = []): void
    {
        // Default: No-op
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        // Default: No-op
    }

    /**
     * @param array $channelConfig
     * @return array
     */
    public function prepareUiConfig(array $channelConfig): array
    {
        return [];
    }

    /**
     * @param mixed $entityManager
     * @param array $config
     * @return array
     */
    public function initializeEntities(mixed $entityManager, array $config = []): array
    {
        return ['initialized' => 0, 'skipped' => 0];
    }

    /**
     * @param mixed $entityManager
     * @param string $mode
     * @param array $config
     * @return array
     */
    public function reset(mixed $entityManager, string $mode = 'all', array $config = []): array
    {
        return ['cleared' => 0, 'mode' => $mode];
    }
}
