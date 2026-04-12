<?php

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

use Symfony\Component\HttpFoundation\Response;
use DateTime;
use Anibalealvarezs\ApiDriverCore\Interfaces\AuthProviderInterface;

/**
 * Interface SyncDriverInterface
 * Defines the contract for a Data Channel Driver
 */
interface SyncDriverInterface
{
    /**
     * Get the common config key for this driver.
     * 
     * @return string|null
     */
    public static function getCommonConfigKey(): ?string;

    /**
     * Store credentials for this driver.
     * 
     * @param array $credentials
     * @return void
     */
    public static function storeCredentials(array $credentials): void;

    /**
     * Get the public resources exposed by this driver.
     * 
     * @return array
     */
    public static function getPublicResources(): array;

    /**
     * Get the display label for the channel.
     * 
     * @return string
     */
    public static function getChannelLabel(): string;

    /**
     * Get the display icon for the channel (letter or icon name).
     * 
     * @return string
     */
    public static function getChannelIcon(): string;

    /**
     * Get the routes served by this driver.
     * 
     * @return array
     */
    public static function getRoutes(): array;

    /**
     * Fetch available assets (sites, pages, accounts) from the provider.
     * 
     * @return array
     */
    public function fetchAvailableAssets(): array;

    /**
     * Validate the current authentication state.
     * 
     * @return array [success => bool, message => string, details => array]
     */
    public function validateAuthentication(): array;

    /**
     * Update/Validate configuration data for this driver.
     * 
     * @param array $newData
     * @param array $currentConfig
     * @return array The processed configuration to be persisted.
     */
    public function updateConfiguration(array $newData, array $currentConfig): array;

    /**
     * Authenticate the driver with a specific provider.
     */
    public function setAuthProvider(AuthProviderInterface $provider): void;

    /**
     * Perform the synchronization loop for a date range.
     */
    public function sync(DateTime $startDate, DateTime $endDate, array $config = []): Response;

    /**
     * Get the channel identifier (e.g. google_search_console).
     */
    public function getChannel(): string;

    /**
     * Get the raw API client instance for the driver.
     *
     * @param array $config
     * @return mixed
     */
    public function getApi(array $config = []): mixed;

    /**
     * Get the current AuthProvider instance.
     *
     * @return AuthProviderInterface|null
     */
    public function getAuthProvider(): ?AuthProviderInterface;

    /**
     * Get the list of environment variables that are updatable for this driver.
     *
     * @return array
     */
    public function getUpdatableCredentials(): array;

    /**
     * Get the configuration schema for the driver.
     *
     * @return array
     */
    public function getConfigSchema(): array;

    /**
     * Validate and prepare the configuration for the driver.
     * Use this to apply defaults and normalize structures.
     *
     * @param array $config
     * @return array
     */
    public function validateConfig(array $config): array;

    /**
     * Seed realistic demo data for this driver.
     * 
     * @param SeederInterface $seeder The seeder utility (command or service)
     * @param array $config
     * @return void
     */
    public function seedDemoData(SeederInterface $seeder, array $config = []): void;

    /**
     * Initialize driver-specific configurations in the host (e.g. Repository relations).
     */
    public function boot(): void;

    /**
     * Get the asset identification patterns for this driver.
     * Used to generate canonical IDs from URLs or hostnames.
     *
     * @return array
     */
    public function getAssetPatterns(): array;

    /**
     * Prepare UI-specific configuration mapping for this channel.
     *
     * @param array $channelConfig The raw channel configuration from YAML.
     * @return array key-value pairs to be injected into the UI state.
     */
    public function prepareUiConfig(array $channelConfig): array;

    /**
     * Initialize channel-specific entities (Pages, Accounts, etc.) in the database.
     *
     * @param mixed $entityManager The host's Entity Manager.
     * @param array $config The channel configuration.
     * @return array Summary of what was initialized [initialized => int, skipped => int].
     */
    public function initializeEntities(mixed $entityManager, array $config = []): array;

    /**
     * Clear channel-specific data from the database.
     *
     * @param mixed $entityManager The host's Entity Manager.
     * @param string $mode The reset mode: 'entities', 'metrics', or 'all' (default).
     * @param array $config Optional configuration for the reset.
     * @return array Summary of what was cleared.
     */
    public function reset(mixed $entityManager, string $mode = 'all', array $config = []): array;

    /**
     * Get the supported page types for this driver.
     *
     * @return array [type_key => label]
     */
    public static function getPageTypes(): array;

    /**
     * Get the supported account types for this driver.
     *
     * @return array [type_key => label]
     */
    public static function getAccountTypes(): array;

    /**
     * Get the entity paths for this driver.
     *
     * @return array
     */
    /**
     * Get the entity paths for this driver.
     *
     * @return array
     */
    public static function getEntityPaths(): array;

    /**
     * Get the date filter mapping for this driver.
     * Returns an array like ['start' => 'createdAtMin', 'end' => 'createdAtMax']
     * or empty array if not supported.
     *
     * @return array
     */
    public function getDateFilterMapping(): array;
}
