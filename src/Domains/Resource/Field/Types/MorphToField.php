<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Support\Composer\Payload;

class MorphToField extends FieldType implements DoesNotInteractWithTable
{
    protected function boot()
    {
        $this->field->on('view.presenting', $this->viewPresenter());
        $this->field->on('view.composing', $this->viewComposer());

        $this->field->on('table.presenting', $this->presenter());
        $this->field->on('table.composing', $this->tableComposer());
        $this->field->on('table.querying', function ($query) {
//            $query->with($this->getName());
        });

        $this->addFlag('form.hide');
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
                $payload->set('meta.link', $resource->route('entry.view', $relatedEntry));
            }
        };
    }

    protected function tableComposer()
    {
        return function (Payload $payload, EntryContract $entry) {
            if ($relatedEntry = $this->getRelatedEntry($entry)) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('entry.view', $relatedEntry));
            }
        };
    }

    protected function getRelatedEntry(EntryContract $parentEntry, ?Resource $resource = null)
    {
        if (! $resource) {
            if (! $resource = $this->getRelatedResource($parentEntry)) {
                return null;
            }
        }
        $relatedEntryId = $parentEntry->{$this->getName().'_id'};

        return $resource->find($relatedEntryId);
    }

    protected function getRelatedResource(EntryContract $entry)
    {
        $type = $entry->{$this->getName().'_type'};

        if (class_exists($type)) {
            $type = (new $type)->getTable();
        }

        return sv_resource($type);
    }
}
