<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Classes;

final class MetricProfileTemplates
{
    /**
     * @return array<string, mixed>
     */
    public static function pageTotals(string $channel, string $key, string $label): array
    {
        return [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'metric_config' => [
                'required_fields' => ['account', 'channeledAccount', 'page', 'dimensionSet', 'channel', 'name', 'period'],
                'common_filters' => ['page', 'name', 'period'],
                'groupable_fields' => ['page'],
                'index_hints' => [
                    ['channel', 'name', 'period', 'page'],
                    ['channel', 'page', 'dimensionSet', 'name', 'id'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pageQueryBreakdown(string $channel, string $key, string $label): array
    {
        return [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'metric_config' => [
                'required_fields' => ['account', 'channeledAccount', 'page', 'query', 'dimensionSet', 'channel', 'name', 'period'],
                'common_filters' => ['page', 'query', 'name', 'period'],
                'groupable_fields' => ['query'],
                'index_hints' => [
                    ['channel', 'page', 'query', 'dimensionSet', 'name', 'id'],
                    ['channel', 'name', 'period', 'query'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pageGeoDeviceBreakdown(string $channel, string $key, string $label): array
    {
        return [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'metric_config' => [
                'required_fields' => ['account', 'channeledAccount', 'page', 'country', 'device', 'dimensionSet', 'channel', 'name', 'period'],
                'common_filters' => ['page', 'country', 'device', 'name', 'period'],
                'groupable_fields' => ['country', 'device'],
                'index_hints' => [
                    ['channel', 'name', 'period', 'page', 'country', 'device'],
                    ['channel', 'page', 'dimensionSet', 'name', 'id'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pagePostBreakdown(string $channel, string $key, string $label): array
    {
        return [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'metric_config' => [
                'required_fields' => ['account', 'channeledAccount', 'page', 'post', 'dimensionSet', 'channel', 'name', 'period'],
                'common_filters' => ['page', 'post', 'name', 'period'],
                'groupable_fields' => ['post'],
                'index_hints' => [
                    ['channel', 'name', 'period', 'post'],
                    ['channel', 'page', 'post', 'dimensionSet', 'name'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function campaignBreakdown(string $channel, string $key, string $label): array
    {
        return [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'metric_config' => [
                'required_fields' => ['account', 'channeledAccount', 'campaign', 'channeledCampaign', 'dimensionSet', 'channel', 'name', 'period'],
                'common_filters' => ['account', 'channeledAccount', 'campaign', 'name', 'period'],
                'groupable_fields' => ['campaign'],
                'index_hints' => [
                    ['channel', 'name', 'period', 'campaign'],
                    ['channel', 'channeledAccount', 'campaign', 'dimensionSet', 'name'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function adGroupBreakdown(string $channel, string $key, string $label): array
    {
        return [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'metric_config' => [
                'required_fields' => ['account', 'channeledAccount', 'campaign', 'channeledCampaign', 'channeledAdGroup', 'dimensionSet', 'channel', 'name', 'period'],
                'common_filters' => ['account', 'channeledAccount', 'campaign', 'channeledAdGroup', 'name', 'period'],
                'groupable_fields' => ['campaign', 'channeledAdGroup'],
                'index_hints' => [
                    ['channel', 'name', 'period', 'channeledAdGroup'],
                    ['channel', 'channeledCampaign', 'channeledAdGroup', 'dimensionSet', 'name'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function adCreativeBreakdown(string $channel, string $key, string $label): array
    {
        return [
            'key' => $key,
            'channel' => $channel,
            'label' => $label,
            'metric_config' => [
                'required_fields' => ['account', 'channeledAccount', 'campaign', 'channeledCampaign', 'channeledAdGroup', 'channeledAd', 'creative', 'dimensionSet', 'channel', 'name', 'period'],
                'common_filters' => ['account', 'channeledAccount', 'campaign', 'channeledAdGroup', 'channeledAd', 'creative', 'name', 'period'],
                'groupable_fields' => ['campaign', 'channeledAdGroup', 'channeledAd', 'creative'],
                'index_hints' => [
                    ['channel', 'name', 'period', 'channeledAd'],
                    ['channel', 'channeledAdGroup', 'channeledAd', 'creative', 'dimensionSet', 'name'],
                ],
            ],
        ];
    }
}

