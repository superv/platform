<?php namespace SuperV\Platform\Domains\Droplet\Feature;

use SuperV\Platform\Domains\Droplet\Jobs\CreateDropletPaths;
use SuperV\Platform\Domains\Droplet\Jobs\MakeDropletModelJob;
use SuperV\Platform\Domains\Droplet\Jobs\WriteDropletFilesJob;
use SuperV\Platform\Domains\Feature\Feature;

/**
 * Class MakeDropletFeature
 *
 * Generates a new droplet and creates files from stubs
 *
 * @package SuperV\Platform\Domains\Droplet\Feature
 */
class MakeDropletFeature extends Feature
{
    /**
     * Slug of the droplet as vendor.type.name
     *
     * @var string
     */
    private $slug;

    /**
     * Target path of the droplet
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
        $model = $this->dispatch(new MakeDropletModelJob($this->slug, $this->path));

        $this->dispatch(new CreateDropletPaths($model));

        $this->dispatch(new WriteDropletFilesJob($model));
    }
}