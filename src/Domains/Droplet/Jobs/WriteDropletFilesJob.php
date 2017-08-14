<?php namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Support\Parser;

class WriteDropletFilesJob
{
    /** @var \SuperV\Platform\Domains\Droplet\Model\DropletModel */
    private $model;
    
    public function __construct(DropletModel $model)
    {
        $this->model = $model;
    }
    
    public function handle(Filesystem $filesystem, Parser $parser)
    {
        $name = ucfirst(camel_case($this->model->getName()));
        $type = ucfirst(camel_case($this->model->type()));
        
        $path = base_path($this->model->path());
        
        // Droplet Class
        $dropletClass = "{$name}{$type}";
        $content = $parser->parse($filesystem->get(base_path("vendor/superv/platform/resources/stubs/droplets/droplet.stub")),
            [
                'class' => $dropletClass,
                'model' => $this->model->toArray(),
            ]);
        $filesystem->put("{$path}/src/{$dropletClass}.php", $content);
        
        // Service Provider
        $providerClass = "{$name}{$type}ServiceProvider";
        $content = $parser->parse($filesystem->get(base_path("vendor/superv/platform/resources/stubs/droplets/provider.stub")),
            [
                'class' => $providerClass,
                'model' => $this->model->toArray(),
            ]);
        $filesystem->put("{$path}/src/{$providerClass}.php", $content);
        
        // composer.json
        $content = $parser->parse($filesystem->get(base_path("vendor/superv/platform/resources/stubs/droplets/composer.stub")),
            [
                'model' => $this->model->toArray(),
                'prefix' => str_replace("\\", "\\\\", $this->model->namespace())
            ]);
        $filesystem->put("{$path}/composer.json", $content);
    }
}