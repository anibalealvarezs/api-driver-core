<?php

namespace Anibalealvarezs\ApiDriverCore\Traits;

use Anibalealvarezs\ApiDriverCore\Enums\AssetCategory;
use Anibalealvarezs\ApiDriverCore\Services\ConfigSchemaRegistryService;

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
     * Get the display label for the provider (e.g. Meta, Google).
     * 
     * @return string
     */
    public static function getProviderLabel(): string
    {
        return static::getProviderName();
    }

    /**
     * Get the internal name/slug for the provider (e.g. meta, google).
     * 
     * @return string
     */
    public static function getProviderName(): string
    {
        $classParts = explode('\\', static::class);
        $provider = strtolower($classParts[0]);
        if ($provider === 'anibalealvarezs' && isset($classParts[1])) {
            return strtolower(str_replace('HubDriver', '', $classParts[1]));
        }
        return $provider;
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
     * @param bool $throwOnError
     * @return array
     */
    public function fetchAvailableAssets(bool $throwOnError = false): array
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
     * @param array $config
     * @return array
     */
    public function initializeEntities(array $config = []): array
    {
        return ['entities' => []];
    }

    /**
     * @param string $mode
     * @param array $config
     * @return array
     */
    public function reset(string $mode = 'all', array $config = []): array
    {
        return ['cleared' => 0, 'mode' => $mode];
    }

    /**
     * @param array $config
     * @return array
     */
    public function validateConfig(array $config): array
    {
        return ConfigSchemaRegistryService::hydrate(
            $this->getChannel(),
            'global',
            $config,
            $this->getConfigSchema()
        );
    }

    /**
     * @return array
     */
    public function getConfigSchema(): array
    {
        return [
            'global' => [
                'enabled' => false,
            ],
            'entity' => [
                'enabled' => true,
            ],
            'metrics' => [],
        ];
    }

    /**
     * @return array
     */
    public static function getInstanceRules(): array
    {
        return [
            'history_months' => 6,
            'entities_sync' => false,
            'recent_cron_hour' => 10,
            'recent_cron_minute' => 0,
        ];
    }

    /*
     *
     */
    public static function getPages(array $asset): array
    {
        return [];
    }

    /*
     *
     */
    public static function getChanneledAccounts(array $asset): array
    {
        return [];
    }

    /*
     *
     */
    public static function getPlatformId(array $asset, AssetCategory $category, string $context): string
    {
        return (string) ($asset['id'] ?? '');
    }

    /*
     *
     */
    public static function getCanonicalId(array $asset, AssetCategory $category, string $context): string
    {
        return static::getPlatformId($asset, $category, $context);
    }

    /**
     * @return array
     */
    public static function getAssetPatterns(): array
    {
        return [];
    }

    /**
     * @param AssetCategory $category
     * @return string|null
     */
    public static function getContextForCategory(AssetCategory $category): ?string
    {
        foreach (static::getAssetPatterns() as $key => $pattern) {
            $categories = (array) ($pattern['category'] ?? []);
            if (in_array($category, $categories)) {
                return $key;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getUpdatableCredentials(): array
    {
        return property_exists($this, 'updatableCredentials') ? $this->updatableCredentials : [];
    }
}
