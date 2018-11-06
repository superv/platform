<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;


use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\TableConfig;

class HasMany extends Relation implements ProvidesTable, ProvidesQuery
{
    protected function newRelationQuery(ResourceEntryModel $instance): EloquentRelation
    {
        return new EloquentHasMany(
            $instance->newQuery(),
            $this->getParentEntry(),
            $this->config->getForeignKey(),
            $this->resource->getEntry()->getKeyName()
        );
    }

    public function makeTableConfig(): TableConfig
    {
        $config = new TableConfig();
        $config->setResource(Resource::of($this->getConfig()->getRelatedResource()));
        $config->queryProvider($this);
        $config->setTitle($this->getName());

        $config->build();

        $belongsTo = $config->getColumns()->first(function(Field $field) {
            if ($field->getType() !== 'belongs_to') {
                return null;
            }
            if ($field->getConfigValue('foreign_key') !== $this->config->getForeignKey()) {
                return null;
            }

            return $field;
        });
        $config->removeColumn($belongsTo->getName());

        return $config;
    }
}