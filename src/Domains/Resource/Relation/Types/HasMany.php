<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\ModalAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
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
        $url = route('relation.create', ['resource' => $this->getParentResourceHandle(),
                                         'id'       => $this->parentEntry->getId(),
                                         'relation' => $this->getName()], false);
        $action = ModalAction::make()->setModalUrl($url);

        $config = TableConfig::make()
                             ->setContextActions([$action])
                             ->setColumns(ResourceFactory::make($this->getConfig()->getRelatedResource()))
                             ->setQuery($this)
                             ->setTitle($this->getName())
                             ->build()
                             ->setDataUrl(url()->current().'/data');

        return $config;
    }
}