<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Relation;

use SuperV\Platform\Domains\Resource\Field\FieldConfig;

class RelationFieldConfig extends FieldConfig
{
    /**
     * Related resource handle
     *
     * @var string
     */
    protected $related;

    /**
     * Parent key name on self
     *
     * @var string
     */
    protected $localKey;

    /**
     * Self key name on related
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * Pivot table for many-to-many relations
     *
     * @var string
     */
    protected $pivotTable;

    protected $pivotColumns = [];

    protected $pivotForeignKey;

    protected $pivotRelatedKey;

    /**
     * Relation type
     *
     * @var \SuperV\Platform\Domains\Resource\Field\Types\Relation\RelationType
     */
    protected $relationType;

    /**
     * Determine if this relation is required
     *
     * @var bool
     */
    protected $required = true;

    public function related($related): RelationFieldConfig
    {
        $this->related = $related;

        return $this;
    }

    public function withLocalKey($localKey): RelationFieldConfig
    {
        $this->localKey = $localKey;

        return $this;
    }

    public function type(RelationType $type): RelationFieldConfig
    {
        $this->relationType = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * @return mixed
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }

    public function withForeignKey(string $foreignKey): RelationFieldConfig
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * @return \SuperV\Platform\Domains\Resource\Field\Types\Relation\RelationType
     */
    public function getRelationType(): RelationType
    {
        return new RelationType($this->relationType);
    }

    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    public function required(bool $required = true): RelationFieldConfig
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    public function withPivotTable(string $pivotTable = null): RelationFieldConfig
    {
        $this->pivotTable = $pivotTable;

        return $this;
    }

    /**
     * @return string
     */
    public function getPivotTable(): ?string
    {
        return $this->pivotTable;
    }

    /**
     * @return array
     */
    public function getPivotColumns(): array
    {
        return $this->pivotColumns;
    }

    /**
     * @return mixed
     */
    public function getPivotForeignKey()
    {
        if ($this->pivotForeignKey) {
            return $this->pivotForeignKey;
        }

        return str_singular($this->getSelf()).'_id';
    }

    /**
     * @return mixed
     */
    public function getPivotRelatedKey()
    {
        if ($this->pivotRelatedKey) {
            return $this->pivotRelatedKey;
        }

        $related = $this->getRelated();

        return str_singular(explode('.', $related)[1]).'_id';
    }

    public static function make()
    {
        return new static;
    }
}
