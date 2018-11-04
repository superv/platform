<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use SuperV\Platform\Support\Concerns\Hydratable;

class RelationConfig
{
    use Hydratable;

    /**
     * The name of the relation as called on parent
     *
     * @var string
     */
    protected $name;

    /**
     * Type of the relation
     *
     * @var \SuperV\Platform\Domains\Resource\Relation\RelationType
     */
    protected $type;

    /**
     * Model of the parent
     *
     * @var string
     */
    protected $parentModel;

    /**
     * Model of the related resource
     *
     * @var string
     */
    protected $relatedModel;

    /**
     * Slug of the related resource
     *
     * @var string
     */
    protected $relatedResource;

    /**
     * Foreign key
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * Local key
     *
     * @var string
     */
    protected $localKey;

    /**
     * Owner key for belongsto relations
     *
     * @var string
     */
    protected $ownerKey;

    /**
     * Pivot table name
     *
     * @var string
     */
    protected $pivotTable;

    /**
     * Pivot foreign key
     *
     * @var string
     */
    protected $pivotForeignKey;

    /**
     * Pivot related key
     *
     * @var string
     */
    protected $pivotRelatedKey;

    /**
     * Pivot columns
     *
     * @var array|\Closure
     */
    protected $pivotColumns;

    /**
     * Morph name
     *
     * @var string
     */
    protected $morphName;

    public function __construct(RelationType $type)
    {
        $this->type = $type;
    }

    public function foreignKey(?string $foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    public function getForeignKey(): ?string
    {
        return $this->foreignKey;
    }

    public function getLocalKey(): ?string
    {
        return $this->localKey;
    }

    public function getMorphName(): ?string
    {
        return $this->morphName;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getOwnerKey(): ?string
    {
        return $this->ownerKey;
    }

    public function getParentModel(): ?string
    {
        return $this->parentModel;
    }

    public function getPivotColumns()
    {
        return $this->pivotColumns;
    }

    public function getPivotForeignKey(): ?string
    {
        return $this->pivotForeignKey;
    }

    public function getPivotRelatedKey(): ?string
    {
        return $this->pivotRelatedKey;
    }

    public function getPivotTable(): ?string
    {
        return $this->pivotTable;
    }

    public function getRelatedModel(): ?string
    {
        return $this->relatedModel;
    }

    public function type(): RelationType
    {
        return $this->type;
    }

    public function getType(): string
    {
        return (string)$this->type;
    }

    public function hasPivotTable(): bool
    {
        return $this->type->isBelongsToMany() || $this->type->isMorphToMany();
    }

    public function localKey(?string $localKey): self
    {
        $this->localKey = $localKey;

        return $this;
    }

    public function morphName(string $morphName): self
    {
        $this->morphName = $morphName;

        return $this;
    }

    public function ownerKey(?string $ownerKey): self
    {
        $this->ownerKey = $ownerKey;

        return $this;
    }

    public function parentModel(string $parentModel): self
    {
        $this->parentModel = $parentModel;

        return $this;
    }

    public function pivotColumns($pivotColumns): self
    {
        $this->pivotColumns = $pivotColumns;

        return $this;
    }

    public function hasPivotColumns(): bool
    {
        return ! empty($this->pivotColumns);
    }

    public function pivotForeignKey(string $pivotForeignKey): self
    {
        $this->pivotForeignKey = $pivotForeignKey;

        return $this;
    }

    public function pivotRelatedKey(string $pivotRelatedKey): self
    {
        $this->pivotRelatedKey = $pivotRelatedKey;

        return $this;
    }

    public function pivotTable(string $pivotTable): self
    {
        $this->pivotTable = $pivotTable;

        return $this;
    }

    public function related(string $related): self
    {
        if (class_exists($related)) {
            return $this->relatedModel($related);
        }

        return $this->relatedResource($related);
    }

    public function relatedModel(string $relatedModel): self
    {
        $this->relatedModel = $relatedModel;

        return $this;
    }

    public function relationName(string $relationName): self
    {
        $this->name = $relationName;

        return $this;
    }

    public function relatedResource(string $relatedResource): self
    {
        $this->relatedResource = $relatedResource;

        return $this;
    }

    public function getRelatedResource(): ?string
    {
        if ($this->relatedResource) {
            return $this->relatedResource;
        }

        if ($this->relatedModel && class_exists($this->relatedModel)) {
            return (new $this->relatedModel)->getTable();
        }

        return null;
    }

    public function toArray()
    {
        $vars = get_object_vars($this);
        $_vars = [];
        foreach ($vars as $key => $value) {
            if (in_array($key, ['name', 'type'])) {
                continue;
            }
            $_vars[snake_case($key)] = $value;
        }

        return array_filter($_vars);
    }

    public static function make(RelationType $type): self
    {
        return (new static($type));
    }

    public static function create($type, array $data)
    {
        return (new static(new RelationType($type)))->hydrate($data);
    }

    public static function belongsTo(): self
    {
        return static::make(RelationType::belongsTo());
    }

    public static function hasMany(): self
    {
        return static::make(RelationType::hasMany());
    }

    public static function belongsToMany(): self
    {
        return static::make(RelationType::belongsToMany());
    }

    public static function morphToMany(): self
    {
        return static::make(RelationType::morphToMany());
    }
}