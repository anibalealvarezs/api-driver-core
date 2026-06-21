<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Classes;

final class CanonicalMetricDefinitionRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $definitions = [
        'spend' => [
            'label' => 'Spend',
            'category' => 'base',
        ],
        'clicks' => [
            'label' => 'Clicks',
            'category' => 'base',
        ],
        'impressions' => [
            'label' => 'Impressions',
            'category' => 'base',
        ],
        'reach' => [
            'label' => 'Reach',
            'category' => 'base',
        ],
        'frequency' => [
            'label' => 'Frequency',
            'category' => 'base',
        ],
        'ctr' => [
            'label' => 'Click-through Rate',
            'category' => 'ratio',
        ],
        'cpc' => [
            'label' => 'Cost per Click',
            'category' => 'ratio',
        ],
        'cpm' => [
            'label' => 'Cost per Mille',
            'category' => 'ratio',
        ],
        'sessions' => [
            'label' => 'Sessions',
            'category' => 'engagement',
        ],
        'new_users' => [
            'label' => 'New Users',
            'category' => 'engagement',
        ],
        'conversions' => [
            'label' => 'Conversions',
            'category' => 'base',
        ],
        'cost_per_conversion' => [
            'label' => 'Cost per Conversion',
            'category' => 'ratio',
        ],
        'conversion_rate' => [
            'label' => 'Conversion Rate',
            'category' => 'ratio',
        ],
        'roas_purchase' => [
            'label' => 'Purchase ROAS',
            'category' => 'ratio',
        ],
        'likes' => [
            'label' => 'Likes',
            'category' => 'engagement',
        ],
        'comments' => [
            'label' => 'Comments',
            'category' => 'engagement',
        ],
        'views' => [
            'label' => 'Views',
            'category' => 'engagement',
        ],
        'content_views' => [
            'label' => 'Content Views',
            'category' => 'engagement',
        ],
        'profile_views' => [
            'label' => 'Profile Views',
            'category' => 'engagement',
        ],
        'website_clicks' => [
            'label' => 'Website Clicks',
            'category' => 'engagement',
        ],
        'profile_links_taps' => [
            'label' => 'Profile Links Taps',
            'category' => 'engagement',
        ],
        'follows_and_unfollows' => [
            'label' => 'Follows and Unfollows',
            'category' => 'engagement',
        ],
        'saves' => [
            'label' => 'Saves',
            'category' => 'engagement',
        ],
        'shares' => [
            'label' => 'Shares',
            'category' => 'engagement',
        ],
        'total_interactions' => [
            'label' => 'Total Interactions',
            'category' => 'engagement',
        ],
        'replies' => [
            'label' => 'Replies',
            'category' => 'engagement',
        ],
        'accounts_engaged' => [
            'label' => 'Accounts Engaged',
            'category' => 'engagement',
        ],
        'post_clicks' => [
            'label' => 'Post Clicks',
            'category' => 'engagement',
        ],
        'ig_reels_avg_watch_time' => [
            'label' => 'IG Reels Average Watch Time',
            'category' => 'engagement',
        ],
        'ig_reels_video_view_total_time' => [
            'label' => 'IG Reels Video View Total Time',
            'category' => 'engagement',
        ],
        'profile_activity' => [
            'label' => 'Profile Activity',
            'category' => 'engagement',
        ],
        'profile_visits' => [
            'label' => 'Profile Visits',
            'category' => 'engagement',
        ],
        'reposts' => [
            'label' => 'Reposts',
            'category' => 'engagement',
        ],
        'follows' => [
            'label' => 'Follows',
            'category' => 'engagement',
        ],
        'position' => [
            'label' => 'Position',
            'category' => 'search',
        ],
        'engagement' => [
            'label' => 'Engagement',
            'category' => 'engagement',
        ],
        'follower_count' => [
            'label' => 'Follower Count',
            'category' => 'engagement',
        ],
        'fan_adds' => [
            'label' => 'Fan Adds',
            'category' => 'engagement',
        ],
        'page_views' => [
            'label' => 'Page Views',
            'category' => 'engagement',
        ],
        'page_views_total' => [
            'label' => 'Total Page Views',
            'category' => 'engagement',
        ],
        'video_views' => [
            'label' => 'Video Views',
            'category' => 'engagement',
        ],
        'event_count' => [
            'label' => 'Event Count',
            'category' => 'engagement',
        ],
        'bounce_rate' => [
            'label' => 'Bounce Rate',
            'category' => 'ratio',
        ],
    ];

    /**
     * @var array<string, string>
     */
    private static array $aliases = [
        'results' => 'conversions',
        'cost_per_result' => 'cost_per_conversion',
        'result_rate' => 'conversion_rate',
        'purchase_roas' => 'roas_purchase',
        'website_purchase_roas' => 'roas_purchase',
        'plays' => 'views',
        'video_views' => 'video_views',
        'page_video_views' => 'video_views',
        'post_video_views' => 'video_views',
        'post_reactions_by_type_total' => 'likes',
        'saved' => 'saves',
        'post_engagement' => 'total_interactions',
        'post_engagements' => 'total_interactions',
        'page_post_engagements' => 'total_interactions',
        'post_comments' => 'comments',
        'post_reach' => 'reach',
        'post_impressions_unique' => 'reach',
        'post_shares' => 'shares',
        'eventcount' => 'event_count',
        'bouncerate' => 'bounce_rate',
        'post_media_view' => 'views',
        'post_video_avg_time_watched' => 'ig_reels_avg_watch_time',
        'page_fan_adds' => 'fan_adds',
        'page_fans' => 'follower_count',
        'activeusers' => 'reach',
        'active_users' => 'reach',
        'newusers' => 'new_users',
        'screenpageviews' => 'impressions',
        'screen_page_views' => 'impressions',
    ];

    /**
     * @var array<string, array<string, string|null>>
     */
    private static array $deprecatedInputs = [
        'actions' => [
            'reason' => 'ambiguous_metric_alias',
            'replacement' => null,
            'message' => 'Use a provider-specific metric or a concrete canonical metric instead of actions.',
        ],
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::$definitions;
    }

    public static function isCanonical(string $metric): bool
    {
        $key = self::normalizeKey($metric);
        return $key !== '' && isset(self::$definitions[$key]);
    }

    public static function normalize(string $metric): ?string
    {
        $key = self::normalizeKey($metric);
        if ($key === '') {
            return null;
        }

        if (isset(self::$definitions[$key])) {
            return $key;
        }

        return self::$aliases[$key] ?? null;
    }

    /**
     * Returns the canonical name plus all aliases pointing to it.
     * @return array<int, string>
     */
    public static function getAllAssociatedNames(string $canonicalMetric): array
    {
        $key = self::normalizeKey($canonicalMetric);
        if (!isset(self::$definitions[$key])) {
            return [];
        }

        $names = [$key];
        foreach (self::$aliases as $alias => $target) {
            if ($target === $key) {
                $names[] = $alias;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolveInput(string $metric): array
    {
        $key = self::normalizeKey($metric);
        $canonical = null;
        $isCanonical = false;
        $isLegacyAlias = false;
        $aliasTarget = null;

        if ($key !== '') {
            if (isset(self::$definitions[$key])) {
                $canonical = $key;
                $isCanonical = true;
            } elseif (isset(self::$aliases[$key])) {
                $canonical = self::$aliases[$key];
                $isLegacyAlias = true;
                $aliasTarget = $canonical;
            }
        }

        $deprecation = self::$deprecatedInputs[$key] ?? null;

        return [
            'requested_metric' => $key,
            'canonical_metric' => $canonical,
            'is_canonical' => $isCanonical,
            'is_legacy_alias' => $isLegacyAlias,
            'alias_target' => $aliasTarget,
            'deprecation' => is_array($deprecation)
                ? [
                    'reason' => (string) ($deprecation['reason'] ?? 'deprecated_metric'),
                    'replacement' => isset($deprecation['replacement']) ? (is_string($deprecation['replacement']) ? $deprecation['replacement'] : null) : null,
                    'message' => isset($deprecation['message']) ? (is_string($deprecation['message']) ? $deprecation['message'] : null) : null,
                ]
                : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function resolveDefinition(string $metric): ?array
    {
        $canonical = self::normalize($metric);
        if ($canonical === null) {
            return null;
        }

        return self::$definitions[$canonical] + ['canonical_metric' => $canonical];
    }

    /**
     * @param array<string, mixed> $definition
     * @param array<int, string> $aliases
     */
    public static function register(string $canonicalMetric, array $definition, array $aliases = []): void
    {
        $canonical = self::normalizeKey($canonicalMetric);
        if ($canonical === '') {
            return;
        }

        self::$definitions[$canonical] = $definition;

        foreach ($aliases as $alias) {
            $aliasKey = self::normalizeKey($alias);
            if ($aliasKey !== '') {
                self::$aliases[$aliasKey] = $canonical;
            }
        }
    }

    private static function normalizeKey(string $metric): string
    {
        return strtolower(trim($metric));
    }
}

