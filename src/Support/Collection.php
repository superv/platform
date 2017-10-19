<?php

namespace SuperV\Platform\Support;

use SuperV\Platform\Adapters\LaravelCollection;

class Collection extends LaravelCollection
{
    public function bySlug($slug)
    {
        foreach ($this->items as $item) {
            if ($item->getSlug() == $slug) {
                return $item;
            }
        }

        return null;
    }
}
