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
        return strtolower($classParts[0]);
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
        $manager = $config['manager'] ?? null;
        if (!$manager instanceof \Doctrine\ORM\EntityManagerInterface) {
            return ['error' => 'No EntityManager provided'];
        }

        $identityMapper = $config['identityMapper'] ?? null;
        $results = ['initialized' => 0, 'skipped' => 0];

        try {
            $patterns = $this->getAssetPatterns();
            $channel = $this->getChannel();
            $chanConfig = $config['channels'][$channel] ?? ($config ?? []);

            // Identify Group Account
            $accountRepo = $manager->getRepository(\Entities\Analytics\Account::class);
            $commonKey = self::getCommonConfigKey();
            $defaultGroupName = method_exists($this, 'getChannelLabel') ? $this->getChannelLabel() : "Default Group";
            $groupName = $chanConfig['accounts_group_name'] ?? ($config['channels'][$commonKey]['accounts_group_name'] ?? $defaultGroupName);

            $accountEntity = $accountRepo->findOneBy(['name' => $groupName]);
            if (!$accountEntity) {
                $accountEntity = new \Entities\Analytics\Account();
                $accountEntity->addName($groupName);
                $manager->persist($accountEntity);
                $manager->flush($accountEntity);
            }

            $isUrlBasedProvider = ($channel === 'google_search_console' || str_contains($channel, 'search_console'));

            foreach ($patterns as $assetKey => $pattern) {
                $configKey = $pattern['key'] ?? $assetKey;
                $assets = $chanConfig[$configKey] ?? [];

                if (empty($assets)) continue;

                $typeMark = $pattern['type'] ?? null;
                if (!$typeMark) continue;

                foreach ($assets as $asset) {
                    $id = (string)($asset['id'] ?? ($asset['url'] ?? ''));
                    if (!$id) continue;

                    if ($isUrlBasedProvider && filter_var($id, FILTER_VALIDATE_URL)) {
                        $id = md5(rtrim($id, '/'));
                    }

                    $name = $asset['name'] ?? $asset['title'] ?? ("Asset " . $id);
                    $channelEntity = $manager->getRepository(\Entities\Analytics\Channel::class)->findOneBy(['name' => $channel]);
                    if (!$channelEntity) continue;

                    $dbChanneled = $manager->getRepository(\Entities\Analytics\Channeled\ChanneledAccount::class)->findOneBy([
                        'platformId' => $id, 
                        'channel' => $channelEntity
                    ]);

                    if (!$dbChanneled) {
                        $dbChanneled = new \Entities\Analytics\Channeled\ChanneledAccount();
                        $dbChanneled->addPlatformId($id)
                            ->addAccount($accountEntity)
                            ->addType($typeMark)
                            ->addChannel($channelEntity)
                            ->addName($name)
                            ->addPlatformCreatedAt(isset($asset['created_at']) ? new \DateTime($asset['created_at']) : null)
                            ->addData([]);
                        $manager->persist($dbChanneled);
                        $results['initialized']++;
                    } elseif ($dbChanneled->getName() !== $name) {
                        $dbChanneled->addName($name);
                        $manager->persist($dbChanneled);
                    } else {
                        $results['skipped']++;
                    }

                    // Children
                    if (isset($pattern['children'])) {
                        foreach ($pattern['children'] as $childKey => $childPattern) {
                            $childId = (string)($asset[$childPattern['id_key']] ?? '');
                            if (!$childId) continue;

                            $childName = $asset[$childPattern['name_key']] ?? $name;
                            $childType = $childPattern['type'];

                            $dbChild = $manager->getRepository(\Entities\Analytics\Channeled\ChanneledAccount::class)->findOneBy([
                                'platformId' => $childId, 
                                'channel' => $channelEntity
                            ]);
                            if (!$dbChild) {
                                $dbChild = new \Entities\Analytics\Channeled\ChanneledAccount();
                                $dbChild->addPlatformId($childId)
                                    ->addAccount($accountEntity)
                                    ->addType($childType)
                                    ->addChannel($channelEntity)
                                    ->addName($childName)
                                    ->addPlatformCreatedAt(isset($asset['created_at']) ? new \DateTime($asset['created_at']) : null)
                                    ->addData([]);
                                $manager->persist($dbChild);
                                $results['initialized']++;
                            }
                        }
                    }

                    // Page Entity
                    if ($typeMark === 'gsc_site' || $typeMark === 'facebook_page') {
                        $canonicalId = \Helpers\Helpers::getCanonicalPageId($asset['url'] ?? $id, null, 'website');
                        $dbPage = $manager->getRepository(\Entities\Analytics\Page::class)->findOneBy(['canonicalId' => $canonicalId]);
                        if (!$dbPage) {
                            $dbPage = new \Entities\Analytics\Page();
                            $dbPage->addCanonicalId($canonicalId)
                                ->addUrl($asset['url'] ?? $id)
                                ->addTitle($name)
                                ->addAccount($accountEntity)
                                ->addPlatformId($id)
                                ->addHostname($asset['hostname'] ?? parse_url($asset['url'] ?? $id, PHP_URL_HOST))
                                ->addData($asset);
                            $manager->persist($dbPage);
                        }
                    }
                }
            }
            $manager->flush();
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }

        return $results;
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

}
