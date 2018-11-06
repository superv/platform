<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Domains\Addon\AddonModel;

class CreateDropletPaths
{
    /** @var \SuperV\Platform\Domains\Addon\AddonModel */
    private $model;

    public function __construct(AddonModel $model)
    {
        $this->model = $model;
    }

    public function handle(Filesystem $filesystem)
    {
        $path = base_path($this->model->path);

        if ($filesystem->exists($path)) {
            throw new \Exception("Path already exists [{$path}]");
        }

        $filesystem->makeDirectory($path, 0755, true, true);
        $filesystem->makeDirectory("{$path}/src", 0755, true, true);
        $filesystem->makeDirectory("{$path}/src/Domains", 0755, true, true);
        $filesystem->makeDirectory("{$path}/src/Features", 0755, true, true);
        $filesystem->makeDirectory("{$path}/src/Console", 0755, true, true);
        $filesystem->makeDirectory("{$path}/resources", 0755, true, true);
        $filesystem->makeDirectory("{$path}/routes", 0755, true, true);
        $filesystem->makeDirectory("{$path}/config", 0755, true, true);
        $filesystem->makeDirectory("{$path}/database/migrations", 0755, true, true);

        $filesystem->makeDirectory("{$path}/tests", 0755, true, true);
        $filesystem->makeDirectory("{$path}/tests/".$this->model->shortName(), 0755, true, true);
    }
}
