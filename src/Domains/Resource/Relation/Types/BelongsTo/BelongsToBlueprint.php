<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types\BelongsTo;

use SuperV\Platform\Domains\Resource\Blueprint\RelationBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\BelongsTo\BelongsToField;

class BelongsToBlueprint extends RelationBlueprint
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
     * @var \SuperV\Platform\Domains\Resource\Blueprint\FieldBlueprint
     */
    protected $field;

    protected function boot()
    {
        $this->field = $this->blueprint->addField('user', BelongsToField::class);
    }

    public function relatedResource($relatedResource)
    {
        parent::relatedResource($relatedResource);

        $this->field->setConfigValue('related_resource', $relatedResource);

        return $this;
    }

    public function mergeConfig(): array
    {
        return array_filter([
            'owner_key'   => $this->getOwnerKey(),
            'foreign_key' => $this->getForeignKey(),
        ]);
    }

    public function getOwnerKey(): ?string
    {
        return $this->ownerKey;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function ownerKey(string $ownerKey): BelongsToBlueprint
    {
        $this->ownerKey = $ownerKey;

        $this->field->setConfigValue('owner_key', $ownerKey);

        return $this;
    }

    public function foreignKey(string $foreignKey): BelongsToBlueprint
    {
        $this->foreignKey = $foreignKey;

        $this->field->setConfigValue('foreign_key', $foreignKey);

        return $this;
    }
}