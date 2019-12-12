<?php

namespace SuperV\Platform\Domains\Resource\Builder;

use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\RelationType;
use SuperV\Platform\Domains\Resource\ResourceModel;

class RelationBlueprint
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Builder\Blueprint
     */
    protected $parent;

    /**
     * @var string
     */
    protected $relationName;

    /**
     * @var string
     */
    protected $related;

    /**
     * @var \SuperV\Platform\Domains\Resource\Relation\Relation
     */
    protected $relation;

    /**
     * @var string
     */
    protected $type;

    public function __construct(Blueprint $parent)
    {
        $this->parent = $parent;
    }

    protected function boot() { }

    public function run(ResourceModel $resource)
    {
        $resource->resourceRelations()->create([
            'uuid'   => uuid(),
            'name'   => $this->getRelationName(),
            'type'   => $this->getType(),
            'config' => $this->getConfig(),
        ]);
    }

    final public function getConfig()
    {
        return array_merge(
            ['related_resource' => $this->getRelated()],
            $this->mergeConfig()
        );
    }

    public function mergeConfig(): array
    {
        return [];
    }

    public function relatedResource($relatedResource)
    {
        $this->related = $relatedResource;

        return $this;
    }

    public function getRelated(): string
    {
        return $this->related;
    }

    public function getRelation(): Relation
    {
        return $this->relation;
    }

    public function getRelationName(): string
    {
        return $this->relationName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getParent(): Blueprint
    {
        return $this->parent;
    }

    public static function make(Blueprint $resource, string $name, RelationType $type): RelationBlueprint
    {
        /** @var \SuperV\Platform\Domains\Resource\Builder\RelationBlueprint $blueprint */
        $blueprint = static::resolve($type, $resource);
        $blueprint->relationName = $name;
        $blueprint->relation = Relation::resolveType($type);
        $blueprint->type = $type->getValue();

        $blueprint->boot();

        return $blueprint;
    }

    public static function resolve($type, Blueprint $resource): RelationBlueprint
    {
        $typeClass = Relation::resolveTypeClass($type);

        $blueprintClass = sprintf("%sBlueprint", $typeClass);

        if (! class_exists($blueprintClass)) {
            $parts = explode("\\", $typeClass);
            $className = end($parts);
            $blueprintClass = str_replace_last($className, 'Config', $typeClass);
            if (! class_exists($blueprintClass)) {
                $blueprintClass = self::class;
            }
        }

        return new $blueprintClass($resource);
    }
}