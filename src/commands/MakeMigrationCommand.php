<?php

namespace Gomaa\Base\commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeMigrationCommand extends Command
{
    protected $signature = 'crud:migration 
                            {name : The name of the model.} 
                            {--fillables= : JSON encoded fillables for the migration.}';

    protected $description = 'Create a migration file for the given model';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $tableName = $this->getTableName();

        // ✅ Check if migration already exists
        $files = glob(database_path('migrations/*_create_' . $tableName . '_table.php'));
        if (!empty($files)) {
            $this->info("File : " . basename($files[0]) . " already exists for table {$tableName}");
            return;
        }

        $path = $this->getSourceFilePath();
        $this->makeDirectory(dirname($path));

        $contents = $this->getSourceFile();

        $this->files->put($path, $contents);
        $this->info("File : {$path} created");
    }

    public function getStubPath(): string
    {
        return __DIR__ . '/stubs/migration.stub';
    }

    public function getStubVariables(): array
    {
        $fillables = json_decode($this->option('fillables'), true) ?? [];

        $fields = '';
        foreach ($fillables as $field => $definition) {
            // ✅ تجاهل created_at و updated_at لأن timestamps() هتضيفهم
            if (in_array($field, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $parts = explode('|', $definition);
            $type = array_shift($parts);

            // enum
            if (Str::startsWith($type, 'enum[')) {
                $values = substr($type, 5, -1); // يجيب اللي جوه []
                $values = explode(',', $values);
                $fields .= "\$table->enum('{$field}', ['" . implode("','", $values) . "']);\n            ";
            }
            // decimal
            elseif (Str::startsWith($type, 'decimal(')) {
                preg_match('/decimal\((\d+),(\d+)\)/', $type, $matches);
                $fields .= "\$table->decimal('{$field}', {$matches[1]}, {$matches[2]});\n            ";
            }
            // default boolean, nullable, unique
            else {
                $fields .= "\$table->{$type}('{$field}')";
                foreach ($parts as $modifier) {
                    if (Str::startsWith($modifier, 'default:')) {
                        $val = substr($modifier, 8);
                        $fields .= "->default({$val})";
                    } elseif ($modifier === 'nullable') {
                        $fields .= "->nullable()";
                    } elseif ($modifier === 'unique') {
                        $fields .= "->unique()";
                    }
                }
                $fields .= ";\n            ";
            }
        }

        return [
            'TABLE_NAME' => $this->getTableName(),
            'FIELDS'     => trim($fields),
        ];
    }

    public function getSourceFile(): string
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }

    public function getStubContents($stub, array $stubVariables = []): string
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('{{' . $search . '}}', $replace, $contents);
        }

        return $contents;
    }

    public function getSourceFilePath(): string
    {
        return database_path('migrations/' . date('Y_m_d_His') . '_create_' . $this->getTableName() . '_table.php');
    }

    public function getTableName(): string
    {
        return Str::snake(Str::pluralStudly($this->argument('name')));
    }

    protected function makeDirectory(string $path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}
