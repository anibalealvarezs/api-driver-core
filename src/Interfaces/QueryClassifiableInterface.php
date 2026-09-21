<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

/**
 * Interface QueryClassifiableInterface
 *
 * Declares that a driver/channel normalizes search queries or keywords
 * that are eligible for post-sync semantic query classification (intent, brand, relevance).
 */
interface QueryClassifiableInterface
{
    /**
     * Determine if this driver produces search query/keyword data eligible for classification.
     *
     * @return bool
     */
    public static function supportsQueryClassification(): bool;
}
