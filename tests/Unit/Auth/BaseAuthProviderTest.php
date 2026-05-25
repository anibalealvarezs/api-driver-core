<?php

namespace Tests\Unit\Auth;

use Anibalealvarezs\ApiDriverCore\Auth\BaseAuthProvider;
use PHPUnit\Framework\TestCase;

class BaseAuthProviderTest extends TestCase
{
    public function testTokenRefresherCallbackBehavior()
    {
        // Instantiate the abstract class using a mock
        $provider = $this->getMockForAbstractClass(BaseAuthProvider::class, ['']);

        // 1. Verify default state is null
        $this->assertNull($provider->getTokenRefresherCallback());

        // 2. Set the callback and verify it returns the exact same closure
        $callback = function () { return 'refreshed_token'; };
        $provider->setTokenRefresherCallback($callback);
        $this->assertSame($callback, $provider->getTokenRefresherCallback());

        // 3. Set it back to null and verify
        $provider->setTokenRefresherCallback(null);
        $this->assertNull($provider->getTokenRefresherCallback());
    }
}
