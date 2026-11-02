<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

/**
 * Interface LiveWindowProviderInterface
 *
 * Implemented by drivers capable of executing live, ad-hoc window queries directly
 * against the provider API for non-additive metrics (e.g. unique users, reach, active users)
 * that cannot be computed by summing pre-aggregated daily records across arbitrary timeframes.
 */
interface LiveWindowProviderInterface
{
    /**
     * Determine if a given metric supports live window querying for this channel.
     */
    public static function supportsLiveWindow(string $metric): bool;

    /**
     * Execute an on-demand live window query against the provider API.
     *
     * @param array<string, mixed> $config Channel credentials and asset configuration.
     * @param string $platformId The platform asset identifier (e.g. GA4 property ID).
     * @param array<int, string> $metrics Raw or canonical metrics to query.
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @param array<int, string> $dimensions Optional dimension breakdowns (e.g. ['deviceCategory', 'countryId']).
     * @param array<string, mixed> $filters Optional dimension filter predicates.
     * @return array<int, array<string, mixed>> Normalized result rows.
     */
    public function fetchLiveWindow(
        array $config,
        string $platformId,
        array $metrics,
        string $startDate,
        string $endDate,
        array $dimensions = [],
        array $filters = []
    ): array;
}
