<?php namespace SuperV\Platform\Domains\Droplet\Feature;

use SuperV\Platform\Domains\Droplet\Jobs\CreateDropletPathsJob;
use SuperV\Platform\Domains\Droplet\Jobs\MakeDropletModelJob;
use SuperV\Platform\Domains\Droplet\Jobs\WriteDropletFilesJob;
use SuperV\Platform\Domains\Feature\Feature;

class MakeDropletFeature extends Feature
{
    private $slug;
    
    public function __construct($slug)
    {
        $this->slug = $slug;
    }
    
    public function handle()
    {
        $model = $this->run(new MakeDropletModelJob($this->slug));
        
        $this->run(new CreateDropletPathsJob($model));
        
        $this->run(new WriteDropletFilesJob($model));
    }
}