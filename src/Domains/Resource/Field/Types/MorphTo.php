<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Support\Composer\Payload;

class MorphTo extends FieldType implements DoesNotInteractWithTable
{
    protected function boot()
    {
//        $this->on('form.presenting', $this->presenter());
//        $this->on('form.composing', $this->composer());

        $this->on('view.presenting', $this->viewPresenter());
        $this->on('view.composing', $this->viewComposer());

        $this->on('table.presenting', $this->presenter());
        $this->on('table.composing', $this->tableComposer());
        $this->on('table.querying', function ($query) {
//            $query->with($this->getName());
        });
    }

    protected function presenter()
    {
        return function (EntryContract $entry) {
            if ($relatedEntry = $this->getRelatedEntry($entry)) {
                return sv_resource($relatedEntry)->getEntryLabel($relatedEntry);
            }
        };
    }

    protected function viewPresenter()
    {
        return function (EntryContract $entry) {
            if ($relatedEntry = $this->getRelatedEntry($entry)) {
                return sv_resource($relatedEntry)->getEntryLabel($relatedEntry);
            }
        };
    }

    protected function viewComposer()
    {
        return function (Payload $payload, EntryContract $entry) {
            if ($relatedEntry = $this->getRelatedEntry($entry)) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view', $relatedEntry));
            }
        };
    }

    protected function tableComposer()
    {
        return function (Payload $payload, EntryContract $entry) {
//            if ($relatedEntry = $entry->{$this->getName()}) {
            if ($relatedEntry = $this->getRelatedEntry($entry)) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view', $relatedEntry));
            }
        };
    }

    protected function composer()
    {
        return function (Payload $payload, EntryContract $entry) {
            $relatedResource = $this->getRelatedResource($entry);

            if ($relatedEntry = $this->getRelatedEntry($entry, $relatedResource)) {
                $resource = ResourceFactory::make($relatedEntry);
                $payload->set('meta.link', $resource->route('view', $relatedEntry));
            }
            $this->buildOptions($payload, $relatedResource);
        };

    }

    protected function getRelatedEntry(EntryContract $parentEntry, ?Resource $resource = null)
    {
        if (! $resource) {
            $resource = $this->getRelatedResource($parentEntry);
        }
        $relatedEntryId = $parentEntry->{$this->getName().'_id'};

        return $resource->find($relatedEntryId);
    }

    protected function getRelatedResource(EntryContract $entry)
    {
        return sv_resource($entry->{$this->getName().'_type'});
    }

    protected function buildOptions(Payload $payload, Resource $relatedResource)
    {
        $query = $relatedResource->newQuery();

        $query->get();

        $entryLabel = $relatedResource->getConfigValue('entry_label', '#{id}');
        $options = $query->get()->map(function ($item) use ($entryLabel) {
            return ['value' => $item->id, 'text' => sv_parse($entryLabel, $item->toArray())];
        })->all();

        $payload->set('meta.options', $options);
        $payload->set('placeholder', 'Choose a '.$relatedResource->getSingularLabel());
    }
}



//        if ($this->hasCallback('querying')) {
//            $this->fire('querying', ['query' => $query]);
//
//            // If parent exists, make sure we get the
//            // current related entry in the list
//            if ($this->entry->exists) {
//                $query->orWhere($query->getModel()->getQualifiedKeyName(), $this->entry->getAttribute($this->getName()));
//            }
//        } else {
//            $query->get();
//        }
//