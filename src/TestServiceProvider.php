<?php

namespace Gomaa\Base;

use Gomaa\Base\commands\MakeControllerCommand;
use Gomaa\Base\commands\MakeCrudCommand;
use Gomaa\Base\commands\MakeDtoCommand;
use Gomaa\Base\commands\MakeInterfaceCommand;
use Gomaa\Base\commands\MakeMapperCommand;
use Gomaa\Base\commands\MakeMigrationCommand;
use Gomaa\Base\commands\MakeModelCommand;
use Gomaa\Base\commands\MakeRepositoryCommand;
use Gomaa\Base\commands\MakeRequestCommand;
use Gomaa\Base\commands\MakeRouteCommand;
use Gomaa\Base\commands\MakeServiceCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeCrudCommand::class,
                MakeModelCommand::class,
                MakeRouteCommand::class,
                MakeServiceCommand::class,
                MakeRepositoryCommand::class,
                MakeControllerCommand::class,
                MakeInterfaceCommand::class,
                MakeRequestCommand::class,
                MakeMigrationCommand::class,
                MakeDtoCommand::class,
                MakeMapperCommand::class,
            ]);
        }
    }

    public function register()
    {
       // نربط الـ alias "files" بالـ Filesystem class
        $this->app->singleton('files', function ($app) {
            return new Filesystem;
        });

        // ولو حابب تخلي الـ class نفسه كمان
        $this->app->singleton(Filesystem::class, function ($app) {
            return new Filesystem;
        });
    }

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
