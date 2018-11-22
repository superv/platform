<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Table\TableConfig;

class HasMany extends Relation implements ProvidesTable, ProvidesQuery
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        if (! $localKey = $this->config->getLocalKey()) {
            if ($this->parentEntry) {
                $entry = $this->parentEntry;
                $localKey = $entry->getKeyName();
            }
        }

        return new EloquentHasMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->config->getForeignKey(),
            $localKey ?? 'id'
        );
    }

    public function makeTableConfig(): TableConfig
    {
        $config = new TableConfig();
        $relatedResource = ResourceFactory::make($this->getConfig()->getRelatedResource());
        $config->setColumns($relatedResource);
        $config->setQuery($this);
        $config->setTitle($this->getName());

        $config->build();
        $config->setDataUrl(url()->current().'/data');

        $belongsTo = $config->getColumns()->first(function (Field $field) {
            if ($field->getType() !== 'belongs_to') {
                return null;
            }
            if ($field->getConfigValue('foreign_key') !== $this->config->getForeignKey()) {
                return null;
            }

            return $field;
        });
        $config->hideColumn($belongsTo->getName());

        return $config;
    }
}