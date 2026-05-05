<?php

declare(strict_types=1);

namespace Tests\Unit\Classes;

use Anibalealvarezs\ApiDriverCore\Classes\AggregationProfileNormalizer;
use PHPUnit\Framework\TestCase;

final class AggregationProfileNormalizerTest extends TestCase
{
    public function testNormalizeProfileAppliesDefaultsAndAliases(): void
    {
        $profile = [
            'assetType' => 'post',
            'metricNature' => 'snapshot',
            'periodModes' => ['daily', 'daily', 'lifetime'],
            'groupPatterns' => [['post'], 'metricDate'],
            'filterContract' => [
                'post' => ['=', 'in', 'in', '!='],
                'channel' => '=',
            ],
            'reducerStrategies' => [
                'engagement_rate' => 'snapshot_delta',
            ],
        ];

        $normalized = AggregationProfileNormalizer::normalizeProfile('facebook_organic', $profile, 0);

        $this->assertSame('facebook_organic', $normalized['channel']);
        $this->assertSame('facebook_organic_aggregation_profile_1', $normalized['key']);
        $this->assertSame('Facebook Organic Aggregation Profile 1', $normalized['label']);
        $this->assertSame('post', $normalized['asset_type']);
        $this->assertSame('snapshot', $normalized['metric_nature']);
        $this->assertSame(['daily', 'lifetime'], $normalized['period_modes']);
        $this->assertSame([['post'], ['metricDate']], $normalized['group_patterns']);
        $this->assertSame(['eq', 'in', 'neq'], $normalized['filter_contract']['post']);
        $this->assertSame(['eq'], $normalized['filter_contract']['channel']);
        $this->assertSame('snapshot_delta', $normalized['reducer_strategies']['engagement_rate']);
    }

    public function testNormalizeProfilesSkipsInvalidRows(): void
    {
        $profiles = [
            ['key' => 'valid_one', 'channel' => 'google_search_console'],
            'invalid',
            ['key' => 'valid_two', 'channel' => 'google_search_console'],
        ];

        $normalized = AggregationProfileNormalizer::normalizeProfiles('google_search_console', $profiles);

        $this->assertCount(2, $normalized);
        $this->assertSame('valid_one', $normalized[0]['key']);
        $this->assertSame('valid_two', $normalized[1]['key']);
    }
}

