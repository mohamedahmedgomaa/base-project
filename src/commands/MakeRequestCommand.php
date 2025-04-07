<?php

namespace Gomaa\Base\commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

class MakeRequestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:request
                            {name : The name of the model.}
                            {--request-action= : Field name request action. example (Create) || (Update)|| (Delete)|| (Show)|| (List)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make an Request Class';

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
        // Converts a singular word into a plural
        $plural_name = Str::of($this->argument('name'))->plural(5);
        return 'App\\Http\\Modules\\'. $plural_name .'\\Requests';
    }

    /**
     * @return string
     */
    public function getBaseName(): string
    {
        return $this->option('request-action') . $this->getSingularClassName($this->argument('name')) . 'Request.php';
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath(): string
    {
        return __DIR__ . '/stubs/new_request.stub';
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
            'NAMESPACE'         => $this->getBasePath(),
            'CLASS_NAME'        => $this->option('request-action') . $this->getSingularClassName($this->argument('name')) . 'Request',
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
    public function getStubContents($stub ,array $stubVariables = []): string|array|bool
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace)
        {
            $contents = str_replace('$'.$search.'$' , $replace, $contents);
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
        return $this->getBasePath() .'\\' . $this->getBaseName();
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
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory(string $path)
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}
