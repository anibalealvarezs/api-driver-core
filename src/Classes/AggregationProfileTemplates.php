<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Classes;

final class AggregationProfileTemplates
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function organicPageFlowProfile(
        string $channel,
        string $key = 'organic_page_flow',
        string $label = 'Organic Page Flow',
        array $overrides = []
    ): array {
        return self::merge($overrides, [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'asset_type' => 'page',
            'metric_nature' => 'flow',
            'period_modes' => ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'],
            'group_patterns' => [
                ['metricDate'],
                ['metricDate', 'page'],
                ['page'],
            ],
            'filter_contract' => [
                'page' => ['=', 'in'],
                'channel' => ['='],
                'metricDate' => ['between', '>=', '<='],
            ],
            'reducer_strategies' => [
                '*' => 'sum',
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function organicPostMixedProfile(
        string $channel,
        string $key = 'organic_post_mixed',
        string $label = 'Organic Post Mixed',
        array $overrides = []
    ): array {
        return self::merge($overrides, [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'asset_type' => 'post',
            'metric_nature' => 'snapshot',
            'period_modes' => ['daily', 'lifetime', 'snapshot_delta'],
            'group_patterns' => [
                ['post'],
                ['page', 'post'],
                ['metricDate', 'post'],
            ],
            'filter_contract' => [
                'post' => ['=', 'in'],
                'page' => ['=', 'in'],
                'channel' => ['='],
                'metricDate' => ['between', '>=', '<='],
            ],
            'reducer_strategies' => [
                '*' => 'latest_snapshot',
                'engagement_rate' => 'snapshot_delta',
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function adsHierarchyProfile(
        string $channel,
        string $key = 'ads_hierarchy',
        string $label = 'Ads Hierarchy',
        array $overrides = []
    ): array {
        return self::merge($overrides, [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'asset_type' => 'ad',
            'metric_nature' => 'weighted_ratio',
            'period_modes' => ['daily', 'weekly', 'monthly'],
            'group_patterns' => [
                ['campaign'],
                ['campaign', 'adset'],
                ['campaign', 'adset', 'ad'],
                ['metricDate', 'campaign'],
            ],
            'filter_contract' => [
                'campaign' => ['=', 'in'],
                'adset' => ['=', 'in'],
                'ad' => ['=', 'in'],
                'channel' => ['='],
                'metricDate' => ['between', '>=', '<='],
            ],
            'reducer_strategies' => [
                '*' => 'sum',
                'ctr' => 'weighted_by_metric',
                'cpc' => 'weighted_by_metric',
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function searchCubeProfile(
        string $channel,
        string $key = 'search_cube',
        string $label = 'Search Cube',
        array $overrides = []
    ): array {
        return self::merge($overrides, [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'asset_type' => 'page',
            'metric_nature' => 'weighted_ratio',
            'period_modes' => ['daily'],
            'group_patterns' => [
                ['dimensions.country'],
                ['dimensions.device'],
                ['dimensions.country', 'dimensions.device'],
                ['dimensions.query'],
                ['dimensions.page'],
            ],
            'filter_contract' => [
                'dimensions.country' => ['=', 'in'],
                'dimensions.device' => ['=', 'in'],
                'dimensions.query' => ['=', 'in', 'like'],
                'dimensions.page' => ['=', 'in'],
                'channel' => ['='],
                'metricDate' => ['between', '>=', '<='],
            ],
            'reducer_strategies' => [
                '*' => 'sum',
                'position' => 'weighted_by_metric',
            ],
            'default_filters' => [],
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function flowCampaignProfile(
        string $channel,
        string $key = 'flow_campaign',
        string $label = 'Flow & Campaign Analysis',
        array $overrides = []
    ): array {
        return self::merge($overrides, [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'asset_type' => 'campaign',
            'metric_nature' => 'flow',
            'period_modes' => ['daily', 'weekly', 'monthly'],
            'group_patterns' => [
                ['campaign'],
                ['flow'],
                ['flow', 'message'],
                ['metricDate', 'campaign'],
                ['metricDate', 'flow'],
            ],
            'filter_contract' => [
                'campaign' => ['=', 'in'],
                'flow' => ['=', 'in'],
                'message' => ['=', 'in'],
                'channel' => ['='],
                'metricDate' => ['between', '>=', '<='],
            ],
            'reducer_strategies' => [
                '*' => 'sum',
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function storeProfile(
        string $channel,
        string $key = 'store_performance',
        string $label = 'Store Performance',
        array $overrides = []
    ): array {
        return self::merge($overrides, [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'asset_type' => 'store',
            'metric_nature' => 'flow',
            'period_modes' => ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'],
            'group_patterns' => [
                ['metricDate'],
                ['vendor'],
            ],
            'filter_contract' => [
                'vendor' => ['=', 'in'],
                'channel' => ['='],
                'metricDate' => ['between', '>=', '<='],
            ],
            'reducer_strategies' => [
                '*' => 'sum',
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @param array<string, mixed> $base
     * @return array<string, mixed>
     */
    private static function merge(array $overrides, array $base): array
    {
        return array_replace_recursive($base, $overrides);
    }
}

