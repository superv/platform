<?php namespace SuperV\Platform\Domains\Feature;

use Illuminate\Support\Collection;

class FeatureCollection extends Collection
{
    public function routable()
    {
        $routables = new FeatureCollection();

        foreach ($this->items as $item) {
            if ($route = $item::$route) {
                $routables->put($route, $item);
            }
        }

        return $routables;
    }
}