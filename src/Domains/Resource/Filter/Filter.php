<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter as FilterContract;
use SuperV\Platform\Domains\Resource\Contracts\Filter\ProvidesField;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

abstract class Filter implements FilterContract, ProvidesField
{
    use FiresCallbacks;

    /**
     * Main identifier for the filter
     *
     * @var null
     */
    protected $identifier;

    /**
     * Corresponding entry attribute if not same with the identifier
     *
     * @var string
     */
    protected $attribute;

    /**
     * Filter type
     *
     * @var string
     */
    protected $type;

    /**
     * Filter label
     *
     * @var string
     */
    protected $label;
    /**
     * Filter placeholder
     *
     * @var string
     */
    protected $placeholder;

    /**
     * Resource the filter belongs to
     *
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    protected $callback;

    public function __construct($identifier = null, $label = null)
    {
        $this->identifier = $identifier;
        $this->label = $label;

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

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function onFieldBuilt(Field $field) {}

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
        return $this->placeholder ?? $this->getLabel();
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

    public function setResource(Resource $resource): FilterContract
    {
        $this->resource = $resource;

        return $this;
    }

    public function getLabel()
    {
        return $this->label ?? str_unslug($this->identifier);
    }

    public static function make($identifier = null, $label = null)
    {
        return new static($identifier, $label);
    }
}