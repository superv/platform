<?php

namespace SuperV\Platform\Domains\Addon\Features;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Addon\Events\MakingAddonEvent;
use SuperV\Platform\Domains\Addon\Jobs\CreateDropletPaths;
use SuperV\Platform\Domains\Addon\Jobs\MakeDropletModel;
use SuperV\Platform\Domains\Addon\Jobs\WriteDropletFiles;

class MakeDroplet
{
    use DispatchesJobs;

    /**
     * Slug of the droplet as vendor.type.name.
     *
     * @var string
     */
    private $slug;

    /**
     * Target path of the droplet.
     *
     * @var null
     */
    private $path;

    public function __construct($slug, $path = null)
    {
        $this->slug = $slug;
        $this->path = $path;
    }

    public function handle()
    {
        $model = $this->dispatch(new MakeDropletModel($this->slug, $this->path));

        $this->dispatch(new CreateDropletPaths($model));

        $this->dispatch(new WriteDropletFiles($model));

        MakingAddonEvent::dispatch($model);

        exec("composer install  -d ".base_path());
    }
}