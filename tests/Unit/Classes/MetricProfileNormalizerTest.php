<?php

namespace Tests\Unit\Classes;

use Anibalealvarezs\ApiDriverCore\Classes\MetricProfileNormalizer;
use PHPUnit\Framework\TestCase;

class MetricProfileNormalizerTest extends TestCase
{
    public function testNormalizeProfilePopulatesDefaults()
    {
        $profile = [
            'key' => 'page_totals_profile',
            'metric_config' => [
                'required_fields' => ['channel', 'name', 'period', 'page'],
                'common_filters' => ['page', 'name'],
                'groupable_fields' => ['page']
            ]
        ];

        $normalized = MetricProfileNormalizer::normalizeProfile(
            defaultChannel: 'google_analytics',
            profile: $profile,
            index: 0
        );

        $this->assertEquals('page_totals_profile', $normalized['key']);
        $this->assertEquals('google_analytics', $normalized['channel']);
        $this->assertEquals('Page Totals Profile', $normalized['label']);
        
        $metricConfig = $normalized['metric_config'];
        $this->assertContains('channel', $metricConfig['required_fields']);
        $this->assertContains('page', $metricConfig['required_fields']);
        $this->assertContains('name', $metricConfig['common_filters']);
    }

    public function testNormalizeProfileAliasMappings()
    {
        // Tests that snake_case required fields are mapped to camelCase aliases
        $profile = [
            'key' => 'alias_test',
            'metric_config' => [
                'required_fields' => [
                    'channeled_account',
                    'channeled_campaign',
                    'channeled_ad_group',
                    'channeled_ad',
                    'dimension_set',
                    'plain_field'
                ]
            ]
        ];

        $normalized = MetricProfileNormalizer::normalizeProfile(
            defaultChannel: 'meta_ads',
            profile: $profile,
            index: 2
        );

        $required = $normalized['metric_config']['required_fields'];

        $this->assertContains('channeledAccount', $required);
        $this->assertContains('channeledCampaign', $required);
        $this->assertContains('channeledAdGroup', $required);
        $this->assertContains('channeledAd', $required);
        $this->assertContains('dimensionSet', $required);
        $this->assertContains('plain_field', $required);

        // Make sure the snake_case keys were replaced and not duplicated
        $this->assertNotContains('channeled_account', $required);
    }

    public function testNormalizeProfilesFiltersInvalidProfiles()
    {
        $profiles = [
            'profile1' => [
                'key' => 'profile_one',
                'channel' => 'google_search_console',
                'metric_config' => [
                    'required_fields' => ['query']
                ]
            ],
            'invalid_profile' => 'not_an_array_which_should_be_skipped',
            'profile2' => [
                'key' => 'profile_two',
                'metric_config' => [
                    'index_hints' => [
                        ['channel', 'name'],
                        'invalid_hint_not_array_should_be_skipped'
                    ]
                ]
            ]
        ];

        $normalizedList = MetricProfileNormalizer::normalizeProfiles('facebook_organic', $profiles);

        $this->assertCount(2, $normalizedList);

        $first = $normalizedList[0];
        $this->assertEquals('profile_one', $first['key']);
        $this->assertEquals('google_search_console', $first['channel']);
        
        $second = $normalizedList[1];
        $this->assertEquals('profile_two', $second['key']);
        $this->assertEquals('facebook_organic', $second['channel']);
        $this->assertCount(1, $second['metric_config']['index_hints']);
        $this->assertEquals(['channel', 'name'], $second['metric_config']['index_hints'][0]);
    }
}
