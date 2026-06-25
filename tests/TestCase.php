<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Tests;

use Eloquage\FilamentVectorSearch\FilamentVectorSearchServiceProvider;
use Eloquage\FilamentVectorSearch\Tests\Support\EnsureErrorBagHook;
use Eloquage\LaravelVectorSearch\LaravelVectorSearchServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Workbench\App\Models\User;
use Workbench\App\Providers\AdminPanelProvider;
use Workbench\App\Providers\WorkbenchServiceProvider;

abstract class TestCase extends Orchestra
{
    use InteractsWithSession;

    private static bool $registeredErrorBagHook = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! self::$registeredErrorBagHook) {
            Livewire::componentHook(EnsureErrorBagHook::class);
            self::$registeredErrorBagHook = true;
        }

        $this->startSession();

        $errors = new ViewErrorBag;
        $errors->put('default', new MessageBag);
        $this->withSession(['errors' => $errors]);

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Eloquage\\LaravelVectorSearch\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        view()->share('errors', new ViewErrorBag);
    }

    protected function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            FilamentServiceProvider::class,
            LaravelVectorSearchServiceProvider::class,
            FilamentVectorSearchServiceProvider::class,
            WorkbenchServiceProvider::class,
            AdminPanelProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('queue.default', 'sync');
        $app['config']->set('vector-search.default_embedding_provider', 'null');
        $app['config']->set('vector-search.testing.embedding_dimensions', 8);

        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('auth.guards.web.provider', 'users');

        $coreRoot = $this->resolveCorePackageRoot();

        foreach ([
            'create_vector_indexes_table.php.stub',
            'create_vector_documents_table.php.stub',
            'create_vector_chunks_table.php.stub',
        ] as $file) {
            $migration = include $coreRoot.'/database/migrations/'.$file;
            $migration->up();
        }

        $usersMigration = include __DIR__.'/../workbench/database/migrations/0001_01_01_000000_create_users_table.php';
        $usersMigration->up();
    }

    protected function resolveCorePackageRoot(): string
    {
        $path = dirname(__DIR__).'/../laravel-vector-search';

        if (is_dir($path.'/database/migrations')) {
            return $path;
        }

        return dirname(__DIR__).'/vendor/eloquage/laravel-vector-search';
    }
}
