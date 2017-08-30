<?php

namespace SuperV\Platform\Domains\Asset;

use SuperV\Platform\Support\Collection;

class AssetCollection extends Collection
{
    public function add($file)
    {
        $this->push($file);

        return $this;
    }

    public function dump($additionalFilter = null)
    {
        // loop through leaves and dump each asset
        $parts = [];
        foreach ($this as $asset) {
            $parts[] = $asset->dump($additionalFilter);
        }

        return implode("\n", $parts);
    }

    public function getLastModified()
    {
        if ($this->isEmpty()) {
            return;
        }

        $mtime = 0;
        foreach ($this as $asset) {
            $assetMtime = $asset->getLastModified();
            if ($assetMtime > $mtime) {
                $mtime = $assetMtime;
            }
        }

        return $mtime;
    }
}
