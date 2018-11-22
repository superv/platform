<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersFieldComposition;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Table\Contracts\AltersTableQuery;
use SuperV\Platform\Support\Composition;

class BelongsTo extends FieldType implements NeedsDatabaseColumn, AltersTableQuery, AltersFieldComposition, AcceptsEntry
{
    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    public function build(): FieldType
    {
        $this->buildOptions();

        return $this;
    }

    public function getPresenter(): ?Closure
    {
        return function (EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}) {
                return Resource::of($relatedEntry)->getLabelOfEntry($relatedEntry);
            }
        };
    }

    public function setValue($value): ?Closure
    {
        if ($value instanceof EntryContract) {
            $value = $value->getId();
        }

        return parent::setValue($value);
    }

    public function getColumnName(): ?string
    {
        return $this->getName().'_id';
    }

    public function getAccessor(): ?Closure
    {
        return function ($value) {
            return (int)$value;
        };
    }

    public function getMutator(): ?Closure
    {
        return $this->getAccessor();
    }

    public function alterComposition(Composition $composition)
    {
        $relationConfig = RelationConfig::create($this->type, $this->config);
        $relatedResource = ResourceFactory::make($relationConfig->getRelatedResource());

        $query = $relatedResource->newQuery();

        if ($this->hasCallback('querying')) {
            $this->fire('querying', ['query' => $query]);

            // If parent exists, make sure we get the
            // current related entry in the list
            if ($this->entry->exists) {
                $query->orWhere($query->getModel()->getQualifiedKeyName(), $this->entry->getAttribute($this->getName()));
            }
        } else {
            $query->get();
        }

        $entryLabel = $relatedResource->getConfigValue('entry_label', '#{id}');
        $options = $query->get()->map(function ($item) use ($entryLabel) {
            return ['value' => $item->id, 'text' => sv_parse($entryLabel, $item->toArray())];
        })->all();

        $composition->replace('config.options', $options);
        $composition->replace('config.placeholder', 'Choose a '.$this->entry->getHandle());
    }

    /**
     * @throws \Exception
     */
    protected function buildOptions()
    {
        $relationConfig = RelationConfig::create($this->type, $this->config);

        $relatedResource = ResourceFactory::make($relationConfig->getRelatedResource());
        $entryLabel = $relatedResource->getConfigValue('entry_label', '#{id}');

        $query = $relatedResource->newQuery();

        if ($this->hasCallback('querying')) {
            $this->fire('querying', ['query' => $query]);

            // If parent exists, make sure we get the
            // current related entry in the list
            if ($this->entry->exists) {
                $query->orWhere($query->getModel()->getQualifiedKeyName(), $this->getEntry()->getAttribute($this->getName()));
            }
        } else {
            $query->get();
        }

        $options = $query->get()->map(function ($item) use ($entryLabel) {
            return ['value' => $item->id, 'text' => sv_parse($entryLabel, $item->toArray())];
        })->all();

        $this->setConfigValue('options', $options);

        $this->setConfigValue('placeholder', 'Choose a '.$this->entry->getHandle());
    }

    public function alterQuery($query)
    {
        $query->with($this->getName());
    }

    public function getAlterQueryCallback(): Closure
    {
        return function ($query) { $this->alterQuery($query); };
    }

    public function acceptEntry(EntryContract $entry)
    {
        $this->entry = $entry;
    }
}