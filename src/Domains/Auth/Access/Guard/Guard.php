<?php

namespace SuperV\Platform\Domains\Auth\Access\Guard;

use Illuminate\Support\Collection;

class Guard
{
    protected $suspect;

    public function __construct($suspect)
    {
        $this->suspect = $suspect;
    }

    public function guard($guardable)
    {
        if ($guardable instanceof Guardable && $guardable->guardKey()) {
            return $this->suspect->can($guardable->guardKey());
        }

        return true;
    }

    public function filterArray(array $items)
    {
        $filteredArray = [];
        foreach ($items as $key => $item) {
            $filtered = $this->filter($item);

            if ($filtered !== false) {
                if (is_numeric($key)) {
                    $filteredArray[] = $filtered;
                } else {
                    $filteredArray[$key] = $filtered;
                }
            }
        }

        return $filteredArray;
    }

    public function filterCollection(Collection $items)
    {
        return $items->filter(function ($item) {
            $filtered = $this->filter($item);

            return $filtered;
        })->values();
    }

    public function filter($mixed)
    {
        if (is_array($mixed)) {
            return $this->filterArray($mixed);
        }

        if ($mixed instanceof Collection) {
            return $this->filterCollection($mixed);
        }

        if ($mixed instanceof HasGuardableItems) {
            $mixed->setGuardableItems($this->filter($mixed->getGuardableItems()));

            return $mixed;
        }

        return $this->guard($mixed) ? $mixed : false;
    }
}