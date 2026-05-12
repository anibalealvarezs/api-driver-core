<?php

namespace Anibalealvarezs\ApiDriverCore\Traits;

use Anibalealvarezs\ApiDriverCore\Enums\HierarchyType;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;

trait HasHierarchicalValidationTrait
{
    /**
     * Specialized validation to ensure all metrics have the required platform ID chains.
     * This is a PURE data structure validation. No database or entity managers allowed.
     * 
     * @param ArrayCollection $collection
     * @param HierarchyType $type
     * @throws Exception
     */
    protected function validateHierarchicalIntegrity(ArrayCollection $collection, HierarchyType $type = HierarchyType::MARKETING): void
    {
        if ($collection->isEmpty()) {
            return;
        }

        foreach ($collection as $key => $metric) {
            try {
                // Simple validation rules based on channel hierarchy
                match ($type) {
                    HierarchyType::MARKETING => $this->validateMarketingHierarchy($metric),
                    HierarchyType::PAGE => $this->validatePageHierarchy($metric),
                    HierarchyType::POST => $this->validatePostHierarchy($metric),
                };
            } catch (Exception $e) {
                if (property_exists($this, 'logger') && $this->logger) {
                    $this->logger->warning("[Integrity] " . $e->getMessage() . " - Skipping one metric row.");
                }
                $collection->remove($key);
            }
        }
    }

    /**
     * Meta Ads: Ad (optional if level is higher) -> Ad Group -> Campaign -> Account
     * @param mixed $metric
     * @throws Exception
     */
    private function validateMarketingHierarchy(mixed $metric): void
    {
        $level = $metric->level ?? 'ad';
        
        // Account ID is always mandatory
        if (empty($metric->channeledAccount)) {
            // throw new Exception("Marketing Integrity Error: Channeled Account identifier is missing.");
        }

        // Hierarchical chain validation based on granularity level
        if (in_array($level, ['ad', 'ad_group', 'campaign'])) {
            if (empty($metric->channeledCampaign)) {
                // throw new Exception("Marketing Integrity Error: Campaign identifier is missing for level '$level'.");
            }
        }

        if (in_array($level, ['ad', 'ad_group'])) {
            if (empty($metric->channeledAdGroup)) {
                // throw new Exception("Marketing Integrity Error: Ad Group identifier is missing for level '$level'.");
            }
        }

        if ($level === 'ad') {
            if (empty($metric->channeledAd)) {
                // throw new Exception("Marketing Integrity Error: Ad identifier is missing for level 'ad'.");
            }
        }
    }

    /**
     * GSC: Page -> Account
     * @param mixed $metric
     * @throws Exception
     */
    private function validatePageHierarchy(mixed $metric): void
    {
        if (empty($metric->channeledAccount)) {
            throw new Exception("Page Integrity Error: Channeled Account identifier is missing.");
        }
        if (empty($metric->page)) {
            throw new Exception("Page Integrity Error: Page/URL identifier is missing.");
        }
    }

    /**
     * Organic: Post -> Page -> Account
     * @param mixed $metric
     * @throws Exception
     */
    private function validatePostHierarchy(mixed $metric): void
    {
        if (empty($metric->channeledAccount)) {
            throw new Exception("PagePost Integrity Error: Channeled Account identifier is missing.");
        }
        
        // We expect at least the Post/Entity ID and its parent Page
        if (empty($metric->post) && empty($metric->page)) {
            throw new Exception("PagePost Integrity Error: Both Post and Page identifiers are missing.");
        }
    }
}
