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

    /**
     * @inheritdoc
     */
    public function validateConfig(array $config): array
    {
        return \Anibalealvarezs\ApiDriverCore\Services\ConfigSchemaRegistryService::hydrate(
            $this->getChannel(),
            'global',
            $config,
            $this->getConfigSchema()
        );
    }

    /**
     * @inheritdoc
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

    /**
     * Periodically check if the associated job is still active, or if it was stopped.
     * Throws an exception to naturally break long-running sync loops if the job is cancelled.
     * 
     * @param array $config
     * @return void
     * @throws \Exception
     */
    public function checkJobStatus(array $config): void
    {
        $jobId = $config['jobId'] ?? null;
        $manager = $config['manager'] ?? null;
        
        if (!$jobId || !$manager) {
            return;
        }

        try {
            if (class_exists('\Helpers\Helpers')) {
                \Helpers\Helpers::reconnectIfNeeded($manager);
            }
            
            if (class_exists('\Entities\Job') && class_exists('\Enums\JobStatus')) {
                $manager->clear(\Entities\Job::class); // Force a fresh read from DB
                $job = $manager->getRepository('\Entities\Job')->find($jobId);
                
                if ($job) {
                    $status = $job->getStatus();
                    $activeStatuses = [
                        \Enums\JobStatus::processing->value, 
                        \Enums\JobStatus::scheduled->value, 
                        \Enums\JobStatus::delayed->value
                    ];
                    
                    if (!in_array($status, $activeStatuses)) {
                        throw new \Exception("Job {$jobId} execution aborted (Status: {$status}).");
                    }
                }
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            // Ignore DBAL exceptions during periodic check to prevent false positives
        }
    }
}
