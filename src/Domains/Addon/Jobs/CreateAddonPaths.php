<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Domains\Addon\AddonModel;

class CreateAddonPaths
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

        foreach ($this->getDirectoryList() as $dir) {
            $filesystem->makeDirectory("{$path}/{$dir}", 0755, true, true);
        }
    }

    protected function getDirectoryList()
    {
        return [
            "src",
            "src/Domains",
            "src/Extensions",
            "src/Console",
            "resources",
            "routes",
            "config",
            "database/migrations",
            "tests",
            "tests/".studly_case($this->model->shortName()),
        ];
    }
}
