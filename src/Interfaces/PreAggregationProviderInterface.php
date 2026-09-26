<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

/**
 * Exposes atomic pre-aggregation capability contracts for a driver/channel.
 *
 * Drivers implementing this interface declare how their raw, atomic entity payloads
 * (e.g. order events, click/open activity logs, transaction records) are mapped
 * and reduced into daily (1-day minimal grain) metrics.
 *
 * The driver remains completely decoupled from physical database schemas,
 * declaring purely semantic derivation rules and reducer strategies.
 */
interface PreAggregationProviderInterface
{
    /**
     * Declares the pre-aggregation rules mapping atomic entities/events to daily metrics.
     *
     * Example structure:
     * [
     *     'orders' => [
     *         'source_entity' => 'channeled_orders',
     *         'metrics' => [
     *             'orders_count' => ['field' => 'platform_id', 'reducer' => 'count_distinct'],
     *             'revenue'      => ['field' => 'total_amount', 'reducer' => 'sum'],
     *         ],
     *         'attribution' => [
     *             'identity_field' => 'customer_identity_hash',
     *             'timestamp_field' => 'platform_created_at',
     *         ],
     *     ],
     *     'email_activity' => [
     *         'source_entity' => 'channeled_events',
     *         'metrics' => [
     *             'opens_standard' => ['condition' => ['action' => 'open', 'is_proxy' => false], 'reducer' => 'count'],
     *             'opens_proxy'    => ['condition' => ['action' => 'open', 'is_proxy' => true],  'reducer' => 'count'],
     *             'clicks_total'   => ['condition' => ['action' => 'click'],                     'reducer' => 'count'],
     *             'clicks_unique'  => ['condition' => ['action' => 'click'], 'field' => 'email_id', 'reducer' => 'count_distinct'],
     *         ],
     *         'attribution' => [
     *             'identity_field' => 'email_id',
     *             'timestamp_field' => 'event_timestamp',
     *         ],
     *     ],
     * ]
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getPreAggregationRules(): array;

    /**
     * Get the default attribution window (in days) for cross-channel matching.
     *
     * @return int
     */
    public static function getDefaultAttributionWindowDays(): int;
}
