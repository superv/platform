<?php

namespace SuperV\Platform\Domains\UI\Page;

use SuperV\Platform\Support\Collection;

class PageCollection extends Collection
{
    public function byDroplet($slug)
    {
        $items = [];
        foreach ($this->items as $item) {
            if ($item->getDroplet()->getSlug() == $slug) {
                $items[] = $item;
            }
        }

        return new self($items);
    }

    public function byModel($model)
    {
        $items = [];
        foreach ($this->items as $item) {
            if ($item->getModel() == $model) {
                $items[$item->getVerb()] = $item;
            }
        }

        return new self($items);
    }
}
