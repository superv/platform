<?php namespace SuperV\Platform\Domains\Feature;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;

trait ServesFeaturesTrait
{
    use MarshalTrait;
    use DispatchesJobs;
    
    /**
     * Serve the given feature with the given arguments.
     *
     * @param \SuperV\Platform\Domains\Feature\Feature|string $feature
     * @param array                                           $arguments
     *
     * @return mixed
     */
    public function serve($feature, $arguments = [])
    {
        if (!is_object($feature)) {
            $feature = $this->marshal($feature, new Collection(), $arguments);
        }
        
        return $this->dispatch($feature);
    }
}
