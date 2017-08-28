<?php namespace SuperV\Platform\Domains\Manifest;

use SuperV\Platform\Support\Collection;

class ManifestCollection extends Collection
{
//    public function droplet()
//    {
//        $droplets = [];
//        foreach ($this->items as $item) {
//            if ($item instanceof DropletManifest) {
//                $droplets[] = $item;
//            }
//        }
//
//        return new self($droplets);
//    }

    public function model()
    {
        $models = [];
        foreach ($this->items as $item) {
            if ($item instanceof ModelManifest) {
                $models[] = $item;
            }
        }

        return new self($models);
    }

    public function byModel($model)
    {

        /** @var ModelManifest $item */
        foreach ($this->items as $item) {
            $manifestModel = $item->getModel();
            $manifestModel = is_object($manifestModel) ? get_class($manifestModel) : $manifestModel;
            if ($model == $manifestModel) {
                return $item;
            }
        }
    }
}