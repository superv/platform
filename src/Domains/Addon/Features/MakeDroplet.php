<?php

namespace SuperV\Platform\Domains\Addon\Features;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Addon\Events\MakingAddonEvent;
use SuperV\Platform\Domains\Addon\Jobs\CreateAddonPaths;
use SuperV\Platform\Domains\Addon\Jobs\MakeAddonModel;
use SuperV\Platform\Domains\Addon\Jobs\WriteAddonFiles;

class MakeAddon
{
    use DispatchesJobs;

    /**
     * Slug of the addon as vendor.type.name.
     *
     * @var string
     */
    private $slug;

    /**
     * Target path of the addon.
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
        $model = $this->dispatch(new MakeAddonModel($this->slug, $this->path));

        $this->dispatch(new CreateAddonPaths($model));

        $this->dispatch(new WriteAddonFiles($model));

        MakingAddonEvent::dispatch($model);

        exec("composer install  -d ".base_path());
    }
}