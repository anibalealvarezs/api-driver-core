<?php

namespace Tests\Unit\Classes;

use Anibalealvarezs\ApiDriverCore\Classes\KeyGenerator;
use PHPUnit\Framework\TestCase;

class KeyGeneratorTest extends TestCase
{
    public function testGenerateQueryKey()
    {
        $this->assertEquals(md5('test_query'), KeyGenerator::generateQueryKey('test_query'));

        $queryObj = new class {
            public function getQuery(): string { return 'dynamic_query'; }
        };
        $this->assertEquals(md5('dynamic_query'), KeyGenerator::generateQueryKey($queryObj));
    }

    public function testGenerateMetricConfigKey()
    {
        $channel = new class {
            public string $value = 'google';
        };

        // Create standard inputs
        $key = KeyGenerator::generateMetricConfigKey(
            channel: 'google',
            name: 'clicks',
            period: 'daily',
            account: 'my_account',
            channeledAccount: 'ca_platform_123',
            campaign: 'campaign_456',
            creative: 'creative_abc'
        );

        $this->assertIsString($key);
        $this->assertEquals(32, strlen($key));
    }

    public function testGenerateMetricKey()
    {
        $metricDate = new \DateTime('2026-05-19');
        $dimensions = [
            ['dimensionKey' => 'device', 'dimensionValue' => 'desktop'],
            ['dimensionKey' => 'country', 'dimensionValue' => 'usa']
        ];

        $key = KeyGenerator::generateMetricKey(
            channel: 'google',
            name: 'clicks',
            period: 'daily',
            metricDate: $metricDate,
            dimensions: $dimensions
        );

        $this->assertIsString($key);
        $this->assertEquals(32, strlen($key));
    }

    public function testGenerateMetricKeyThrowsExceptionOnMissingParams()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Channel, name, period and metricDate are required to generate a metric key.');

        KeyGenerator::generateMetricKey(
            channel: null,
            name: null,
            period: null,
            metricDate: null
        );
    }

    public function testSortDimensions()
    {
        $dimensions = [
            ['dimensionKey' => 'z_dimension'],
            ['dimensionKey' => 'a_dimension'],
            ['dimensionKey' => 'm_dimension']
        ];

        KeyGenerator::sortDimensions($dimensions);

        $this->assertEquals('a_dimension', $dimensions[0]['dimensionKey']);
        $this->assertEquals('m_dimension', $dimensions[1]['dimensionKey']);
        $this->assertEquals('z_dimension', $dimensions[2]['dimensionKey']);
    }

    public function testGenerateChanneledMetricKey()
    {
        $metric = new class {
            public function getId(): int { return 12345; }
        };

        $key = KeyGenerator::generateChanneledMetricKey(
            channel: 'google',
            platformId: 'g_platform_site',
            metric: $metric,
            platformCreatedAt: new \DateTime('2026-05-19')
        );

        $this->assertIsString($key);
        $this->assertEquals(32, strlen($key));
    }

    public function testCustomerKeys()
    {
        $email = 'Test@Example.com ';
        $key = KeyGenerator::generateCustomerKey($email);
        $this->assertEquals(md5('test@example.com'), $key);

        $channeledKey = KeyGenerator::generateChanneledCustomerKey('shopify', 'cust_123');
        $this->assertEquals(md5('shopify_cust_123'), $channeledKey);
    }

    public function testProductKeys()
    {
        $key = KeyGenerator::generateProductKey('prod_456');
        $this->assertEquals(md5('prod_456'), $key);

        $channeledKey = KeyGenerator::generateChanneledProductKey('shopify', 'prod_456');
        $this->assertEquals(md5('shopify_prod_456'), $channeledKey);
    }

    public function testVendorKeys()
    {
        $key = KeyGenerator::generateVendorKey(' My Vendor ');
        $this->assertEquals(md5('my vendor'), $key);

        $channeledKey = KeyGenerator::generateChanneledVendorKey('shopify', ' My Vendor ');
        $this->assertEquals(md5('shopify_my vendor'), $channeledKey);
    }

    public function testProductVariantKeys()
    {
        $key = KeyGenerator::generateProductVariantKey('var_789');
        $this->assertEquals(md5('var_789'), $key);

        $channeledKey = KeyGenerator::generateChanneledProductVariantKey('shopify', 'var_789');
        $this->assertEquals(md5('shopify_var_789'), $channeledKey);
    }

    public function testProductCategoryKeys()
    {
        $key = KeyGenerator::generateProductCategoryKey('cat_101');
        $this->assertEquals(md5('cat_101'), $key);

        $channeledKey = KeyGenerator::generateChanneledProductCategoryKey('shopify', 'cat_101');
        $this->assertEquals(md5('shopify_cat_101'), $channeledKey);
    }

    public function testOrderKeys()
    {
        $key = KeyGenerator::generateOrderKey('ord_202');
        $this->assertEquals(md5('ord_202'), $key);

        $channeledKey = KeyGenerator::generateChanneledOrderKey('shopify', 'ord_202');
        $this->assertEquals(md5('shopify_ord_202'), $channeledKey);
    }

    public function testDiscountKeys()
    {
        $key = KeyGenerator::generateDiscountKey('SAVE20');
        $this->assertEquals(md5('SAVE20'), $key);

        $channeledKey = KeyGenerator::generateChanneledDiscountKey('shopify', 'SAVE20');
        $this->assertEquals(md5('shopify_SAVE20'), $channeledKey);
    }

    public function testPriceRuleKeys()
    {
        $key = KeyGenerator::generatePriceRuleKey('rule_303');
        $this->assertEquals(md5('rule_303'), $key);

        $channeledKey = KeyGenerator::generateChanneledPriceRuleKey('shopify', 'rule_303');
        $this->assertEquals(md5('shopify_rule_303'), $channeledKey);
    }
}
