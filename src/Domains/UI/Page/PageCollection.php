<?php namespace SuperV\Platform\Domains\UI\Page;

use SuperV\Platform\Support\Collection;

class PageCollection extends Collection
{
    public function byDroplet($slug)
    {
        $items = [];
        foreach($this->items as $item) {
            if ($item->getDroplet()->getSlug() == $slug) {
                $items[] = $item;
            }
        }

        return new self($items);
    }
}