<?php

namespace SuperV\Platform\Domains\Addon\Features;

use SuperV\Platform\Domains\Addon\Events\MakingAddonEvent;
use SuperV\Platform\Domains\Addon\Jobs\CreateAddonFiles;
use SuperV\Platform\Domains\Addon\Jobs\CreateAddonPaths;
use SuperV\Platform\Domains\Addon\Jobs\MakeAddonModel;
use SuperV\Platform\Support\Dispatchable;

class MakeAddon
{
    use Dispatchable;

    /**
     * Slug of the addon as vendor.type.name.
     *
     * @var string
     */
    private $identifier;

    /**
     * Target path of the addon.
     *
     * @var null
     */
    private $path;

    protected $type;

    public function __construct($identifier, $type, $path = null)
    {
        $this->identifier = $identifier;
        $this->path = $path;
        $this->type = $type;
    }

    public function handle()
    {
        $model = MakeAddonModel::dispatch($this->identifier, $this->type, $this->path);

        CreateAddonPaths::dispatch($model);
        CreateAddonFiles::dispatch($model);
        MakingAddonEvent::dispatch($model);

        exec("composer install  -d ".base_path());
    }
}
