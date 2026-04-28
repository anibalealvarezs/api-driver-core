<?php

    declare(strict_types=1);

    namespace Anibalealvarezs\ApiDriverCore\Classes;

    final class MetricProfileNormalizer
    {
        private const FIELD_ALIASES = [
            'channeled_account'  => 'channeledAccount',
            'channeled_campaign' => 'channeledCampaign',
            'channeled_ad_group' => 'channeledAdGroup',
            'channeled_ad'       => 'channeledAd',
            'dimension_set'      => 'dimensionSet',
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
                    index: is_int($index) ? $index : 0,
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
            $key = self::normalizeScalar($profile['key'] ?? null) ?: sprintf('%s_profile_%d', $defaultChannel, $index + 1);
            $channel = self::normalizeScalar($profile['channel'] ?? null) ?: $defaultChannel;
            $label = self::normalizeScalar($profile['label'] ?? null) ?: ucwords(str_replace(['_', '-'], ' ', $key));

            $metricConfig = is_array($profile['metric_config'] ?? null) ? $profile['metric_config'] : [];

            return [
                'key'           => $key,
                'channel'       => $channel,
                'label'         => $label,
                'metric_config' => [
                    'required_fields'  => self::normalizeFieldList($metricConfig['required_fields'] ?? []),
                    'common_filters'   => self::normalizeFieldList($metricConfig['common_filters'] ?? []),
                    'groupable_fields' => self::normalizeFieldList($metricConfig['groupable_fields'] ?? []),
                    'index_hints'      => self::normalizeIndexHints($metricConfig['index_hints'] ?? []),
                ],
            ];
        }

        /**
         * @param mixed $value
         * @return string|null
         */
        private static function normalizeScalar(mixed $value): ?string
        {
            if ($value === null) {
                return null;
            }

            $normalized = trim((string)$value);

            return $normalized !== '' ? $normalized : null;
        }

        /**
         * @param array<int, mixed> $fields
         * @return array<int, string>
         */
        private static function normalizeFieldList(array $fields): array
        {
            $normalized = [];

            foreach ($fields as $field) {
                $value = self::normalizeScalar($field);
                if ($value === null) {
                    continue;
                }

                $value = self::FIELD_ALIASES[$value] ?? $value;
                if (!in_array($value, $normalized, true)) {
                    $normalized[] = $value;
                }
            }

            return $normalized;
        }

        /**
         * @param array<int, mixed> $indexHints
         * @return array<int, array<int, string>>
         */
        private static function normalizeIndexHints(array $indexHints): array
        {
            $normalized = [];

            foreach ($indexHints as $hint) {
                if (!is_array($hint)) {
                    continue;
                }

                $columns = self::normalizeFieldList($hint);
                if ($columns === []) {
                    continue;
                }

                $normalized[] = $columns;
            }

            return $normalized;
        }
    }

