<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

/**
 * Exposes canonical-metric equivalences for a driver/channel in read-only mode.
 *
 * Returned dictionary maps canonical metric keys to one or more raw provider
 * metric names persisted in APIs Hub storage.
 */
interface CanonicalMetricDictionaryProviderInterface
{
    /**
     * @return array<string, array<int, string>|string>
     */
    public static function getCanonicalMetricDictionary(): array;

    /**
     * Returns the field name in channeled_accounts.data that contains the platform entity ID.
     */
    public static function getPlatformEntityIdField(): string;
}

