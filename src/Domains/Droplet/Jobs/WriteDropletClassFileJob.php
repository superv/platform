<?php namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Support\Parser;

class WriteDropletClassFileJob
{
    private $model;
    
    public function __construct($model)
    {
        $this->model = $model;
    }
    
    public function handle(Filesystem $filesystem, Parser $parser)
    {
        $template = $filesystem->get(
            base_path("vendor/superv/platform/resources/stubs/droplets/droplet.stub")
        );
    }
}