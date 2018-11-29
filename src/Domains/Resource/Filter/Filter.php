<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter as FilterContract;
use SuperV\Platform\Domains\Resource\Contracts\Filter\ProvidesField;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

abstract class Filter implements FilterContract, ProvidesField
{
    use FiresCallbacks;

    protected $type;

    protected $placeholder;

    protected $callback;

    protected $identifier;

    protected $attribute;

    public function __construct($identifier = null)
    {
        $this->identifier = $identifier;

        $this->boot();
    }

    protected function boot() {}

    public function apply($query, $value)
    {
        if ($this->callback) {
            return ($this->callback)($query, $value);
        }

        if (str_contains($this->getIdentifier(), '.')) {
            return $this->applyRelationQuery($query, $this->getIdentifier(), $value);
        }
        $query->where($this->getAttribute(), '=', $value);
    }

    protected function applyRelationQuery($query, $slug, $value, $operator = '=', $method = 'whereHas')
    {
        list($relation, $column) = explode('.', $slug);
        $query->{$method}($relation, function ( $query) use ($column, $value, $operator) {
            $query->where($column, $operator, $value);
        });
    }


    public function makeField(): Field
    {
        $field = FieldFactory::createFromArray([
            'type'        => $this->getType(),
            'name'        => $this->getIdentifier(),
            'placeholder' => $this->getPlaceholder(),
        ]);

        $this->fire('field.built', ['field' => $field]);

        return $field;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPlaceholder()
    {
        return $this->placeholder ?? str_unslug($this->getIdentifier());
    }

    public function getAttribute()
    {
        return $this->attribute ?? $this->getIdentifier();
    }

    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    public static function make($identifier = null)
    {
        return new static($identifier);
    }
}