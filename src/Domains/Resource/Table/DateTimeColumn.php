<?php

namespace SuperV\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use Illuminate\Support\Carbon;

class DateTimeColumn extends TableColumn
{
    protected $format = 'M j, Y H:i';

    protected function boot()
    {
        parent::boot();

        $this->presenter = function (EntryContract $entry) {
            $value = $entry->getAttribute($this->getName());

            if (! $value instanceof Carbon) {
                $value = \Carbon\Carbon::parse($value);
            }

            return $value->format($this->format);
        };
    }
}