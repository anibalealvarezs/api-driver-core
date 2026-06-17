<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

interface LocationableInterface
{
    public static function getLocationPlatformId(array $asset, ?string $key = null): string;

    public static function getLocationTitle(array $asset, ?string $key = null): string;

    public static function getLocationStoreCode(array $asset, ?string $key = null): ?string;

    public static function getLocationLat(array $asset, ?string $key = null): ?float;

    public static function getLocationLng(array $asset, ?string $key = null): ?float;

    public static function getLocationZipCode(array $asset, ?string $key = null): ?string;

    public static function getLocationCity(array $asset, ?string $key = null): ?string;

    public static function getLocationState(array $asset, ?string $key = null): ?string;

    public static function getLocationCountry(array $asset, ?string $key = null): ?string;

    public static function getLocationData(array $asset, ?string $key = null): array;
}
