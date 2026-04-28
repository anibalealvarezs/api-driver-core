<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

/**
 * Exposes semantic metric profiles for a driver/channel.
 *
 * Profiles describe business-level metric identity and aggregation patterns.
 * They are intentionally database-agnostic so APIs Hub can translate them into
 * physical column mappings and Doctrine-managed index candidates.
 */
interface MetricProfileProviderInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getMetricProfiles(): array;
}

