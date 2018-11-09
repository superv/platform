<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;

class BelongsTo extends FieldType implements NeedsDatabaseColumn
{
    /** @var \SuperV\Platform\Domains\Resource\Relation\RelationConfig */
    protected $relationConfig;

    public function build(): FieldType
    {
        $this->buildRelationConfig();

        $this->buildOptions();

        $this->buildConfig();

        return $this;
    }

    public function buildForView($query)
    {
        $query->with($this->getName());

        return parent::buildForView($query);
    }

    public function presentValue()
    {
        $this->buildRelationConfig();

        /** @var ResourceEntryModel $relatedEntry */
        $relatedEntry = $this->getEntry()->getRelation($this->getName());

        return $relatedEntry ? $relatedEntry->wrap()->entryLabel() : null;
    }

    public function getPresentingCallback(): ?Closure
    {
        return function (?ResourceEntryModel $relatedEntry) {
            return $relatedEntry ? $relatedEntry->wrap()->entryLabel() : null;
        };
    }

    protected function buildRelationConfig()
    {
        $this->relationConfig = RelationConfig::create($this->type, $this->config);
    }

    /**
     * @throws \Exception
     */
    protected function buildOptions()
    {

//        $entryLabel = $this->relatedResource->getConfigValue('entry_label', '#{id}');
        $entryLabel = '#{id}';

        $query = $this->entry->newEntryInstance()->newQuery();

        if ($this->hasCallback('querying')) {
            $this->fire('querying', ['query' => $query]);

            // If parent exists, make sure we get the
            // current related entry in the list
            if (optional($this->getFieldEntry())->exists) {
                $query->orWhere($query->getModel()->getQualifiedKeyName(), $this->getFieldEntry()->getAttribute($this->getName()));
            }
        } else {
            $query->get();
        }

        $options = $query->get()->map(function ($item) use ($entryLabel) {
            return ['value' => $item->id, 'text' => sv_parse($entryLabel, $item->toArray())];
        })->all();

        $this->setConfigValue('options', $options);
    }

    protected function buildConfig(): void
    {
        $this->setConfigValue('placeholder', 'Select '.$this->entry->getHandle());
    }

    public function setValue($value): ?Closure
    {
        if ($value instanceof ResourceEntryModel) {
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