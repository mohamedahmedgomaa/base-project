<?php

namespace Gomaa\Base\commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

class MakeRequestCommand extends Command
{
    protected $signature = 'crud:request
                            {name : The name of the model.}
                            {--request-action= : Request action (Create|Update|Delete|Show|List)}
                            {--fillables= : JSON encoded fillables fields.}';

    protected $description = 'Make a Request Class';
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
        $plural_name = Str::of($this->argument('name'))->plural(5);
        return 'App\\Http\\Modules\\' . $plural_name . '\\Requests';
    }

    public function getBaseName(): string
    {
        return $this->option('request-action') 
             . $this->getSingularClassName($this->argument('name')) 
             . 'Request.php';
    }

    public function getStubPath(): string
    {
        return __DIR__ . '/stubs/new_request.stub';
    }

    public function getStubVariables(): array
    {
        $fillables = json_decode($this->option('fillables'), true) ?? [];

        return [
            'NAMESPACE'   => $this->getBasePath(),
            'CLASS_NAME'  => $this->option('request-action') 
                             . $this->getSingularClassName($this->argument('name')) 
                             . 'Request',
            'RULES'       => $this->generateRules($fillables, $this->option('request-action')),
        ];
    }

    public function getSourceFile(): string|array|bool
    {
        return $this->getStubContents(
            $this->getStubPath(), 
            $this->getStubVariables()
        );
    }

    public function getStubContents($stub, array $stubVariables = []): string|array|bool
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$'.$search.'$', $replace, $contents);
        }

        return $contents;
    }

    public function getSourceFilePath()
    {
        return $this->getBasePath().'\\'.$this->getBaseName();
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

    /**
     * 🟢 Generate rules based on action
     */
    protected function generateRules(array $fillables, string $action): string
    {
        if (!in_array($action, ['Create', 'Update'])) {
            return '';
        }

        $excluded = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'];
        $fillables = array_diff_key($fillables, array_flip($excluded));

        $rules = [];

        foreach ($fillables as $field => $typeString) {
            $requiredOrNullable = $action === 'Create' ? 'required' : 'nullable';

            $parts = explode('|', $typeString);
            $fieldRules = [$requiredOrNullable];

            foreach ($parts as $part) {
                $part = trim($part);

                switch (true) {
                    case $part === 'string':
                    case $part === 'text':
                        $fieldRules[] = 'string';
                        break;

                    case $part === 'boolean':
                        $fieldRules[] = 'boolean';
                        break;

                    case str_contains($part, 'int'):
                    case $part === 'unsignedBigInteger':
                        $fieldRules[] = 'integer';
                        break;

                    case $part === 'timestamp':
                    case $part === 'date':
                        $fieldRules[] = 'date';
                        break;

                    case $part === 'unique':
                        $table = Str::plural(str_replace('_id', '', $field));
                        $fieldRules[] = "unique:$table,$field";
                        break;

                    case $part === 'nullable':
                        $fieldRules[0] = 'nullable';
                        break;

                    case str_starts_with($part, 'enum:'):
                        $values = str_replace('enum:', '', $part);
                        $fieldRules[] = "in:$values";
                        break;

                    case str_starts_with($part, 'default:'):
                        break;

                    default:
                        $fieldRules[] = $part;
                        break;
                }
            }

            $fieldRules = array_unique($fieldRules);
            $rules[] = "            '$field' => '" . implode('|', $fieldRules) . "',";
        }

        return implode("\n", $rules);
    }


}
