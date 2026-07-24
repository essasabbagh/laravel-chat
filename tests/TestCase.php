<?php

namespace Essasabbagh\LaravelChat\Tests;

use Essasabbagh\LaravelChat\ChatServiceProvider;
use Essasabbagh\LaravelChat\Tests\Models\TestAgent;
use Essasabbagh\LaravelChat\Tests\Models\TestCustomer;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ChatServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('chat.participant_models', [
            'customer' => TestCustomer::class,
            'agent' => TestAgent::class,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadPackageMigrations();
        $this->createTestModelsTables();
    }

    private function loadPackageMigrations(): void
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/../database/migrations'));
        $this->loadMigrationsFrom(realpath(__DIR__.'/../vendor/orchestra/testbench-core/laravel/migrations'));
    }

    private function createTestModelsTables(): void
    {
        Schema::create('test_customers', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('test_agents', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
}
