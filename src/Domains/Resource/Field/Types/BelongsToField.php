<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Filter\SelectFilter;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Support\Composer\Payload;

class BelongsToField extends FieldType implements NeedsDatabaseColumn, ProvidesFilter
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var array */
    protected $options;

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

    protected function viewComposer()
    {
        return function (Payload $payload, EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view', $relatedEntry));
            }
        };
    }

    protected function tableComposer()
    {
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
            $this->buildOptions();
            $payload->set('meta.options', $this->options);
            $payload->set('placeholder', 'Select '.$this->resource->getSingularLabel());
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

    protected function buildOptions()
    {
        $relationConfig = RelationConfig::create($this->getType(), $this->getConfig());
        $this->resource = sv_resource($relationConfig->getRelatedResource());

        $query = $this->resource->newQuery();
        $query->get();

        $entryLabel = $this->resource->getConfigValue('entry_label', '#{id}');
        $this->options = $query->get()->map(function ($item) use ($entryLabel) {
            return ['value' => $item->id, 'text' => sv_parse($entryLabel, $item->toArray())];
        })->all();

//        $this->options = array_merge(
//            [['value' => null, 'text' => 'Select '.$this->resource->getSingularLabel()]],
//            $this->options
//        );
    }

    public function makeFilter()
    {
        $this->buildOptions();

        return SelectFilter::make($this->getName(), 'Select '.$this->resource->getSingularLabel())
                           ->setAttribute($this->getColumnName())
                           ->setOptions($this->options);
    }
}