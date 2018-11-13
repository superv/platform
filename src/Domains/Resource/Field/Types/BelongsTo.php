<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class BelongsTo extends FieldType implements NeedsDatabaseColumn
{

    public function build(): FieldType
    {
        $this->buildOptions();

        return $this;
    }

    public function buildForView($query)
    {
        $query->with($this->getName());

        return parent::buildForView($query);
    }

    public function getPresentingCallback(): ?Closure
    {
        return function (?ResourceEntry $relatedEntry) {
            return $relatedEntry ? $relatedEntry->getLabel() : null;
        };
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
            if ($this->entryExists()) {
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
}