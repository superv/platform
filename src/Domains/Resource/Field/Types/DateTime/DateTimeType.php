<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\DateTime;

use Carbon\Carbon;
use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasPresenter;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesFieldComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Support\Composer\Payload;

class DateTimeType extends FieldType implements
    RequiresDbColumn,
    HasPresenter,
    SortsQuery,
    ProvidesFieldComponent
{
    protected $handle = 'datetime';

    protected $component = 'sv_datetime_field';

    protected function boot()
    {
        $this->field->on('table.presenting', $this->getPresenter());
        $this->field->on('view.presenting', $this->formPresenter());
        $this->field->on('form.accessing', $this->formPresenter());
        $this->field->on('form.composing', $this->formComposer());
    }

    public function handleDatabaseDriver(DatabaseDriver $driver, FieldBlueprint $blueprint, array $options = [])
    {
        $driver->getTable()->addColumn($this->getColumnName(), 'datetime', $options);
    }

    public function sortQuery($query, $direction)
    {
        $query->orderBy($this->field->getColumnName(), $direction);
    }

    public function getPresenter(): Closure
    {
        return function (EntryContract $entry) {
            if (! $value = $entry->getAttribute($this->getFieldHandle())) {
                return null;
            }

            if (! $value instanceof Carbon) {
                $value = Carbon::parse($value);
            }

            return $value->format($this->getFormat());
        };
    }

    public function getComponentName(): string
    {
        return $this->component;
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
            if (! $value = $entry->getAttribute($this->getFieldHandle())) {
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
}
