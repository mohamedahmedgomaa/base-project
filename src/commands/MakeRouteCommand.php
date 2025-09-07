<?php

namespace Gomaa\Base\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

class MakeRouteCommand extends Command
{
    protected $signature = 'crud:route 
                            {name : The name of the Model.}';

    protected $description = 'Make a Route/index.php file inside the module folder';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $modulePath = base_path('app/Http/Modules/'.$this->getClassPlural().'/Route');
        $routePath  = $modulePath.'/index.php';

        // لو الملف مش موجود ننشئه بالـ use
        if (!$this->files->exists($routePath)) {
            $this->makeDirectory(dirname($routePath));
            $this->files->put($routePath, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n");
        }

        $routeLine = $this->getRouteLine();
        $contents  = $this->files->get($routePath);

        if (!str_contains($contents, $routeLine)) {
            $this->files->append($routePath, $routeLine . "\n");
            $this->info("File : {$routePath} created");
        } else {
            $this->info("File : {$routePath} already exists");
        }
    }

    /**
     * Generate the route line
     */
    protected function getRouteLine(): string
    {
        $plural     = Str::of($this->argument('name'))->snake()->plural();
        $controller = $this->getSingularClassName($this->argument('name')) . 'Controller';

        return "Route::apiResource('{$plural}', \\App\\Http\\Modules\\{$this->getClassPlural()}\\Controllers\\{$controller}::class);";
    }

    protected function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    protected function getClassPlural(): string
    {
        return Str::of($this->argument('name'))->plural(5);
    }

    protected function makeDirectory(string $path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }
    }
}
