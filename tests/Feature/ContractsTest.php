<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Contracts\PresenceDriver;
use Essasabbagh\LaravelChat\Contracts\TenantResolver;
use Essasabbagh\LaravelChat\Drivers\DatabasePresenceDriver;
use Essasabbagh\LaravelChat\Resolvers\NullTenantResolver;
use Essasabbagh\LaravelChat\Tests\TestCase;

class ContractsTest extends TestCase
{
    /** @test */
    public function default_tenant_resolver_returns_null()
    {
        $resolver = app(TenantResolver::class);

        $this->assertInstanceOf(NullTenantResolver::class, $resolver);
        $this->assertNull($resolver->resolve());
    }

    /** @test */
    public function can_use_custom_tenant_resolver()
    {
        $custom = new class implements TenantResolver
        {
            public function resolve(): string|int|null
            {
                return 'tenant_abc';
            }
        };

        app()->instance(TenantResolver::class, $custom);

        $this->assertEquals('tenant_abc', app(TenantResolver::class)->resolve());
    }

    /** @test */
    public function default_presence_driver_is_database()
    {
        $driver = app(PresenceDriver::class);

        $this->assertInstanceOf(DatabasePresenceDriver::class, $driver);
    }

    /** @test */
    public function presence_driver_tracks_online_status()
    {
        $driver = app(PresenceDriver::class);

        $driver->online('1', 'test_user');
        $this->assertEquals('online', $driver->status('1', 'test_user'));

        $driver->away('1', 'test_user');
        $this->assertEquals('away', $driver->status('1', 'test_user'));

        $driver->offline('1', 'test_user');
        $this->assertEquals('offline', $driver->status('1', 'test_user'));
    }
}
