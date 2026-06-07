<?php

namespace Ccast\TagixoPrimix\Tests;

use Ccast\Tagixo\TagixoServiceProvider;
use Ccast\TagixoPrimix\TagixoPrimixServiceProvider;
use Ccast\TagixoPrimix\Tests\Support\AdminPanelProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LiVue\LiVueServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Primix\Actions\PrimixActionsServiceProvider;
use Primix\Details\PrimixDetailsServiceProvider;
use Primix\Forms\PrimixFormsServiceProvider;
use Primix\MultiTenant\MultiTenantServiceProvider;
use Primix\Notifications\PrimixNotificationsServiceProvider;
use Primix\PrimixServiceProvider;
use Primix\Support\PrimixSupportServiceProvider;
use Primix\Tables\PrimixTablesServiceProvider;
use Primix\Widgets\PrimixWidgetsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Bare users table — Primix's auth guard needs a user retrievable by id;
        // the schema is intentionally minimal because the migrated tests only
        // need a session-authenticated principal, not user-management features.
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        $this->loadMigrationsFrom(__DIR__.'/../vendor/ccast/tagixo/database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            LiVueServiceProvider::class,
            PrimixSupportServiceProvider::class,
            PrimixActionsServiceProvider::class,
            PrimixFormsServiceProvider::class,
            PrimixTablesServiceProvider::class,
            PrimixDetailsServiceProvider::class,
            PrimixNotificationsServiceProvider::class,
            PrimixWidgetsServiceProvider::class,
            MultiTenantServiceProvider::class,
            PrimixServiceProvider::class,
            TagixoServiceProvider::class,
            TagixoPrimixServiceProvider::class,
            AdminPanelProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('auth.providers.users.model', Support\User::class);
    }
}
