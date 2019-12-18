<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;

abstract class RelationFieldType extends FieldType
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $relatedResource;

    /** @var RelationConfig */
    protected $relationConfig;

    /**
     * @return \SuperV\Platform\Domains\Resource\Resource
     * @throws \Exception
     */
    public function getRelatedResource()
    {
        if (! $this->relatedResource) {
            $this->relatedResource = ResourceFactory::make($this->getRelationConfig()->getRelatedResource());
        }

        return $this->relatedResource;
    }

    protected function getRelationConfig(): RelationConfig
    {
        if (! $this->relationConfig) {
            $this->relationConfig = RelationConfig::create($this->field->getType(), $this->field->getConfig());
        }

        return $this->relationConfig;
    }

    protected function getRelatedEntryLabel(?EntryContract $relatedEntry = null)
    {
        if (is_null($relatedEntry)) {
            return null;
        }

        return sv_resource($relatedEntry)->getEntryLabel($relatedEntry);
    }
}