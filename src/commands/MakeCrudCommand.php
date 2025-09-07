<?php

namespace Gomaa\Base\commands;

use Illuminate\Console\Command;

class MakeCrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:all
                            {name? : (Optional) The name of the Model. If not provided, all models in crud.json will be generated.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make full CRUD (Migration, Model, Service, Repository, Controller, Route, DTO, Mapper) using external JSON config';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $configPath = base_path('database/crud.json');

        if (!file_exists($configPath)) {
            $this->error("❌ Config file not found at: {$configPath}");
            return;
        }

        $config = json_decode(file_get_contents($configPath), true);

        if (!$config || !is_array($config)) {
            $this->error("❌ Invalid or empty crud.json file");
            return;
        }

        // ✅ لو اليوزر مرر اسم موديل معين
        $name = $this->argument('name');
        if ($name) {
            if (!isset($config[$name])) {
                $this->error("❌ Model '{$name}' not found in crud.json");
                return;
            }
            $this->generateCrud($name, $config[$name]);
        } else {
            // ✅ لو اليوزر ما مررش حاجة → نعمل لكل الموديلات
            foreach ($config as $modelName => $modelConfig) {
                $this->generateCrud($modelName, $modelConfig);
            }
        }
    }

    /**
     * توليد CRUD كامل لموديل واحد
     */
    protected function generateCrud(string $name, array $modelConfig): void
    {
        $fillables = $modelConfig['fillables'] ?? [];
        $fillableKeys = array_keys($fillables);

        // 🟢 1. create migration
        $this->call('crud:migration', [
            'name' => $name,
            '--fillables' => json_encode($fillables),
        ]);

        // 🟢 2. create model
        $this->call('crud:model', [
            'name' => $name,
            '--fillables' => json_encode($fillableKeys),
        ]);

        // 🟢 3. create service
        $this->call('crud:service', [
            'name' => $name
        ]);

        // 🟢 4. create repository
        $this->call('crud:repository', [
            'name' => $name
        ]);

        // 🟢 5. create controller
        $this->call('crud:controller', [
            'name'       => $name,
            '--fillables'=> json_encode($fillables),
        ]);

        // 🟢 6. create route
        $this->call('crud:route', [
            'name' => $name
        ]);

        // 🟢 7. create DTO
        $this->call('crud:dto', [
            'name' => $name,
            '--fillables' => json_encode($fillableKeys),
        ]);

        // 🟢 8. create Mapper
        $this->call('crud:mapper', [
            'name' => $name,
            '--fillables' => json_encode($fillableKeys),
        ]);

        $this->info("✅ Full CRUD for '{$name}' created successfully!");
    }
}
