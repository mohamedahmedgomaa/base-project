<?php

namespace Gomaa\Base\commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

class MakeMapperCommand extends Command
{
    protected $signature = 'crud:mapper
                            {name : The name of the Model.}
                            {--fillables= : JSON encoded fillables for the Mapper.}';

    protected $description = 'Create a Mapper class for converting between Model, DTO, and Array';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $path = $this->getSourceFilePath();
        $this->makeDirectory(dirname($path));

        $contents = $this->getSourceFile();

        if (!$this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("✅ File created: {$path}");
        } else {
            $this->warn("⚠️ File already exists: {$path}");
        }
    }

    public function getBasePath(): string
    {
        return 'App\\Http\\Modules\\' . $this->getClassPlural() . '\\Mappers';
    }

    public function getClassPlural(): string
    {
        return Pluralizer::plural($this->argument('name'));
    }

    public function getStubPath(): string
    {
        return __DIR__ . '/stubs/mapper.stub';
    }

    public function getStubVariables(): array
    {
        $fillables = json_decode($this->option('fillables'), true) ?? [];

        $dtoFill   = '';
        $modelFill = '';
        $arrayFill = '';

        foreach ($fillables as $field) {
            $camel = Str::studly($field);

            // Model → DTO
            $dtoFill .= "        \$dto->set{$camel}(\$model->{$field});\n";

            // DTO → Model
            $modelFill .= "        \$model->{$field} = \$dto->get{$camel}();\n";

            // DTO → Array
            $arrayFill .= "            '{$field}' => \$dto->get{$camel}(),\n";
        }

        return [
            'NAMESPACE'       => $this->getBasePath(),
            'CLASS_NAME'      => $this->getSingularClassName($this->argument('name')), // Category
            'CLASS_PLURAL'    => $this->getClassPlural(), // Categories
            'DTO_FILL'        => $dtoFill,
            'MODEL_FILL'      => $modelFill,
            'ARRAY_FILL'      => $arrayFill,
        ];
    }

    public function getSourceFile(): string|array|bool
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }

    public function getStubContents($stub, array $stubVariables = []): string|array|bool
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('{{' . $search . '}}', $replace, $contents);
        }

        return $contents;
    }

    public function getSourceFilePath(): string
    {
        return app_path(
            'Http/Modules/' .
            $this->getClassPlural() .
            '/Mappers/' .
            $this->getSingularClassName($this->argument('name')) .
            'Mapper.php'
        );
    }

    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    protected function makeDirectory(string $path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }
        return $path;
    }
}
