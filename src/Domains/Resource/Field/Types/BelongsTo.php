<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Support\Composer\Payload;

class BelongsTo extends FieldType implements NeedsDatabaseColumn
{
    protected function presenter()
    {
        return function (EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}) {
                return sv_resource($relatedEntry)->getEntryLabel($relatedEntry);
            }
        };
    }

    protected function viewPresenter()
    {
        return function (EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                return sv_resource($relatedEntry)->getEntryLabel($relatedEntry);
            }
        };
    }

    public function getColumnName(): ?string
    {
        return $this->field->getName().'_id';
    }

    protected function boot()
    {
        $this->on('form.presenting', $this->presenter());
        $this->on('form.composing', $this->composer());

        $this->on('view.presenting', $this->viewPresenter());
        $this->on('view.composing', $this->viewComposer());

        $this->on('table.presenting', $this->presenter());
        $this->on('table.composing', $this->tableComposer());
        $this->on('table.querying', function ($query) {
            $query->with($this->getName());
        });
    }

    protected function viewComposer() {

        return function (Payload $payload, EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view', $relatedEntry));
            }
        };
    }

    protected function tableComposer() {

        return function (Payload $payload, EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view', $relatedEntry));
            }
        };
    }

    protected function composer()
    {
        return function (Payload $payload, EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view', $relatedEntry));
            }
            $this->buildOptions($payload);
        };

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
    }

    protected function buildOptions(Payload $payload)
    {
        $relationConfig = RelationConfig::create($this->getType(), $this->getConfig());
        $relatedResource = ResourceFactory::make($relationConfig->getRelatedResource());

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