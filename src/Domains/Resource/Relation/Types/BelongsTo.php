<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Filter\SelectFilter;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class BelongsTo extends Relation implements AcceptsParentEntry, ProvidesFilter
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        return new EloquentBelongsTo(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->config->getForeignKey(),
            'id',
            $this->getName()
        );
    }

    public function makeFilter()
    {
        $resource = sv_resource($this->getConfig()->getRelatedResource());
        $options = $resource->newQuery()->get()->map(function (EntryContract $entry) use ($resource) {
            return ['value' => $entry->getId(), 'text' => $resource->getEntryLabel($entry)];
        })->all();

        $options = array_merge([['value' => null, 'text' => $resource->getSingularLabel()]], $options);

        return SelectFilter::make($this->getName())
                           ->setOptions($options)
                           ->setAttribute($this->getConfig()->getForeignKey());
    }
}