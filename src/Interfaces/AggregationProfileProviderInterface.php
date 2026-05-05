<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

/**
 * Exposes semantic aggregation capability profiles for a driver/channel.
 *
 * Profiles are database-agnostic capability declarations consumed by planners
 * and orchestrators to validate aggregation requests before SQL rendering.
 */
interface AggregationProfileProviderInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getAggregationProfiles(): array;
}

