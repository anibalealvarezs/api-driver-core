<?php

namespace Tests\Unit\Classes;

use Anibalealvarezs\ApiDriverCore\Classes\AssetRegistry;
use PHPUnit\Framework\TestCase;

class AssetRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        // Register some patterns to test
        AssetRegistry::register('page', [
            'prefix' => 'page_prefix',
            'hostnames' => ['example.com', 'site.com'],
            'url_id_regex' => '~/blog/([0-9]+)~i'
        ]);

        AssetRegistry::register('campaign', [
            'prefix' => 'camp',
            'url_id_regex' => '~/campaign/([a-z0-9\-]+)~i'
        ]);
    }

    public function testGetAllPatterns()
    {
        $patterns = AssetRegistry::getAll();
        $this->assertArrayHasKey('page', $patterns);
        $this->assertArrayHasKey('campaign', $patterns);
    }

    public function testFindByHostname()
    {
        $match = AssetRegistry::findByHostname('https://sub.example.com/some/path');
        $this->assertNotNull($match);
        $this->assertEquals('page', $match['type']);
        $this->assertEquals('page_prefix', $match['prefix']);

        $noMatch = AssetRegistry::findByHostname('nonexistent.com');
        $this->assertNull($noMatch);
    }

    public function testFindByType()
    {
        $pattern = AssetRegistry::findByType('campaign');
        $this->assertNotNull($pattern);
        $this->assertEquals('camp', $pattern['prefix']);
    }

    public function testGetCanonicalIdWithPlatformId()
    {
        // If type is defined and platformId is provided, should use prefix + platformId
        $id = AssetRegistry::getCanonicalId(
            url: 'https://example.com/some/url',
            platformId: '98765',
            type: 'campaign'
        );

        $this->assertEquals('camp:98765', $id);
    }

    public function testGetCanonicalIdExtractsFromRegex()
    {
        // If platformId is not provided, should extract it using URL regex pattern
        $id = AssetRegistry::getCanonicalId(
            url: 'https://example.com/blog/4242',
            platformId: null,
            type: 'page'
        );

        $this->assertEquals('page_prefix:4242', $id);
    }

    public function testGetCanonicalIdWebsiteNormalizationSpine()
    {
        // If type prefix matches web/site/sc or is missing, should normalize host to site:domain:host
        $id = AssetRegistry::getCanonicalId(
            url: 'https://www.Google.com/search-console/insights',
            platformId: null,
            type: null,
            hostname: 'google.com'
        );

        $this->assertEquals('site:domain:google.com', $id);
    }

    public function testGetCanonicalIdFallbackHash()
    {
        // Fallback to md5 of normalized URL when platform ID cannot be resolved
        $id = AssetRegistry::getCanonicalId(
            url: 'https://example.com/non-matching-url',
            platformId: null,
            type: 'campaign'
        );

        $normalizedUrl = 'example.com/non-matching-url';
        $this->assertEquals('camp:' . md5($normalizedUrl), $id);
    }
}
