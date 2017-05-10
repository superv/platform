<?php namespace SuperV\Platform\Domains\Feature\Command;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Feature\FeatureCollection;
use Vizra\SupervModule\Drop\DropModel;

class FormulateFeature
{
    use DispatchesJobs;
    
    /**
     * @var DropModel
     */
    private $drop;
    private $feature;
    
    public function __construct(DropModel $drop, $feature)
    {
        $this->drop = $drop;
        $this->feature = $feature;
    }
    
    public function handle(FeatureCollection $features)
    {
        $namespace = 'superv.' . $this->drop->getDropper()->getSlug();
        $class = $features->get("server." . $this->feature . "@" . $namespace);
        
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("$class does not exist");
        }
        $feature = new $class($this->drop);
        
        $jobs = $feature->getJobs();
        dd($jobs);
        //$this->dispatch(new $class($this->drop));
    }
}