<?php

namespace Gomaa\Base\commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

class MakeDtoCommand extends Command
{
    protected $signature = 'crud:dto
                            {name : The name of the Model.}
                            {--fillables= : JSON encoded fillables for the DTO.}';

    protected $description = 'Create a DTO class for the given model with getters, setters, and JsonSerializable implementation';

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
            $this->info("File : {$path} created");
        } else {
            $this->info("File : {$path} already exists");
        }
    }

    public function getBasePath(): string
    {
        return 'App\\Http\\Modules\\' . $this->getClassPlural() . '\\Dtos';
    }

    public function getClassPlural(): string
    {
        return Str::of($this->argument('name'))->plural(5);
    }

    public function getStubPath(): string
    {
        return __DIR__ . '/stubs/dto.stub';
    }

    public function getStubVariables(): array
    {
        $fillables = json_decode($this->option('fillables'), true) ?? [];

        $props = '';
        $methods = '';

        foreach ($fillables as $field) {
            $camel = Str::studly($field);

            $props .= "    private \${$field};\n";

            $methods .= <<<EOT

    public function get{$camel}()
    {
        return \$this->{$field};
    }

    public function set{$camel}(\$value): void
    {
        \$this->{$field} = \$value;
    }

EOT;
        }

        return [
            'NAMESPACE'   => $this->getBasePath(),
            'CLASS_NAME'  => $this->getSingularClassName($this->argument('name')),
            'PROPERTIES'  => $props,
            'METHODS'     => $methods,
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
            'Http/Modules/' . $this->getClassPlural() . '/Dtos/' .
            $this->getSingularClassName($this->argument('name')) . 'Dto.php'
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
