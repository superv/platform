<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use SuperV\Platform\Domains\Config\Jobs\EnableConfigFiles;
use SuperV\Platform\Domains\Droplet\Jobs\LinkPublicFolders;
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
        /** @var \SuperV\Platform\Domains\Droplet\Droplet $model */
        $model = $this->dispatch(new MakeDropletModel($this->slug, $this->path));

        $model->locate()
              ->enable()
              ->save();

        $this->dispatch(new EnableConfigFiles($model));

        return true;
    }
}
