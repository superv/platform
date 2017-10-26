<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use SuperV\Platform\Domains\Composer\Jobs\GetBaseNamespace;
use SuperV\Platform\Domains\Composer\Jobs\GetComposerArrayJob;
use SuperV\Platform\Domains\Droplet\Jobs\LocateDroplet;
use SuperV\Platform\Domains\Droplet\Jobs\MakeDropletModelJob;
use SuperV\Platform\Domains\Feature\Feature;

/**
 * Class InstallDroplet.
 *
 * Installs a droplet into the platform
 */
class InstallDroplet extends Feature
{
    private $slug;

    /**
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
        /** @var \SuperV\Platform\Domains\Droplet\Model\DropletModel $model */
        $model = $this->dispatch(new MakeDropletModelJob($this->slug, $this->path));

        $this->dispatch(new LocateDroplet($model));

        $composer = $this->dispatch(new GetComposerArrayJob(base_path($model->getPath())));


        $model->setNamespace($this->dispatch(new GetBaseNamespace($composer)))
              ->setEnabled(true)
              ->save();

        $this->dispatch(new LoadDroplet($this->path));
        $this->dispatch(new IntegrateDroplet($model));

        // symlink public folder
        if (in_array($model->getType(), ['port', 'theme'])) {
            $publicPath = public_path(str_plural($model->getType()));
            if (!file_exists($publicPath)) {
                mkdir($publicPath);
            }
            $where = $publicPath."/".$model->getName();
            $what = base_path($model->getPath('public'));
            symlink($what, $where);
        }

        return true;
    }
}
