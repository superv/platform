<?php namespace SuperV\Platform\Feature;

use Illuminate\Support\Collection;
use Vizra\SupervModule\Drop\DropModel;

class Feature
{
    /**
     * @var DropModel
     */
    protected $drop;
    
    /** @var Collection  */
    protected $jobs;
    
    public function __construct(DropModel $drop)
    {
        $this->drop = $drop;
        $this->jobs = collect();
    }
    
    public function getJobs(): Collection
    {
        return $this->jobs;
    }
}