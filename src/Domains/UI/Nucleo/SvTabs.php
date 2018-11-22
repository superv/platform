<?php

namespace SuperV\Platform\Domains\UI\Nucleo;

use SuperV\Platform\Domains\Auth\Access\Guard\HasGuardableItems;

class SvTabs extends SvComponent implements HasGuardableItems
{
    protected $name = 'sv-tabs';

    public function slug($slug)
    {
        return $this->setProp('slug', $slug);
    }

    public function addTab(SvTab $tab)
    {
        $this->props['tabs'][] = [
            'fetch' => $tab->isAutoFetch(),
            'title' => $tab->getTitle(),
            'block' => $tab->getContent(),
        ];

        return $this;
    }

    public function build()
    {
        sv_collect($this->props['tabs'])->map(function ($tab) {
            $tab['block']->setResource($this->resource);
            $tab['block']->build();
        });
    }

    public function addTabs(array $tabs)
    {
        sv_collect($tabs)->map(function ($tab) { $this->addTab($tab); });

        return $this;
    }

    public function selected($tab)
    {
        return $this->setProp('initial-selected', $tab);
    }

    public function getGuardableItems()
    {
        return $this->props['tabs'];
    }

    public function setGuardableItems($items)
    {
        $this->props['tabs'] = sv_collect($items)->filter(function ($tab) {
            return isset($tab['block']);
        })->all();

        return $this;
    }
}