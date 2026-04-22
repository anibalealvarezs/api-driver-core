<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

/**
 */
interface ChanneledAccountableInterface
{
    /**
     *
     * @param array $asset
     * @return string
     */
    public function getChanneledAccountPlatformId(array $asset): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public function getChanneledAccountPlatformCreatedAt(array $asset): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public function getChanneledAccountName(array $asset): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public function getChanneledAccountType(array $asset): string;

    /**
     *
     * @param array $asset
     * @return array
     */
    public function getChanneledAccountData(array $asset): array;
}
