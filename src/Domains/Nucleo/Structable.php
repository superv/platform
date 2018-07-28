<?php

namespace SuperV\Platform\Domains\Nucleo;

trait Structable
{
    public $__cache = []; /** hate */

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

    public function struct()
    {
        $prototype = Prototype::where('slug', $this->getTable())->first();

        return Struct::where('prototype_id', $prototype->id)
                     ->where('related_id', $this->id)
                     ->first();
    }
}