<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany;

use Closure;
use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Builder\Pivot;

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

    /**
     * @var \SuperV\Platform\Domains\Resource\Builder\Pivot
     */
    protected $pivot;

    public function related($related)
    {
        $this->related = $related;

        return $this;
    }

    public function mergeConfig(): array
    {
        return [
            'related'     => $this->related,
            'foreign_key' => $this->pivot ? null : $this->getForeignKey(),
            'pivot'       => $this->pivot ? $this->pivot->getIdentifier() : null,
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

    public function through(string $identifier, ?Closure $callback = null)
    {
        $this->pivot = (new Pivot())->identifier($identifier);
        $this->pivot->id();
        $this->pivot->relatesToOne($this->blueprint->getIdentifier(), $this->blueprint->getKey());
        $this->pivot->relatesToOne($this->getRelated(), str_singular($this->field->getHandle()));

        if ($callback) {
            $callback($this->pivot);
        }

        return $this->pivot;
    }

    public function getPivot(): ?Pivot
    {
        return $this->pivot;
    }
}