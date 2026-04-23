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
     * @param string|null $key
     * @return string
     */
    public static function getChanneledAccountPlatformId(array $asset, ?string $key = null): string;

    /**
     *
     * @param array $asset
     * @param string|null $key
     * @return string
     */
    public static function getChanneledAccountPlatformCreatedAt(array $asset, ?string $key = null): string;

    /**
     *
     * @param array $asset
     * @param string|null $key
     * @return string
     */
    public static function getChanneledAccountName(array $asset, ?string $key = null): string;

    /**
     *
     * @return string
     */
    public static function getChanneledAccountType(): string;

    /**
     *
     * @param array $asset
     * @param string|null $key
     * @return array
     */
    public static function getChanneledAccountData(array $asset, ?string $key = null): array;
}
