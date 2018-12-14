<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Support\Composer\Payload;

class MorphTo extends FieldType implements DoesNotInteractWithTable
{
    protected function boot()
    {

        $this->on('view.presenting', $this->viewPresenter());
        $this->on('view.composing', $this->viewComposer());

        $this->on('table.presenting', $this->presenter());
        $this->on('table.composing', $this->tableComposer());
        $this->on('table.querying', function ($query) {
//            $query->with($this->getName());
        });

        $this->field->addFlag('form.hide');
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
            if ($relatedEntry = $this->getRelatedEntry($entry)) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view', $relatedEntry));
            }
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
}