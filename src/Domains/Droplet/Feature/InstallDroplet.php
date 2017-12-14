<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use SuperV\Platform\Domains\Composer\Jobs\GetBaseNamespace;
use SuperV\Platform\Domains\Composer\Jobs\GetComposerArray;
use SuperV\Platform\Domains\Config\Jobs\EnableConfigFiles;
use SuperV\Platform\Domains\Droplet\Jobs\LocateDroplet;
use SuperV\Platform\Domains\Droplet\Jobs\MakeDropletModel;
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
        $model = $this->dispatch(new MakeDropletModel($this->slug, $this->path));

        $this->dispatch(new LocateDroplet($model));

        $composer = $this->dispatch(new GetComposerArray(base_path($model->getPath())));


        $model->setNamespace($this->dispatch(new GetBaseNamespace($composer)))
              ->setEnabled(true)
              ->save();

        $this->dispatch(new LoadDroplet($model->getPath()));

        $droplet = app($model->droplet())->setModel($model);
        $this->dispatch(new EnableConfigFiles($droplet));
//        $this->dispatch(new IntegrateDroplet($droplet));

        // symlink public folder
        if (in_array($model->getType(), ['theme'])) {
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
