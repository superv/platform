<?php

namespace SuperV\Platform\Domains\Resource\Builder;

class Pivot extends Blueprint
{
    /**
     * Parent Resource
     *
     * @var \SuperV\Platform\Domains\Resource\Builder\Blueprint
     */
    protected $parent;

    /**
     * Parent Relation
     *
     * @var \SuperV\Platform\Domains\Resource\Builder\RelationBlueprint
     */
    protected $relation;

    /**
     * @var string
     */
    protected $resourceIdentifier;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $handle;

    protected $pivot = true;

    /**
     * @var string
     */
    protected $foreignKey;

    /**
     * @var string
     */
    protected $relatedKey;


    public function foreignKey(string $foreignKey): Pivot
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    public function relatedKey(string $relatedKey): Pivot
    {
        $this->relatedKey = $relatedKey;

        return $this;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey ?? str_singular($this->getParent()->getHandle());
    }

    public function getRelatedKey(): string
    {
        return $this->relatedKey ?? str_singular($this->relation->getRelationName());
    }

    public function parent(\SuperV\Platform\Domains\Resource\Builder\Blueprint $parent): Pivot
    {
        $this->parent = $parent;

        return $this;
    }
//
//    public function getParent(): \SuperV\Platform\Domains\Resource\Builder\Blueprint
//    {
//        return $this->parent;
//    }
//
//    public function relation(\SuperV\Platform\Domains\Resource\Builder\RelationBlueprint $relation): Pivot
//    {
//        $this->relation = $relation;
//
//        return $this;
//    }
}