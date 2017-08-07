<?php namespace SuperV\Platform\Domains\Manifest;

use SuperV\Platform\Support\Collection;

class ManifestCollection extends Collection
{
    public function byModel($model)
    {

        /** @var Manifest $item */
        foreach ($this->items as $item) {
            $manifestModel = $item->getModel();
            $manifestModel = is_object($manifestModel) ? get_class($manifestModel) : $manifestModel;
            if ($model == $manifestModel) {
                return $item;
            }
        }
    }
}