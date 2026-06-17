<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Classes;

use DateTime;
use Anibalealvarezs\ApiSkeleton\Enums\Period;
use Anibalealvarezs\ApiSkeleton\Enums\Country as CountryEnum;
use Anibalealvarezs\ApiSkeleton\Enums\Device as DeviceEnum;
use InvalidArgumentException;

/**
 * KeyGenerator v1.13.3
 *
 * Snapshot of the KeyGenerator BEFORE the addition of location/state/city params.
 * Used to verify that the new generator produces identical signatures
 * for existing records (where the new fields are null).
 */
class KeyGenerator_v1_13_3
{
    public static function generateQueryKey(object|string $query): string
    {
        if (is_object($query) && method_exists($query, 'getQuery')) {
            return md5($query->getQuery());
        }
        return md5((string) $query);
    }

    public static function generateMetricConfigKey(
        mixed $channel,
        string $name,
        mixed $period,
        object|string|null $account = null,
        object|string|null $channeledAccount = null,
        object|string|null $campaign = null,
        object|string|null $channeledCampaign = null,
        object|string|null $channeledAdGroup = null,
        object|string|null $channeledAd = null,
        ?string $creative = null,
        object|string|null $page = null,
        object|string|null $query = null,
        object|string|null $post = null,
        object|string|null $product = null,
        object|string|null $customer = null,
        object|string|null $order = null,
        mixed $country = null,
        mixed $device = null,
        object|int|string|null $dimensionSet = null
    ): string {
        $emptyHash = self::generateDimensionsHash([]);
        if ($dimensionSet === $emptyHash) { $dimensionSet = null; }

        $channelVal = $channel instanceof \BackedEnum ? $channel->value : $channel;

        $params = [
            'channel' => (string) $channelVal,
            'name' => $name,
            'period' => $period instanceof \BackedEnum ? $period->value : $period,
            'account' => self::extractString($account, 'getName'),
            'channeledAccount' => (string) self::extractString($channeledAccount, 'getPlatformId'),
            'campaign' => (string) self::extractString($campaign, 'getCampaignId'),
            'channeledCampaign' => (string) self::extractString($channeledCampaign, 'getPlatformId'),
            'channeledAdGroup' => (string) self::extractString($channeledAdGroup, 'getPlatformId'),
            'channeledAd' => (string) self::extractString($channeledAd, 'getPlatformId'),
            'creative' => $creative,
            'page' => self::extractString($page, 'getUrl'),
            'query' => self::extractString($query, 'getQuery'),
            'post' => (string) self::extractString($post, 'getPostId'),
            'product' => (string) self::extractString($product, 'getProductId'),
            'customer' => self::extractString($customer, 'getEmail'),
            'order' => (string) self::extractString($order, 'getOrderId'),
            'country' => ($country instanceof \BackedEnum) ? $country->value : self::extractString($country, 'getCode'),
            'device' => ($device instanceof \BackedEnum) ? $device->value : self::extractString($device, 'getType'),
            'dimensionSet' => self::extractString($dimensionSet, 'getHash')
        ];

        return md5(json_encode($params, JSON_UNESCAPED_UNICODE));
    }

    public static function generateMetricKey(
        mixed $channel = null,
        ?string $name = null,
        mixed $period = null,
        DateTime|string|null $metricDate = null,
        object|int|null $account = null,
        object|int|null $channeledAccount = null,
        object|int|null $campaign = null,
        object|int|null $channeledCampaign = null,
        object|int|null $channeledAdGroup = null,
        object|int|null $channeledAd = null,
        ?string $creative = null,
        object|string|null $page = null,
        object|string|null $query = null,
        object|string|null $post = null,
        object|int|null $product = null,
        object|int|null $customer = null,
        object|int|null $order = null,
        mixed $country = null,
        mixed $device = null,
        array $dimensions = [],
        ?string $dimensionsHash = null,
        ?string $metricConfigKey = null,
    ): string {
        if (is_null($metricConfigKey)) {
            if (is_null($channel) || is_null($name) || is_null($period) || is_null($metricDate)) {
                throw new InvalidArgumentException('Channel, name, period and metricDate are required to generate a metric key.');
            }
            $metricConfigKey = self::generateMetricConfigKey(
                channel: $channel,
                name: $name,
                period: $period,
                account: $account,
                channeledAccount: $channeledAccount,
                campaign: $campaign,
                channeledCampaign: $channeledCampaign,
                channeledAdGroup: $channeledAdGroup,
                channeledAd: $channeledAd,
                creative: $creative,
                page: $page,
                query: $query,
                post: $post,
                product: $product,
                customer: $customer,
                order: $order,
                country: $country,
                device: $device,
                dimensionSet: $dimensionsHash
            );
        }
        if (is_null($dimensionsHash)) {
            self::sortDimensions($dimensions);
            $dimensionsHash = self::generateDimensionsHash($dimensions);
        }
        return md5(json_encode([
            'metricConfig' => $metricConfigKey,
            'dimensionsHash' => $dimensionsHash,
            'metricDate' => $metricDate instanceof DateTime ? $metricDate->format('Y-m-d') : $metricDate,
        ], JSON_UNESCAPED_UNICODE));
    }

    public static function sortDimensions(array &$dimensions): void
    {
        usort($dimensions, function ($a, $b) {
            return strcmp($a['dimensionKey'], $b['dimensionKey']);
        });
    }

    public static function generateDimensionsHash(array $dimensions): string
    {
        return md5(json_encode($dimensions, JSON_UNESCAPED_UNICODE));
    }

    public static function generateChanneledMetricKey(
        mixed $channel,
        string $platformId,
        object|int $metric,
        DateTime|string $platformCreatedAt
    ): string {
        return md5(json_encode([
            'channel' => (string) ($channel instanceof \BackedEnum ? $channel->value : $channel),
            'platformId' => $platformId,
            'metric_id' => is_object($metric) ? $metric->getId() : $metric,
            'platformCreatedAt' => $platformCreatedAt instanceof DateTime ? $platformCreatedAt->format('Y-m-d') : $platformCreatedAt
        ], JSON_UNESCAPED_UNICODE));
    }

    private static function extractString(mixed $val, string $method): ?string
    {
        if (is_null($val)) return null;
        if (is_object($val) && method_exists($val, $method)) {
            return (string) $val->$method();
        }
        return (string) $val;
    }
}
