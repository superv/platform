<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany;

use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;

class Blueprint extends FieldBlueprint
{
    /**
     * @var string
     */
    protected $related;

    /**
     * @var string
     */
    protected $foreignKey;

    public function related($related)
    {
        $this->related = $related;

        return $this;
    }

    public function mergeConfig(): array
    {
        return [
            'related'     => $this->related,
            'foreign_key' => $this->getForeignKey(),
        ];
    }

    public function getRelated(): string
    {
        return $this->related;
    }

    public function foreignKey(string $foreignKey): Blueprint
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey ?? $this->blueprint->getKey().'_id';
    }
}