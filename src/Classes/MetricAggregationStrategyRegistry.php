<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Classes;

final class MetricAggregationStrategyRegistry
{
    public const METHOD_WEIGHTED_BY_METRIC = 'weighted_by_metric';

    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $strategies = [
        'position' => [
            'method' => self::METHOD_WEIGHTED_BY_METRIC,
            'source_metric_names' => ['position'],
            'weight_metric_names' => ['impressions', 'impressions_daily', 'page_media_view', 'post_media_view'],
            'required_period' => 'daily',
            'allowed_group_by_patterns' => [
                [],
                ['daily'],
                ['weekly'],
                ['monthly'],
                ['quarterly'],
                ['yearly'],
                ['query'],
                ['page'],
                ['country'],
                ['device'],
                ['query', 'page'],
                ['query', 'country'],
                ['query', 'device'],
                ['page', 'country'],
                ['page', 'device'],
                ['country', 'device'],
                ['query', 'page', 'country'],
                ['query', 'page', 'device'],
                ['query', 'country', 'device'],
                ['page', 'country', 'device'],
                ['query', 'page', 'country', 'device'],
                ['dimensions.*'],
                ['dimensions.*', 'dimensions.*'],
                ['dimensions.*', 'dimensions.*', 'dimensions.*'],
                ['dimensions.*', 'dimensions.*', 'dimensions.*', 'dimensions.*'],
            ],
        ],
    ];

    /**
     * @return array<string, mixed>|null
     */
    public static function resolve(string $metricName): ?array
    {
        $key = strtolower(trim($metricName));
        if ($key === '') {
            return null;
        }

        return self::$strategies[$key] ?? null;
    }

    /**
     * @param array<string, mixed> $strategy
     */
    public static function register(string $metricName, array $strategy): void
    {
        $key = strtolower(trim($metricName));
        if ($key === '') {
            return;
        }

        self::$strategies[$key] = $strategy;
    }
}

