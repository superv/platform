<?php namespace SuperV\Platform\Domains\Droplet\Command;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Feature\FeatureCollection;
use Vizra\SupervModule\Drop\DropModel;

class QueueServerFeature
{
    use DispatchesJobs;
    /**
     * @var DropModel
     */
    protected $drop;
    
    protected $feature;
    
    public function __construct(DropModel $drop, $feature)
    {
        $this->drop = $drop;
        $this->feature = $feature;
    }
    
    public function handle(FeatureCollection $features)
    {
        $namespace = 'superv.' . $this->drop->getDropper()->getSlug();
        $class = $features->get("server.".$this->feature."@". $namespace);
        
        $this->dispatch(new $class($this->drop));
    }
}