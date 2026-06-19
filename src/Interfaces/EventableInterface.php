<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

interface EventableInterface
{
    /**
     * Gets the platform's specific ID for the event (e.g. GA4's event name, Meta's event ID).
     */
    public static function getEventPlatformId(array $asset, ?string $key = null): string;

    /**
     * Gets the name of the event (either the native name or the mapped global unified key).
     */
    public static function getEventName(array $asset, ?string $key = null): string;

    /**
     * Gets the type/category of the event.
     */
    public static function getEventType(array $asset, ?string $key = null): ?string;

    /**
     * Extracts and returns the full metadata payload associated with the event.
     */
    public static function getEventData(array $asset, ?string $key = null): array;
}
