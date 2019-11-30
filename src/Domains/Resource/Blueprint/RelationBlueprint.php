<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\RelationType;
use SuperV\Platform\Domains\Resource\ResourceModel;

class RelationBlueprint
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Blueprint\Blueprint
     */
    protected $blueprint;

    /**
     * @var string
     */
    protected $relationName;

    /**
     * @var string
     */
    protected $relatedResource;

    /**
     * @var \SuperV\Platform\Domains\Resource\Relation\Relation
     */
    protected $relation;

    /** @var string */
    protected $type;

    public function __construct(Blueprint $blueprint)
    {
        $this->blueprint = $blueprint;
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
            ['related_resource' => $this->getRelatedResource()],
            $this->mergeConfig()
        );
    }

    public function mergeConfig(): array
    {
        return [];
    }

    public function relatedResource($relatedResource)
    {
        $this->relatedResource = $relatedResource;

        return $this;
    }

    public function getRelatedResource(): string
    {
        return $this->relatedResource;
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

    public static function make(Blueprint $blueprint, string $name, RelationType $type): RelationBlueprint
    {
        $relation = Relation::resolveType($type);
        $typeClass = Relation::resolveTypeClass($type);

        $blueprintClass = sprintf("%sBlueprint", $typeClass);

        if (! class_exists($blueprintClass)) {
            $blueprintClass = self::class;
        }

        /** @var \SuperV\Platform\Domains\Resource\Blueprint\RelationBlueprint $blueprint */
        $blueprint = new $blueprintClass($blueprint);
        $blueprint->relationName = $name;
        $blueprint->relation = $relation;
        $blueprint->type = $type->getValue();

        $blueprint->boot();

        return $blueprint;
    }
}