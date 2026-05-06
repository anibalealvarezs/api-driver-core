<?php

    declare(strict_types=1);

    namespace Anibalealvarezs\ApiDriverCore\Classes;

    final class AggregationProfileNormalizer
    {
        private const array OPERATOR_ALIASES = [
            '='           => 'eq',
            '=='          => 'eq',
            'eq'          => 'eq',
            '!='          => 'neq',
            '<>'          => 'neq',
            'neq'         => 'neq',
            'null'        => 'is_null',
            'is_null'     => 'is_null',
            'not_null'    => 'is_not_null',
            'is_not_null' => 'is_not_null',
        ];

        /**
         * @param array<int, array<string, mixed>> $profiles
         * @return array<int, array<string, mixed>>
         */
        public static function normalizeProfiles(string $defaultChannel, array $profiles): array
        {
            $normalized = [];

            foreach ($profiles as $index => $profile) {
                if (!is_array($profile)) {
                    continue;
                }

                $normalized[] = self::normalizeProfile(
                    defaultChannel: $defaultChannel,
                    profile: $profile,
                    index: is_int($index) ? $index : 0
                );
            }

            return $normalized;
        }

        /**
         * @param array<string, mixed> $profile
         * @return array<string, mixed>
         */
        public static function normalizeProfile(string $defaultChannel, array $profile, int $index = 0): array
        {
            $channel = self::normalizeScalar($profile['channel'] ?? null) ?: $defaultChannel;
            $key = self::normalizeScalar($profile['key'] ?? null) ?: sprintf('%s_aggregation_profile_%d', $channel, $index + 1);
            $label = self::normalizeScalar($profile['label'] ?? null) ?: ucwords(str_replace(['_', '-'], ' ', $key));

            return [
                'key'                => $key,
                'channel'            => $channel,
                'label'              => $label,
                'asset_type'         => self::normalizeScalar($profile['asset_type'] ?? $profile['assetType'] ?? null) ?: 'page',
                'metric_nature'      => self::normalizeMetricNature($profile['metric_nature'] ?? $profile['metricNature'] ?? null),
                'period_modes'       => self::normalizeSimpleList($profile['period_modes'] ?? $profile['periodModes'] ?? ['daily']),
                'group_patterns'     => self::normalizeGroupPatterns($profile['group_patterns'] ?? $profile['groupPatterns'] ?? [['metricDate']]),
                'filter_contract'    => self::normalizeFilterContract($profile['filter_contract'] ?? $profile['filterContract'] ?? []),
                'reducer_strategies' => self::normalizeReducerStrategies($profile['reducer_strategies'] ?? $profile['reducerStrategies'] ?? []),
            ];
        }

        private static function normalizeMetricNature(mixed $value): string
        {
            $normalized = self::normalizeScalar($value);
            if ($normalized === null) {
                return 'flow';
            }

            $allowed = ['flow', 'snapshot', 'ratio', 'weighted_ratio'];

            return in_array($normalized, $allowed, true) ? $normalized : 'flow';
        }

        /**
         * @param mixed $patterns
         * @return array<int, array<int, string>>
         */
        private static function normalizeGroupPatterns(mixed $patterns): array
        {
            if (!is_array($patterns)) {
                return [['metricDate']];
            }

            $normalized = [];

            foreach ($patterns as $pattern) {
                if (is_array($pattern)) {
                    $row = self::normalizeSimpleList($pattern);
                    // Explicitly allow empty patterns if provided as an empty array
                    $normalized[] = $row;
                    continue;
                }

                $scalar = self::normalizeScalar($pattern);
                if ($scalar !== null) {
                    $normalized[] = [$scalar];
                }
            }

            return $normalized !== [] ? $normalized : [['metricDate']];
        }


        /**
         * @param mixed $contract
         * @return array<string, array<int, string>>
         */
        private static function normalizeFilterContract(mixed $contract): array
        {
            if (!is_array($contract)) {
                return [];
            }

            $normalized = [];
            foreach ($contract as $field => $operators) {
                $fieldName = self::normalizeScalar($field);
                if ($fieldName === null) {
                    continue;
                }

                $operatorList = is_array($operators) ? self::normalizeSimpleList($operators) : self::normalizeSimpleList([$operators]);
                if ($operatorList === []) {
                    continue;
                }

                $operatorList = array_values(array_map(
                    static fn(string $operator): string => self::OPERATOR_ALIASES[$operator] ?? $operator,
                    $operatorList
                ));

                $operatorList = array_values(array_unique($operatorList));

                $normalized[$fieldName] = $operatorList;
            }

            return $normalized;
        }

        /**
         * @param mixed $strategies
         * @return array<string, string>
         */
        private static function normalizeReducerStrategies(mixed $strategies): array
        {
            if (!is_array($strategies)) {
                return [];
            }

            $normalized = [];
            foreach ($strategies as $metric => $strategy) {
                $metricName = self::normalizeScalar($metric);
                $strategyName = self::normalizeScalar($strategy);
                if ($metricName === null || $strategyName === null) {
                    continue;
                }

                $normalized[$metricName] = $strategyName;
            }

            return $normalized;
        }

        /**
         * @param array<int, mixed> $values
         * @return array<int, string>
         */
        private static function normalizeSimpleList(array $values): array
        {
            $normalized = [];

            foreach ($values as $value) {
                $item = self::normalizeScalar($value);
                if ($item === null || in_array($item, $normalized, true)) {
                    continue;
                }

                $normalized[] = $item;
            }

            return $normalized;
        }

        private static function normalizeScalar(mixed $value): ?string
        {
            if ($value === null) {
                return null;
            }

            $normalized = trim((string)$value);

            return $normalized === '' ? null : $normalized;
        }
    }

