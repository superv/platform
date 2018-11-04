<?php

namespace SuperV\Platform\Domains\Resource\Relation\Table;

use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\TableConfig;

class RelationTableConfig extends TableConfig
{
    /** @var \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected $relation;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $parentResource;

    public function __construct(Relation $relation)
    {
        $this->relation = $relation;
    }

//
//    public function setParentResource(Resource $parentResource): RelationTableConfig
//    {
//        $this->parentResource = $parentResource;
//
//        return $this;
//    }

    public function build(): TableConfig
    {
        $resource = Resource::of($this->relation->getConfig()->getRelatedResource());
        $this->setResource($resource);


        return parent::build();
    }

    public function newQuery()
    {
        return $this->relation->newQuery();
    }
}