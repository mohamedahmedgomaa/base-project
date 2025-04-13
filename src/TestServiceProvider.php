<?php

namespace Gomaa\Base;

use Gomaa\Base\commands\MakeControllerCommand;
use Gomaa\Base\commands\MakeCrudCommand;
use Gomaa\Base\commands\MakeInterfaceCommand;
use Gomaa\Base\commands\MakeModelCommand;
use Gomaa\Base\commands\MakeRepositoryCommand;
use Gomaa\Base\commands\MakeRequestCommand;
use Gomaa\Base\commands\MakeServiceCommand;
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
