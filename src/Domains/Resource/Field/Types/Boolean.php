<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;

class Boolean extends FieldType implements NeedsDatabaseColumn
{
    protected function boot()
    {
        $this->on('form.accessing', $this->accessor());
        $this->on('form.mutating', $this->accessor());
        $this->on('view.presenting', $this->presenter());
    }

    protected function accessor()
    {
        return function ($value) {
            return ($value === 'false' || ! $value) ? false : true;
        };
    }

    protected function presenter()
    {
        return function ($value) {
            return $value ? 'Yes' : 'No';
        };
    }
}