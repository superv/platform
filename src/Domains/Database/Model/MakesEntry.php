<?php

namespace SuperV\Platform\Domains\Database\Model;

interface MakesEntry
{
    /**
     * Create and return an un-saved instance of the related model.
     *
     * @param  array $attributes
     * @return \SuperV\Platform\Domains\Database\Model\Entry
     */
    public function make(array $attributes = []);
}