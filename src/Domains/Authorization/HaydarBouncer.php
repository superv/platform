<?php

namespace SuperV\Platform\Domains\Authorization;

use Illuminate\Support\Collection;
use Silber\Bouncer\Bouncer;
use function sv_guard;

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
        return sv_collect($guardable)
            ->filter(function ($item) {
                if ($item instanceof Guardable && ! $this->can($item->ability())) {

                    return false;
                }

                return true;
            })
            ->map(function ($item) {
                if (is_array($item) || $item instanceof Collection) {
                    return sv_guard($item);
                }

                if ($item instanceof HasGuardableItems) {
                    $scannedItems = sv_guard($item->getGuardableItems());
                    $item->setGuardableItems($scannedItems->all());

                    return $item;
                }

//                dump($item);

                return $item;
            });
    }
}