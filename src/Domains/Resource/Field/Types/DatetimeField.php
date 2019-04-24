<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Carbon\Carbon;
use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasPresenter;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Support\Composer\Payload;

class DatetimeField extends FieldType implements RequiresDbColumn, HasPresenter
{
    protected function boot()
    {
        $this->field->on('view.accessing', $this->formPresenter());
        $this->field->on('form.accessing', $this->formPresenter());
        $this->field->on('form.composing', $this->formComposer());
    }

    protected function formComposer()
    {
        return function (Payload $payload, ?EntryContract $entry) {
            $payload->set('config.time', $this->getConfigValue('time'));
        };
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


    protected function getFormat()
    {
        $default = $this->hasTime() ? 'M j, Y H:i' : 'M j, Y';

        return $this->getConfigValue('format', $default);
    }

    protected function hasTime()
    {
        return $this->getConfigValue('time') === true;
    }

    public function getPresenter(): Closure
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
}