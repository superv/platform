<?php

namespace SuperV\Platform\Domains\Authorization;

use Silber\Bouncer\Bouncer;
use SuperV\Platform\Support\Collection;

class HaydarBouncer implements Haydar
{
    /**
     * @var \Silber\Bouncer\Bouncer
     */
    protected $bouncer;

    public function __construct(Bouncer $bouncer)
    {
        $this->bouncer = $bouncer;
    }

    public function can($ability): bool
    {
        return $this->bouncer->can($ability);
    }

    public function guard($guardable)
    {
        return $this->guardItems(sv_collect($guardable))->map(function ($item) {
            if (is_array($item) || $item instanceof Collection) {
                return sv_guard($item);
            }
            $this->scanGuardableChildrenOf($item);

            return $item;
        });
    }

    public function authorize($item)
    {
        /** Allow non-guardable items */
        if (! $item instanceof Guardable) {
            return true;
        }

        if ($this->can($item->getRequiredAbility())) {
            return true;
        }

        return false;
    }

    public function guardItems(Collection $items)
    {
        return $items->filter(function ($item) {
            return $this->authorize($item);
        });
    }

    public function scanGuardableChildrenOf($item)
    {
        if (! $item instanceof HasGuardableItems) {
            return;
        }

        $scannedItems = sv_guard($item->getGuardableItems());
        $item->setGuardableItems($scannedItems->all());
    }
}