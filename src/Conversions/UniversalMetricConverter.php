<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Conversions;

use Anibalealvarezs\ApiDriverCore\Classes\KeyGenerator;
use Anibalealvarezs\ApiSkeleton\Enums\Channel;
use Anibalealvarezs\ApiSkeleton\Enums\Period;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * UniversalMetricConverter
 * 
 * Standardizes the conversion of raw provider data into APIs Hub metric objects.
 * Uses a configuration-driven approach to map fields and dimensions.
 */
class UniversalMetricConverter
{
    /**
     * Converts raw rows into a collection of metric objects based on mapping config.
     *
     * @param array $rows Raw rows from the provider API
     * @param array $config Mapping configuration
     * @param LoggerInterface|null $logger
     * @return ArrayCollection
     */
    public static function convert(array $rows, array $config, ?LoggerInterface $logger = null): ArrayCollection
    {
        $startTime = microtime(true);
        $collection = new ArrayCollection();
        
        // Configuration defaults
        $channel = $config['channel'] ?? null;
        if (!$channel) {
            throw new \InvalidArgumentException("Channel is required for UniversalMetricConverter");
        }
        $channelEnum = \Anibalealvarezs\ApiSkeleton\Enums\Channel::tryFromName((string) $channel);
        $channel = $channelEnum ? $channelEnum->value : $channel;

        $period = $config['period'] ?? Period::Daily->value;
        $platformIdField = $config['platform_id_field'] ?? 'id';
        $dateField = $config['date_field'] ?? 'date';
        
        // Metric Mappings: [provider_field => system_name]
        $metricsMap = $config['metrics'] ?? [];
        
        // Dimension Mappings: [breakdown_key]
        $dimensionsKeys = $config['dimensions'] ?? [];

        // Contextual Entities (Page, Account, Campaign, etc.)
        $context = $config['context'] ?? [];

        foreach ($rows as $row) {
            // 1. Extract Dimensions
            $dimensions = [];
            foreach ($dimensionsKeys as $dimKey) {
                if (is_array($dimKey)) {
                    // Support pre-calculated dimensions (e.g. from GSC aggregation)
                    $dimensions[] = $dimKey;
                } elseif (isset($row[$dimKey])) {
                    $dimensions[] = [
                        'dimensionKey' => $dimKey,
                        'dimensionValue' => (string) $row[$dimKey]
                    ];
                }
            }
            // Sort to ensure stable hash
            KeyGenerator::sortDimensions($dimensions);
            $dimensionsHash = KeyGenerator::generateDimensionsHash($dimensions);

            // 2. Extract Date
            $rawDate = self::getValueByPath($row, $dateField);
            $metricDate = $rawDate ? Carbon::parse((string) $rawDate)->toDateString() : Carbon::now()->toDateString();

            // 3. Nested Row Support (e.g. Facebook Organic values array)
            $nestedRows = [$row];
            if (isset($config['row_path'])) {
                $nestedRows = self::getValueByPath($row, $config['row_path']) ?: [];
            }

            foreach ($nestedRows as $nRow) {
                // If nested, we might need to extract date from the nested row
                $nDate = $metricDate;
                if (isset($config['nested_date_field'])) {
                    $rawNDate = self::getValueByPath($nRow, $config['nested_date_field']);
                    if ($rawNDate) $nDate = Carbon::parse((string) $rawNDate)->toDateString();
                }

                // 4. Process each mapped metric
                foreach ($metricsMap as $providerField => $systemName) {
                    $rawValue = self::getValueByPath($nRow, $providerField);
                    if (is_null($rawValue) && !isset($config['include_nulls'])) {
                        continue;
                    }

                    $normalizedValue = self::normalizeValue($rawValue);

                    // 5a. Extract country/device from row (universal auto-resolution)
                    // These are read from known field names in the row before key generation.
                    $rowCountry = $row['country_code'] ?? ($row['country'] ?? ($context['countryCode'] ?? ($context['country'] ?? null)));
                    $rowDevice  = $row['device_type'] ?? ($row['device'] ?? ($context['deviceType'] ?? ($context['device'] ?? null)));

                    // 5b. Build row_key_fields overrides:
                    // Maps raw row fields -> KeyGenerator param names AND sets canonical metric properties.
                    // e.g. ['campaign_id' => 'channeledCampaign', 'adset_id' => 'channeledAdGroup']
                    // Known canonical property names per keyParam:
                    //   channeledAccount  → channeledAccountPlatformId
                    //   channeledCampaign → channeledCampaignPlatformId
                    //   channeledAdGroup  → channeledAdGroupPlatformId
                    //   channeledAd       → channeledAdPlatformId
                    //   creative          → creativePlatformId
                    //   country           → countryCode
                    //   device            → deviceType
                    $keyParamToMetricProp = [
                        'campaign'          => 'campaignPlatformId',
                        'channeledAccount'  => 'channeledAccountPlatformId',
                        'channeledCampaign' => 'channeledCampaignPlatformId',
                        'channeledAdGroup'  => 'channeledAdGroupPlatformId',
                        'channeledAd'       => 'channeledAdPlatformId',
                        'creative'          => 'creativePlatformId',
                        'country'           => 'countryCode',
                        'device'            => 'deviceType',
                        'page'              => 'pagePlatformId',
                        'post'              => 'postPlatformId',
                        'product'           => 'productPlatformId',
                        'customer'          => 'customerPlatformId',
                        'order'             => 'orderPlatformId',
                        'account'           => 'accountPlatformId',
                        'query'             => 'query',
                    ];
                    $rowKeyExtras = []; // keyParam name => value from row
                    foreach ($config['row_key_fields'] ?? [] as $rowField => $targets) {
                        $rawVal = $row[$rowField] ?? null;
                        if ($rawVal !== null && $rawVal !== '') {
                            $targetList = is_array($targets) ? $targets : [$targets];
                            foreach ($targetList as $keyParamName) {
                                $rowKeyExtras[$keyParamName] = (string)$rawVal;
                            }
                        }
                    }

                    // 5c. Generate Metric Configuration Key
                    // Priority: context (static) → row_key_fields → auto country/device
                    $keyParams = array_merge($context, [
                        'channel'      => $channel,
                        'name'         => $systemName,
                        'period'       => $period,
                        'dimensionSet' => $dimensionsHash,
                        'country'      => $rowCountry,
                        'device'       => $rowDevice,
                    ], $rowKeyExtras);

                    $keyParams = array_filter($keyParams, function($v, $k) {
                        return !is_null($v) && in_array($k, [
                            'channel', 'name', 'period', 'account', 'channeledAccount', 'campaign',
                            'channeledCampaign', 'channeledAdGroup', 'channeledAd', 'creative',
                            'page', 'query', 'post', 'product', 'customer', 'order', 'country',
                            'device', 'dimensionSet'
                        ]);
                    }, ARRAY_FILTER_USE_BOTH);

                    $metricConfigKey = KeyGenerator::generateMetricConfigKey(...$keyParams);

                    // 6. Build Standardized Metric Object
                    $metric = new stdClass();
                    $metric->channel          = $channel;
                    $metric->name             = $systemName;
                    $metric->value            = $normalizedValue;
                    $metric->period           = $period;
                    $metric->metricDate       = $nDate;
                    $metric->platformId       = (string) ($row[$platformIdField] ?? $config['fallback_platform_id'] ?? 'unknown');
                    $metric->platformCreatedAt = $nDate;
                    $metric->dimensions       = $dimensions;
                    $metric->dimensionsHash   = $dimensionsHash;
                    $metric->metricConfigKey  = $metricConfigKey;
                    $metric->data             = $row;
                    $metric->nested_data      = $nRow;
                    // Auto-propagate country/device with canonical property names for MetricsProcessor
                    $metric->countryCode      = $rowCountry;
                    $metric->deviceType       = $rowDevice;

                    // Metadata filtering (optional)
                    $metadataFields = $config['metadata_fields'] ?? [];
                    if (!empty($metadataFields)) {
                        $metric->metadata = array_filter($row, fn($key) => in_array($key, $metadataFields), ARRAY_FILTER_USE_KEY);
                    } else {
                        $metric->metadata = [];
                    }

                    // Inject Context Values (static entities / strings for KeyGenerator reference)
                    foreach ($context as $key => $val) {
                        $metric->$key = $val;
                    }

                    // Inject Entities (pre-loaded ORM objects for MetricsProcessor)
                    $entities = $config['entities'] ?? [];
                    foreach ($entities as $key => $val) {
                        $metric->$key = $val;
                    }

                    // Inject Row Key Fields as canonical *PlatformId properties for MetricsProcessor resolution
                    foreach ($rowKeyExtras as $keyParamName => $rawVal) {
                        $metricProp = $keyParamToMetricProp[$keyParamName] ?? null;
                        if ($metricProp) {
                            $metric->$metricProp = $rawVal;
                        }
                    }

                    // Inject Row Entity Fields (legacy / explicit: row field -> metric property name)
                    // Use this when the canonical *PlatformId property name doesn't match row_key_fields.
                    foreach ($config['row_entity_fields'] ?? [] as $rowField => $metricProp) {
                        $rawId = $row[$rowField] ?? null;
                        if ($rawId !== null && $rawId !== '') {
                            $metric->$metricProp = (string)$rawId;
                        }
                    }

                    $collection->add($metric);
                }
            }
        }

        $totalTime = microtime(true) - $startTime;
        $logger?->debug(sprintf(
            "Universal conversion completed: %d source rows -> %d metrics in %.4f seconds",
            count($rows),
            $collection->count(),
            $totalTime
        ));

        return $collection;
    }

    /**
     * Extracts values using dot-notation path.
     */
    private static function getValueByPath(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return null;
            }
            $data = $data[$key];
        }
        return $data;
    }

    /**
     * Normalizes complex provider values (arrays, strings) into numeric formats.
     */
    private static function normalizeValue(mixed $value): float|int
    {
        if (is_numeric($value)) {
            return $value + 0;
        }

        if (is_array($value)) {
            // Handle Facebook-style action results or value objects
            return (float) ($value[0]['value'] ?? ($value[0]['amount'] ?? ($value['value'] ?? 0)));
        }

        return 0;
    }
}
