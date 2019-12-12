<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types\HasMany;

use SuperV\Platform\Domains\Resource\Builder\RelationBlueprint;

class HasManyBlueprint extends RelationBlueprint
{
    /**
     * @var string
     */
    protected $localKey;

    /**
     * @var string
     */
    protected $foreignKey;

    public function relatedResource($relatedResource)
    {
        parent::relatedResource($relatedResource);

        return $this;
    }

    public function mergeConfig(): array
    {
        return array_filter([
            'local_key'   => $this->getLocalKey(),
            'foreign_key' => $this->getForeignKey(),
        ]);
    }

    public function getLocalKey(): ?string
    {
        return $this->localKey;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function localKey(string $ownerKey): HasManyBlueprint
    {
        $this->localKey = $ownerKey;

        return $this;
    }

    public function foreignKey(string $foreignKey): HasManyBlueprint
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }
}