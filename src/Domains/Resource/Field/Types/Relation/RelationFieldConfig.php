<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Relation;

use SuperV\Platform\Domains\Resource\Field\FieldConfig;

class RelationFieldConfig extends FieldConfig
{
    protected $related;

    protected $localKey;

    /** @var \SuperV\Platform\Domains\Resource\Field\Types\Relation\RelationType */
    protected $relationType;

    public function related($related): RelationFieldConfig
    {
        $this->related = $related;

        return $this;
    }

    public function localKey($localKey): RelationFieldConfig
    {
        $this->localKey = $localKey;

        return $this;
    }

    public function type(RelationType $type): RelationFieldConfig
    {
        $this->relationType = $type;

        return $this;
    }

    public static function make()
    {
        return new static;
    }
}
