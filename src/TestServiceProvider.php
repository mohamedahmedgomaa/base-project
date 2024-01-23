<?php

namespace Gomaa\Test;

use Gomaa\Test\commands\MakeControllerCommand;
use Gomaa\Test\commands\MakeCrudCommand;
use Gomaa\Test\commands\MakeInterfaceCommand;
use Gomaa\Test\commands\MakeModelCommand;
use Gomaa\Test\commands\MakeRepositoryCommand;
use Gomaa\Test\commands\MakeRequestCommand;
use Gomaa\Test\commands\MakeServiceCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function boot()
    {
//        $this->offerPublishing();

        // php artisan crud:all ExampleCommand "id,name"
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();

            $this->commands([
                MakeCrudCommand::class,
                MakeModelCommand::class,
                MakeServiceCommand::class,
                MakeRepositoryCommand::class,
                MakeControllerCommand::class,
                MakeInterfaceCommand::class,
                MakeRequestCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->bind('command.crud:all', MakeCrudCommand::class);
        $this->app->bind('command.crud:model', MakeModelCommand::class);
        $this->app->bind('command.crud:service', MakeServiceCommand::class);
        $this->app->bind('command.crud:repository', MakeRepositoryCommand::class);
        $this->app->bind('command.crud:controller', MakeControllerCommand::class);
        $this->app->bind('command.crud:interface', MakeInterfaceCommand::class);
        $this->app->bind('command.crud:request', MakeRequestCommand::class);
        $this->commands([
            'command.crud:all',
            'command.crud:model',
            'command.crud:service',
            'command.crud:repository',
            'command.crud:controller',
            'command.crud:interface',
            'command.crud:request',
        ]);
    }

    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function offerPublishing()
    {
        if (!function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        }

        $this->publishes(
            [
                __DIR__ . '/../database/migrations/create_permission_tables.php.stub' =>
                    $this->getMigrationFileName('create_permission_tables.php')
            ], 'permission-migrations');

    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName($migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path . '*_' . $migrationFileName);
            })
            ->push($this->app->databasePath() . "/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
