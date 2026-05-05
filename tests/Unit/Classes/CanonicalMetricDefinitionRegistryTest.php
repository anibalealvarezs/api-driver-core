<?php

declare(strict_types=1);

namespace Tests\Unit\Classes;

use Anibalealvarezs\ApiDriverCore\Classes\CanonicalMetricDefinitionRegistry;
use PHPUnit\Framework\TestCase;

final class CanonicalMetricDefinitionRegistryTest extends TestCase
{
    public function testNormalizeReturnsCanonicalMetricForKnownAliases(): void
    {
        $this->assertSame('conversions', CanonicalMetricDefinitionRegistry::normalize('results'));
        $this->assertSame('cost_per_conversion', CanonicalMetricDefinitionRegistry::normalize('cost_per_result'));
        $this->assertSame('conversion_rate', CanonicalMetricDefinitionRegistry::normalize('result_rate'));
        $this->assertSame('roas_purchase', CanonicalMetricDefinitionRegistry::normalize('purchase_roas'));
    }

    public function testResolveDefinitionIncludesCanonicalMetricKey(): void
    {
        $definition = CanonicalMetricDefinitionRegistry::resolveDefinition('purchase_roas');

        $this->assertIsArray($definition);
        $this->assertSame('roas_purchase', $definition['canonical_metric']);
        $this->assertSame('ratio', $definition['category']);
    }

    public function testResolveInputMarksLegacyAliasMetadata(): void
    {
        $resolved = CanonicalMetricDefinitionRegistry::resolveInput('results');

        $this->assertSame('results', $resolved['requested_metric']);
        $this->assertSame('conversions', $resolved['canonical_metric']);
        $this->assertFalse($resolved['is_canonical']);
        $this->assertTrue($resolved['is_legacy_alias']);
        $this->assertSame('conversions', $resolved['alias_target']);
        $this->assertNull($resolved['deprecation']);
    }

    public function testResolveInputMarksAmbiguousDeprecatedMetric(): void
    {
        $resolved = CanonicalMetricDefinitionRegistry::resolveInput('actions');

        $this->assertSame('actions', $resolved['requested_metric']);
        $this->assertNull($resolved['canonical_metric']);
        $this->assertFalse($resolved['is_canonical']);
        $this->assertFalse($resolved['is_legacy_alias']);
        $this->assertSame('ambiguous_metric_alias', $resolved['deprecation']['reason']);
        $this->assertNull($resolved['deprecation']['replacement']);
    }

    public function testRegisterAllowsExtendingCanonicalDictionary(): void
    {
        CanonicalMetricDefinitionRegistry::register(
            canonicalMetric: 'engagements_total',
            definition: [
                'label' => 'Total Engagements',
                'category' => 'base',
            ],
            aliases: ['actions_total', 'custom_interactions_count']
        );

        $this->assertTrue(CanonicalMetricDefinitionRegistry::isCanonical('engagements_total'));
        $this->assertSame('engagements_total', CanonicalMetricDefinitionRegistry::normalize('actions_total'));
        $this->assertSame('engagements_total', CanonicalMetricDefinitionRegistry::normalize('custom_interactions_count'));
    }
}

