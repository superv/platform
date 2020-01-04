<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Support\Dispatchable;

class CreateAddonPaths
{
    use Dispatchable;

    /** @var \SuperV\Platform\Domains\Addon\AddonModel */
    private $model;

    /**
     * @var bool
     */
    protected $force;

    public function __construct(AddonModel $model, bool $force = false)
    {
        $this->model = $model;
        $this->force = $force;
    }

    public function handle(Filesystem $filesystem)
    {
        $path = base_path($this->model->path);

        if (! $this->force && $filesystem->exists($path)) {
            throw new \Exception("Path already exists [{$path}]");
        }

        $filesystem->makeDirectory($path, 0755, true, true);

        foreach ($this->getDirectoryList($this->model->getType()) as $dir) {
            $filesystem->makeDirectory("{$path}/{$dir}", 0755, true, true);
        }
    }

    protected function getDirectoryList($type)
    {
        $map = [
            'module' => [
                'src/Domains',
                'src/Resources',
                'src/Console',
                'config',
                'resources',
                'database/migrations',
            ],
            'drop'   => [
                'resources',
                'js',
            ],
            'panel'  => [
                'frontend',
                'resources',
                'tools',
            ],
            'tool'   => [
                'frontend',
                'database/migrations',
            ],
        ];

        return array_merge($map[$type], [
            'src',
            'routes',
            'tests',
            'tests/'.studly_case($this->model->getHandle()),
        ]);
    }
}
