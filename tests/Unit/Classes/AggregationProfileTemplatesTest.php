<?php

declare(strict_types=1);

namespace Tests\Unit\Classes;

use Anibalealvarezs\ApiDriverCore\Classes\AggregationProfileTemplates;
use PHPUnit\Framework\TestCase;

final class AggregationProfileTemplatesTest extends TestCase
{
    public function testOrganicPageFlowTemplateShape(): void
    {
        $profile = AggregationProfileTemplates::organicPageFlowProfile('facebook_organic');

        $this->assertSame('facebook_organic', $profile['channel']);
        $this->assertSame('page', $profile['asset_type']);
        $this->assertSame('flow', $profile['metric_nature']);
        $this->assertContains('daily', $profile['period_modes']);
        $this->assertArrayHasKey('filter_contract', $profile);
        $this->assertArrayHasKey('reducer_strategies', $profile);
    }

    public function testSearchCubeTemplateOverrideMergesRecursively(): void
    {
        $profile = AggregationProfileTemplates::searchCubeProfile(
            channel: 'google_search_console',
            overrides: [
                'metric_nature' => 'ratio',
                'filter_contract' => [
                    'dimensions.country' => ['=', 'in', 'not_in'],
                ],
            ]
        );

        $this->assertSame('ratio', $profile['metric_nature']);
        $this->assertSame(['=', 'in', 'not_in'], $profile['filter_contract']['dimensions.country']);
        $this->assertArrayHasKey('dimensions.device', $profile['filter_contract']);
    }
}

