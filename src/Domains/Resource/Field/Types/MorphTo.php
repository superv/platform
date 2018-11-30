<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Support\Composer\Composition;

class MorphTo extends FieldType
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


    protected function boot()
    {
        $this->on('form.presenting', $this->presenter());
        $this->on('form.composing', $this->composer());

        $this->on('view.presenting', $this->viewPresenter());
        $this->on('view.composing', $this->composer());

        $this->on('table.presenting', $this->presenter());
        $this->on('table.composing', $this->composer());
        $this->on('table.querying', function ($query) {
            $query->with($this->getName());
        });
    }

    protected function composer()
    {
        return function (Composition $composition, EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                $resource = ResourceFactory::make($relatedEntry);
                $composition->set('meta.link', $resource->route('view', $relatedEntry));
            }
            $this->buildOptions($composition);
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

    protected function buildOptions(Composition $composition)
    {
        $relationConfig = RelationConfig::create($this->getType(), $this->getConfig());
        $relatedResource = ResourceFactory::make($relationConfig->getRelatedResource());

        $query = $relatedResource->newQuery();

        $query->get();

        $entryLabel = $relatedResource->getConfigValue('entry_label', '#{id}');
        $options = $query->get()->map(function ($item) use ($entryLabel) {
            return ['value' => $item->id, 'text' => sv_parse($entryLabel, $item->toArray())];
        })->all();

        $composition->set('meta.options', $options);
        $composition->set('placeholder', 'Choose a '.$relatedResource->getSingularLabel());
    }
}