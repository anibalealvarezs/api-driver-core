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
    public static function getChanneledAccountPlatformId(array $asset): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public static function getChanneledAccountPlatformCreatedAt(array $asset): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public static function getChanneledAccountName(array $asset): string;

    /**
     *
     * @param array $asset
     * @return string
     */
    public static function getChanneledAccountType(): string;

    /**
     *
     * @param array $asset
     * @return array
     */
    public static function getChanneledAccountData(array $asset): array;
}
