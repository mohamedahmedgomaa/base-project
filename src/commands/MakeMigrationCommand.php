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
        foreach ($fillables as $field => $type) {
            // ✅ Skip id
            if ($field === 'id') {
                continue;
            }

            if (Str::endsWith($field, '_id')) {
                $refTable = Str::plural(Str::beforeLast($field, '_id'));

                $fields .= "\$table->unsignedBigInteger('{$field}');\n            ";
                $fields .= "\$table->foreign('{$field}')->references('id')->on('{$refTable}')->onDelete('cascade');\n            ";
            } else {
                switch ($type) {
                    case 'string':
                        $fields .= "\$table->string('{$field}');\n            ";
                        break;

                    case 'text':
                        $fields .= "\$table->text('{$field}');\n            ";
                        break;

                    case 'boolean':
                        $fields .= "\$table->boolean('{$field}');\n            ";
                        break;

                    default:
                        $fields .= "\$table->{$type}('{$field}');\n            ";
                }
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
