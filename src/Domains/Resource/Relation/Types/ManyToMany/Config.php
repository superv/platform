<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types\ManyToMany;

use Closure;
use SuperV\Platform\Domains\Resource\Builder\Pivot;
use SuperV\Platform\Domains\Resource\Builder\RelationBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\BelongsToMany\BelongsToManyField;

class Config extends RelationBlueprint
{
    /**
     * @var string
     */
    protected $ownerKey;

    /**
     * @var string
     */
    protected $foreignKey;

    /**
     * @var \SuperV\Platform\Domains\Resource\Builder\FieldBlueprint
     */
    protected $field;

    /**
     * @var \SuperV\Platform\Domains\Resource\Builder\Pivot
     */
    protected $pivot;

    protected function boot()
    {
        $this->field = $this->parent->addField($this->getRelationName(), BelongsToManyField::class);
    }

    public function mergeConfig(): array
    {
        $config = [
            'pivot_identifier'  => $this->pivot->getIdentifier(),
            'pivot_table'       => $this->pivot->getHandle(),
            'pivot_foreign_key' => $this->pivot->getForeignKey().'_id',
            'pivot_related_key' => $this->pivot->getRelatedKey().'_id',
        ];

        $this->field->mergeConfig($config);

        return $config;
    }

    public function relatedResource($relatedResource)
    {
        parent::relatedResource($relatedResource);

        $this->field->setConfigValue('related_resource', $relatedResource);

        return $this;
    }

    public function pivot(string $identifier, ?Closure $callback = null)
    {
        $this->pivot = (new Pivot())->identifier($identifier);

        $this->pivot->parent($this->getParent());
        $this->pivot->relation($this);

        if ($callback) {
            $callback($this->pivot);
        }

        return $this->pivot;
    }

    public function getPivot(): Pivot
    {
        return $this->pivot;
    }
}
