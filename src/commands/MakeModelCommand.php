<?php

namespace Gomaa\Base\commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

class MakeModelCommand extends Command
{
    protected $signature = 'crud:model
                            {name : The name of the model.}
                            {--fillables= : Field names for the form & migration. example (id,name)}';

    protected $description = 'Make a Model Class with relationships';
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
            $this->info("✅ File : {$path} created");
        } else {
            $this->info("⚠️ File : {$path} already exists");
        }

        // ✅ Add belongsTo use statements
        $this->addBelongsToUses();

        // ✅ Generate inverse relations (hasMany)
        $this->generateInverseRelations();
    }

    public function getBasePath(): string
    {
        $plural_name = Str::of($this->argument('name'))->plural(5);
        return 'App\\Http\\Modules\\'. $plural_name .'\\Models';
    }

    public function getBaseName(): string
    {
        return $this->getSingularClassName($this->argument('name'));
    }

    public function getTableName(): string
    {
        return Str::plural(Str::snake($this->argument('name')));
    }

    public function getStubPath(): string
    {
        return __DIR__ . '/stubs/new_model.stub';
    }

    public function getFillables(string $fillables): string
    {
        $clean = str_replace(['[', ']', '"', "'"], '', $fillables);
        $arrayFillables = array_map('trim', explode(',', $clean));
        $fillable = implode("', '", $arrayFillables);
        return "['$fillable']";
    }

    public function getAllowedFilters(string $fillables): string
    {
        $clean = str_replace(['[', ']', '"', "'"], '', $fillables);
        $allowedFilters = array_map('trim', explode(',', $clean));
        $allowedFilterString = collect($allowedFilters)
            ->map(fn($f) => "AllowedFilter::exact('$f')")
            ->implode(",\n            ");
        return "[\n            " . $allowedFilterString . "\n        ]";
    }

    public function getRelationships(string $fillables): string
    {
        $clean = str_replace(['[', ']', '"', "'"], '', $fillables);
        $fields = array_map('trim', explode(',', $clean));
        $methods = [];

        foreach ($fields as $field) {
            if (Str::endsWith($field, '_id')) {
                $related = ucfirst(Str::camel(Str::beforeLast($field, '_id')));
                $methodName = Str::camel($related);

                $methods[] = <<<EOT

        public function {$methodName}()
        {
            return \$this->belongsTo({$related}::class, '{$field}');
        }
    EOT;
            }
        }

        return implode("\n", $methods);
    }

    public function getStubVariables(): array
    {
        return [
            'NAMESPACE' => $this->getBasePath(),
            'CLASS_NAME' => $this->getBaseName(),
            'FILLABLES' => $this->getFillables($this->option('fillables')),
            'ALLOWED_FILTERS' => $this->getAllowedFilters($this->option('fillables')),
            'TABLE_NAME' => $this->getTableName(),
            'RELATIONSHIPS' => $this->getRelationships($this->option('fillables')),
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
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;
    }

    public function getSourceFilePath(): string
    {
        return $this->getBasePath() . '\\' . $this->getBaseName() . '.php';
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
     * ✅ Add missing use statements for belongsTo relations
     */
    protected function addBelongsToUses()
    {
        $fillables = $this->option('fillables');
        $clean = str_replace(['[', ']', '"', "'"], '', $fillables);
        $fields = array_map('trim', explode(',', $clean));
        $path = $this->getSourceFilePath();

        if (!$this->files->exists($path)) {
            return;
        }

        $contents = $this->files->get($path);

        foreach ($fields as $field) {
            if (Str::endsWith($field, '_id')) {
                $related = ucfirst(Str::camel(Str::beforeLast($field, '_id')));

                $useStatement = "use App\\Http\\Modules\\" . Str::plural($related) . "\\Models\\" . $related . ";";
                if (!str_contains($contents, $useStatement)) {
                    $contents = preg_replace(
                        '/(namespace\s+[^;]+;)(\s*)/m',
                        "$1\n$useStatement\n",
                        $contents,
                        1
                    );
                }
            }
        }

        $this->files->put($path, $contents);
        $this->info("✅ BelongsTo use statements added in {$this->getBaseName()} model.");
    }

    /**
     * ✅ Generate inverse relations (hasMany) in related models
     */
    protected function generateInverseRelations()
    {
        $fillables = $this->option('fillables');
        $clean = str_replace(['[', ']', '"', "'"], '', $fillables);
        $fields = array_map('trim', explode(',', $clean));

        foreach ($fields as $field) {
            if (Str::endsWith($field, '_id')) {
                $related = ucfirst(Str::camel(Str::beforeLast($field, '_id')));
                $relatedModelPath = base_path(
                    'app/Http/Modules/' . Str::plural($related) . '/Models/' . $related . '.php'
                );

                $methodName = Str::camel(Str::plural($this->getBaseName()));

                $relationMethod = <<<EOT

        public function {$methodName}()
        {
            return \$this->hasMany({$this->getBaseName()}::class, '{$field}');
        }
    EOT;

                if ($this->files->exists($relatedModelPath)) {
                    $contents = $this->files->get($relatedModelPath);

                    // ✅ Add use statement if missing
                    $useStatement = "use App\\Http\\Modules\\" . Str::plural($this->getBaseName()) . "\\Models\\" . $this->getBaseName() . ";";
                    if (!str_contains($contents, $useStatement)) {
                        $contents = preg_replace(
                            '/(namespace\s+[^;]+;)(\s*)/m',
                            "$1\n$useStatement\n",
                            $contents,
                            1
                        );
                    }

                    // ✅ Add hasMany method if missing
                    if (!str_contains($contents, "function {$methodName}(")) {
                        $contents = preg_replace('/}\s*$/', $relationMethod . "\n}", $contents);
                    }

                    $this->files->put($relatedModelPath, $contents);
                    $this->info("✅ Added hasMany relation + use statement in {$related} model.");
                }
            }
        }
    }
}
