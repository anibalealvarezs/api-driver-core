<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Classes;

class GlobalEventDictionary
{
    /**
     * Maps specific channel events to a unified global key.
     * Add new mappings here as needed to cross-match events across channels.
     * 
     * @var array<string, array<string, string>>
     */
    private static array $dictionary = [
        'google_analytics' => [
            'generate_lead'   => 'lead',
            'purchase'        => 'purchase',
            'add_to_cart'     => 'add_to_cart',
            'begin_checkout'  => 'begin_checkout',
            'view_item'       => 'view_content',
            'sign_up'         => 'sign_up',
        ],
        'meta' => [
            'Lead'            => 'lead',
            'Purchase'        => 'purchase',
            'AddToCart'       => 'add_to_cart',
            'InitiateCheckout'=> 'begin_checkout',
            'ViewContent'     => 'view_content',
            'CompleteRegistration' => 'sign_up',
        ]
    ];

    /**
     * Returns the global unified key for a given channel event key.
     * If no mapping is found, it returns the original source key.
     *
     * @param string $sourceKey
     * @param string $channel
     * @return string
     */
    public static function getGlobalKey(string $sourceKey, string $channel): string
    {
        return self::$dictionary[$channel][$sourceKey] ?? $sourceKey;
    }
}
