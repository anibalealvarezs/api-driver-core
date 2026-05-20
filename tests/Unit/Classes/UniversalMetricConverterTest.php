<?php

namespace Tests\Unit\Classes;

use Anibalealvarezs\ApiDriverCore\Conversions\UniversalMetricConverter;
use Anibalealvarezs\ApiDriverCore\Classes\UniversalEntity;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UniversalMetricConverterTest extends TestCase
{
    private $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testConvertThrowsExceptionOnMissingChannel()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Channel is required for UniversalMetricConverter');

        UniversalMetricConverter::convert([], []);
    }

    public function testConvertValidMetricCollection()
    {
        $rows = [
            [
                'id' => 'row_123',
                'date' => '2026-05-19',
                'clicks' => '150',
                'impressions' => 2000,
                'ctr' => '0.075',
                'device' => 'desktop',
                'country' => 'US'
            ]
        ];

        $config = [
            'channel' => 'google_search_console',
            'period' => 'daily',
            'platform_id_field' => 'id',
            'date_field' => 'date',
            'metrics' => [
                'clicks' => 'clicks',
                'impressions' => 'impressions'
            ],
            'dimensions' => ['device', 'country'],
            'fallback_platform_id' => 'fallback_123'
        ];

        // We want to test that the Logger receives the debug message
        $this->logger->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('Universal conversion completed: 1 source rows'));

        $collection = UniversalMetricConverter::convert($rows, $config, $this->logger);

        $this->assertInstanceOf(ArrayCollection::class, $collection);
        $this->assertEquals(2, $collection->count()); // 2 mapped metrics: clicks and impressions

        $clicksMetric = $collection->first();
        $this->assertInstanceOf(UniversalEntity::class, $clicksMetric);
        $this->assertEquals('clicks', $clicksMetric->name);
        $this->assertEquals(150.0, $clicksMetric->value);
        $this->assertEquals('google_search_console', $clicksMetric->channel);
        $this->assertEquals('row_123', $clicksMetric->platformId);
        $this->assertEquals('desktop', $clicksMetric->deviceType);
        $this->assertEquals('US', $clicksMetric->countryCode);
        $this->assertCount(2, $clicksMetric->dimensions);
    }

    public function testNestedRowConversion()
    {
        $rows = [
            [
                'id' => 'facebook_post_999',
                'date' => '2026-05-19',
                'insights' => [
                    'data' => [
                        [
                            'name' => 'post_impressions',
                            'values' => [
                                ['value' => 500, 'end_time' => '2026-05-20']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $config = [
            'channel' => 'facebook_organic',
            'period' => 'daily',
            'platform_id_field' => 'id',
            'date_field' => 'date',
            'row_path' => 'insights.data',
            'nested_date_field' => 'values.0.end_time',
            'metrics' => [
                'values' => 'impressions'
            ]
        ];

        $collection = UniversalMetricConverter::convert($rows, $config, $this->logger);

        $this->assertInstanceOf(ArrayCollection::class, $collection);
        $this->assertEquals(1, $collection->count());

        $metric = $collection->first();
        $this->assertEquals('impressions', $metric->name);
        $this->assertEquals(500.0, $metric->value);
        $this->assertEquals('2026-05-20', $metric->metricDate);
    }

    public function testNormalizeValueVariations()
    {
        // Reflection to test normalizeValue private method
        $converter = new UniversalMetricConverter();
        $ref = new \ReflectionMethod($converter, 'normalizeValue');
        $ref->setAccessible(true);

        // 1. Numeric String
        $this->assertEquals(42.5, $ref->invoke($converter, '42.5'));

        // 2. Facebook action structure [0 => ['value' => X]]
        $fbActionVal = [['value' => 310]];
        $this->assertEquals(310.0, $ref->invoke($converter, $fbActionVal));

        // 3. Facebook amount structure [0 => ['amount' => Y]]
        $fbAmountVal = [['amount' => 15.75]];
        $this->assertEquals(15.75, $ref->invoke($converter, $fbAmountVal));

        // 4. Fallback invalid structure
        $this->assertEquals(0, $ref->invoke($converter, 'non-numeric'));
    }

    public function testGetUniversalContext()
    {
        $context = UniversalMetricConverter::getUniversalContext([
            'accountPlatformId' => 'custom_acc',
            'custom_key' => 'custom_val'
        ]);

        $this->assertArrayHasKey('account', $context);
        $this->assertEquals('custom_acc', $context['accountPlatformId']);
        $this->assertEquals('custom_val', $context['custom_key']);
    }
}
