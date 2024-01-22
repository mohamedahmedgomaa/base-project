<?php

namespace Gomaa\Test\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Pluralizer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeCrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *  php artisan crud:all ExampleCommand "id,name"
     *
     * @var string
     */
    protected $signature = 'crud:all
                            {name : The name of the Model.}
                            {fillables : The name of the Fillables Model.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make an crud all class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // create files controller
        $this->call('crud:model', ['name' => $this->argument('name'), '--fillables' => $this->argument('fillables')]);
        $this->call('crud:service', ['name' => $this->argument('name')]);
        $this->call('crud:repository', ['name' => $this->argument('name')]);
        $this->call('crud:controller', ['name' => $this->argument('name')]);

        $this->info("Files Crud created");

    }

}
