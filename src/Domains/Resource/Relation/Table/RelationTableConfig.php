<?php

namespace SuperV\Platform\Domains\Resource\Relation\Table;

use SuperV\Platform\Domains\Resource\Action\AttachEntryAction;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
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

    public function build(): TableConfig
    {
        $resource = Resource::of($this->relation->getConfig()->getRelatedResource());
        $fields = $resource->getFields();

        if ($pivotColumns = $this->relation->getConfig()->getPivotColumns()) {
            $pivotResource = Resource::of($this->relation->getConfig()->getPivotTable());
            $pivotFields = $pivotResource->getFields()
                                         ->filter(function (Field $field) use ($pivotColumns) {
                                             return in_array($field->getColumnName(), $pivotColumns);
                                         })
                                         ->map(function (Field $field) {
                                             return $field;
                                         })
                                         ->values()->all();

            $fields = $fields->merge($pivotFields);
        }

        $attachAction = AttachEntryAction::make()->setRelation($this->relation);

        $this->setColumns($fields);
        $this->setContextActions([$attachAction]);
        $this->setTitle($this->relation->getName());

        return parent::build();
    }

    public function newQuery()
    {
        return $this->relation->newQuery();
    }
}