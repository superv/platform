<?php namespace SuperV\Platform\Domains\Droplet\Feature;

use SuperV\Platform\Domains\Composer\Jobs\GetBaseNamespaceJob;
use SuperV\Platform\Domains\Composer\Jobs\GetComposerArrayJob;
use SuperV\Platform\Domains\Droplet\Jobs\LocateDropletJob;
use SuperV\Platform\Domains\Droplet\Jobs\MakeDropletModelJob;
use SuperV\Platform\Domains\Feature\Feature;

/**
 * Class InstallDropletFeature
 *
 * Installs a droplet into the platform
 *
 * @package SuperV\Platform\Domains\Droplet\Feature
 */
class InstallDropletFeature extends Feature
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
        
        $this->dispatch(new LocateDropletJob($model));
        
        $composer = $this->dispatch(new GetComposerArrayJob(base_path($model->path())));

        $namespace = $this->dispatch(new GetBaseNamespaceJob($composer));
        
        $model->namespace($namespace);
        
        $model->enabled = true;

        return $model->save();
    }
}