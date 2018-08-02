<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Support\Parser;
use SuperV\Platform\Contracts\Filesystem;

class WriteDropletFiles
{
    /** @var \SuperV\Platform\Domains\Droplet\DropletModel */
    private $model;

    public function __construct(DropletModel $model)
    {
        $this->model = $model;
    }

    public function handle(Filesystem $filesystem, Parser $parser)
    {
        $name = ucfirst(camel_case($this->model->name));
        $type = ucfirst(camel_case($this->model->type));

        $path = base_path($this->model->path);

        // Droplet Class
        $dropletClass = "{$name}{$type}";
        $content = $parser->parse($filesystem->get(base_path('vendor/superv/platform/resources/stubs/droplets/'.strtolower($type).'.stub')),
            [
                'class'   => $dropletClass,
                'extends' => ucwords($type),
                'model'   => $this->model->toArray(),
            ]);
        $filesystem->put("{$path}/src/{$dropletClass}.php", $content);

        // Service Provider
        $providerClass = "{$name}{$type}ServiceProvider";
        $content = $parser->parse($filesystem->get(base_path('vendor/superv/platform/resources/stubs/droplets/provider.stub')),
            [
                'class' => $providerClass,
                'model' => $this->model->toArray(),
            ]);
        $filesystem->put("{$path}/src/{$providerClass}.php", $content);

        // composer.json
        $content = $parser->parse($filesystem->get(base_path('vendor/superv/platform/resources/stubs/droplets/composer.stub')),
            [
                'model'  => $this->model->toArray(),
                'prefix' => str_replace('\\', '\\\\', $this->model->namespace),
            ]);
        $filesystem->put("{$path}/composer.json", $content);
    }
}
