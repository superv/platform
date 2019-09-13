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
     * @var \SuperV\Platform\Domains\Addon\Features\MakeAddonRequest
     */
    protected $request;

    /**
     * @var bool
     */
    protected $force;

    public function __construct(MakeAddonRequest $request, bool $force = false)
    {
        $this->request = $request;
        $this->force = $force;
    }

    public function handle()
    {
        $model = MakeAddonModel::makeFromRequest($this->request);

        CreateAddonPaths::dispatch($model, $this->force);
        CreateAddonFiles::dispatch($model);
        MakingAddonEvent::dispatch($model);

        exec("composer install  -d ".base_path());
    }
}
