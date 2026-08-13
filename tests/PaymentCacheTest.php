<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Cache\PaymentCache;

class PaymentCacheTest extends TestCase
{
    private PaymentCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new PaymentCache();
        $this->cache->flush();
    }

    protected function tearDown(): void
    {
        $this->cache->flush();

        parent::tearDown();
    }

    public function test_put_and_get_round_trip(): void
    {
        $this->assertTrue($this->cache->put('test-key', ['value' => 42], 60));
        $this->assertSame(['value' => 42], $this->cache->get('test-key'));
    }

    public function test_get_missing_key_returns_default(): void
    {
        $this->assertNull($this->cache->get('missing'));
        $this->assertSame('fallback', $this->cache->get('missing', 'fallback'));
    }

    public function test_has_forget_and_remember(): void
    {
        $this->cache->put('key-1', 'value-1', 60);

        $this->assertTrue($this->cache->has('key-1'));
        $this->assertTrue($this->cache->forget('key-1'));
        $this->assertFalse($this->cache->has('key-1'));

        $value = $this->cache->remember('key-2', fn () => 'computed', 60);
        $this->assertSame('computed', $value);
        $this->assertSame('computed', $this->cache->get('key-2'));
    }

    public function test_gateway_and_transaction_specific_cache(): void
    {
        $this->assertTrue($this->cache->cacheGatewayData('bkash', 'token', 'abc123', 60));
        $this->assertSame('abc123', $this->cache->getCachedGatewayData('bkash', 'token'));

        $this->assertTrue($this->cache->cacheTransactionData('TXN-1', ['amount' => 100], 60));
        $this->assertSame(['amount' => 100], $this->cache->getCachedTransactionData('TXN-1'));
    }

    public function test_rate_limit_and_ai_caching(): void
    {
        $this->assertTrue($this->cache->cacheRateLimitData('ip-1', 3, 60));
        $this->assertSame(3, $this->cache->getCachedRateLimitData('ip-1'));
        $this->assertSame(0, $this->cache->getCachedRateLimitData('unknown'));

        $this->assertTrue($this->cache->cacheAIAnalysisData('PAY-1', ['risk' => 40], 60));
        $this->assertSame(['risk' => 40], $this->cache->getCachedAIAnalysisData('PAY-1'));
    }

    public function test_stats(): void
    {
        $stats = $this->cache->getStats();

        $this->assertArrayHasKey('total_keys', $stats);
        $this->assertArrayHasKey('prefix', $stats);
        $this->assertArrayHasKey('default_ttl', $stats);
        $this->assertSame('hadi_payment', $stats['prefix']);
        $this->assertSame(3600, $stats['default_ttl']);
    }
}
