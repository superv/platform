<?php

namespace SuperV\Platform\Domains\Nucleo\Concerns;

use SuperV\Platform\Domains\Nucleo\Observer;
use SuperV\Platform\Domains\Nucleo\Prototype;
use SuperV\Platform\Domains\Nucleo\Struct;

trait Structable
{
    public $__cache = []; /** hate */

    public function struct()
    {
        $prototype = Prototype::where('slug', $this->getTable())->first();

        return Struct::where('prototype_id', $prototype->id)
                     ->where('related_id', $this->id)
                     ->first();
    }
}