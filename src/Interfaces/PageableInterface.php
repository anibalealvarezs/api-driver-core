<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

/**
 */
interface PageableInterface
{
    /**
     *
     * @param array $asset
     * @return string
     */
    public static function getPagePlatformId(array $asset, ?string $key = null): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public static function getPageCanonicalId(array $asset, ?string $key = null): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public static function getPageHostname(array $asset, ?string $key = null): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public static function getPageTitle(array $asset, ?string $key = null): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public static function getPageUrl(array $asset, ?string $key = null): string;

    /**
     *
     * @param array $asset
     * @return array
     */
    public static function getPageData(array $asset, ?string $key = null): array;
}
