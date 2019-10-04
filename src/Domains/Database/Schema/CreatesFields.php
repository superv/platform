<?php

namespace SuperV\Platform\Domains\Database\Schema;

use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Support\ValueObject;

/**
 * Trait CreatesFields
 * @method ColumnDefinition string($column, $length = null)
 * @method ColumnDefinition unsignedInteger($column, $autoIncrement = false)
 */
trait CreatesFields
{
    public function email($name): ColumnDefinition
    {
        return $this->string($name)->fieldType('email');
    }

    public function file($name, $path = '/', $disk = 'public'): ColumnDefinition
    {
        return $this->addColumn(null, $name)
                    ->fieldType('file')
                    ->ignore()
                    ->nullable()
                    ->config(MediaOptions::one($name)
                                         ->public()
                                         ->disk($disk)
                                         ->path($path)
                                         ->all());
    }

    public function image($name, $path = '/', $disk = 'public'): ColumnDefinition
    {
        return $this->file(...func_get_args())->rules(['image', 'mimes:jpeg,png,gif']);
    }

    public function select($name, $options = []): ColumnDefinition
    {
        return $this->string($name)->fieldType('select')->setConfigValue('options', $options);
    }

    public function status($valueObjectClass = null)
    {
        $column = $this->select('status');
        if ($valueObjectClass) {
            /** @var ValueObject $valueObjectClass */
            $column->options($valueObjectClass::choices());
            $column->setConfigValue('value_object', $valueObjectClass);
        }

        return $column;
    }

    public function enum($column, array $allowed)
    {
        return $this->select($column)->options($allowed);
    }

    public function decimal($column, $total = 8, $places = 2): ColumnDefinition
    {
        return $this->addColumn('decimal', $column, compact('total', 'places'));
    }

    public function boolean($column): ColumnDefinition
    {
        return $this->addColumn('boolean', $column);
    }

    public function money($column)
    {
        return $this->decimal($column, 10, 2)->default(0);
    }

    public function number($column): ColumnDefinition
    {
        return $this->integer($column);
    }

    public function text($column): ColumnDefinition
    {
        return $this->addColumn('text', $column);
    }

    public function dictionary($column)
    {
        return $this->text($column)->fieldType('dictionary')->nullable();
    }

    public function timestamp($column, $precision = 0): ColumnDefinition
    {
        return $this->addColumn('timestamp', $column, compact('precision'));
    }

    public function createdBy(): self
    {
        $this->nullableBelongsTo('platform.users', 'created_by')->hideOnForms();
        $this->timestamp('created_at')->nullable()->hideOnForms();

        return $this;
    }

    public function increments($column)
    {
        $this->resourceConfig()->keyName($column);

        return parent::increments($column);
    }

    public function hasUuid($column = 'uuid'): ColumnDefinition
    {
        $this->resourceConfig()->setHasUuid(true);

        return $this->addColumn('uuid', $column);
    }

    public function restorable()
    {
        $this->resourceConfig()->restorable(true);

//        $this->nullableBelongsTo('users', 'deleted_by')->hideOnForms();
//        $this->timestamp('deleted_at')->nullable()->hideOnForms();
    }

    public function sortable()
    {
        $this->resourceConfig()->sortable(true);
//        $this->unsignedBigInteger('sort_order')->default(0);
    }

    public function updatedBy(): self
    {
        $this->nullableBelongsTo('platform.users', 'updated_by')->hideOnForms();
        $this->timestamp('updated_at')->nullable()->hideOnForms();

        return $this;
    }

    public function id($key = 'id')
    {
        $this->increments($key);
    }
}
