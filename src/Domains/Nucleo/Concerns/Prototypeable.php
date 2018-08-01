<?php

namespace SuperV\Platform\Domains\Nucleo\Concerns;

use SuperV\Platform\Domains\Nucleo\Observer;
use SuperV\Platform\Domains\Nucleo\Prototype;

trait Prototypeable
{
    protected static function boot()
    {
        static::observe(Observer::class);

        parent::boot();
    }

    public function fields()
    {
        $prototype = Prototype::where('slug', $this->getTable())->first();

        return $prototype->fields;
    }

    public function prototype()
    {
        return Prototype::where('slug', $this->getTable())->first();
    }
}