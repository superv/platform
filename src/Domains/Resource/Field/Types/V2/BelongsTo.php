<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\V2;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Field\Types\FieldTypeV2;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Table\Contracts\AltersTableQuery;
use SuperV\Platform\Support\Composer\Composition;

class BelongsTo extends FieldTypeV2 implements NeedsDatabaseColumn, AltersTableQuery
{
    public function getPresenter(): ?Closure
    {
        return function (EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                $resource = ResourceFactory::make($relatedEntry);

                return $resource->getEntryLabel($relatedEntry);
            }
        };
    }

    public function getColumnName(): ?string
    {
        return $this->field->getName().'_id';
    }

    public function getComposer(): ?Closure
    {
        return function (Composition $composition, EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                $resource = ResourceFactory::make($relatedEntry);
                $composition->replace('meta.link', $resource->route('view', $relatedEntry));
            }
            $this->buildOptions($composition);
        };
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

        $composition->replace('meta.options', $options);
        $composition->replace('placeholder', 'Choose a '.$relatedResource->getSingularLabel());
    }

    public function alterComposition(Composition $composition)
    {
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

    public function getAlterQueryCallback(): Closure
    {
        return function ($query) { $query->with($this->getName()); };
    }
}