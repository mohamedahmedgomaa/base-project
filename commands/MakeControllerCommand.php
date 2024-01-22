<?php

namespace Gomaa\Test\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Pluralizer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeControllerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:controller
                            {name : The name of the Model.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make an Controller Class';

    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->getSourceFilePath();

        $this->makeDirectory(dirname($path));

        // create files requests
        $requestArray = ['Create', 'Update', 'Delete', 'Show', 'List'];
        foreach ($requestArray as $request) {
            $this->call('crud:request', [
                'name' => $this->argument('name'),
                '--request-action' => $request
            ]);
        }

        // create file controller
        $contents = $this->getSourceFile();

        if (!$this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("File : {$path} created");
        } else {
            $this->info("File : {$path} already exits");
        }

    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return 'App\\Http\\Modules\\' . $this->getClassPlural() . '\\Controllers';
    }

    /**
     * @return string
     */
    public function getServicePath(): string
    {
        return 'App\\Http\\Modules\\' . $this->getClassPlural() . '\\Services\\' . $this->argument('name') . 'Service';
    }

    /**
     * @return string
     */
    public function getClassPlural(): string
    {
        // Converts a singular word into a plural
        return Str::of($this->argument('name'))->plural(5);
    }

    /**
     * @return string
     */
    public function getBaseName(): string
    {
        return $this->getSingularClassName($this->argument('name')) . 'Controller.php';
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath(): string
    {
        return __DIR__ . '../stubs/new_controller.stub';
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     *
     */
    public function getStubVariables(): array
    {
        return [
            'NAMESPACE' => $this->getBasePath(),
            'MODEL_NAME' => $this->getSingularClassName($this->argument('name')),
            'CLASS_NAME' => $this->getSingularClassName($this->argument('name')) . 'Controller',
            'CLASS_PLURAL' => $this->getClassPlural(),
            'SERVICE_NAME' => $this->getSingularClassName($this->argument('name')) . 'Service',
            'SERVICE_PATH' => $this->getServicePath(),
        ];
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return string|array|bool
     *
     */
    public function getSourceFile(): string|array|bool
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }


    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return string|array|bool
     */
    public function getStubContents($stub, array $stubVariables = []): string|array|bool
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;

    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath()
    {
        return $this->getBasePath() . '\\' . $this->getBaseName();
    }

    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @return string
     */
    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory(string $path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}
