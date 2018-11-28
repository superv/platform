<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Carbon\Carbon;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;

class Datetime extends FieldType implements NeedsDatabaseColumn
{
    protected function boot()
    {
        $this->on('form.accessing', $this->formPresenter());
        $this->on('table.presenting', $this->presenter());
        $this->on('view.presenting', $this->presenter());
    }

    protected function formPresenter()
    {
        return function (EntryContract $entry) {
            if (! $value = $entry->getAttribute($this->getName())) {
                return null;
            }

            if (! $value instanceof Carbon) {
                $value = Carbon::parse($value);
            }

            return $value->format('Y-m-d H:i:s');
        };
    }

    protected function presenter()
    {
        return function (EntryContract $entry) {
            if (! $value = $entry->getAttribute($this->getName())) {
                return null;
            }

            if (! $value instanceof Carbon) {
                $value = Carbon::parse($value);
            }

            return $value->format($this->getFormat());
        };
    }

    public function getFormat()
    {
        $default = $this->hasTime() ? 'M j, Y H:i' : 'M j, Y';

        return $this->getConfigValue('format', $default);
    }

    public function hasTime()
    {
        return $this->getConfigValue('time') === true;
    }
}