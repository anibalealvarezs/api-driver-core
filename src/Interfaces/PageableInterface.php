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
    public function getPagePlatformId(array $asset): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public function getPageCanonicalId(array $asset): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public function getPageHostname(array $asset): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public function getPageTitle(array $asset): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public function getPageUrl(array $asset): string;

    /**
     *
     * @param array $asset
     * @return array
     */
    public function getPageData(array $asset): array;
}
