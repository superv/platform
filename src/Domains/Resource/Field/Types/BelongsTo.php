<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\Resource;

class BelongsTo extends Field
{
    /** @var \SuperV\Platform\Domains\Resource\Relation\RelationConfig */
    protected $relationConfig;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $relatedResource;

    public function build(): Field
    {
        $this->buildRelationConfig();

        $this->relatedResource = Resource::of($this->relationConfig->getRelatedResource());

        $this->buildOptions();

        $this->buildConfig();

        return parent::build();
    }

    public function buildForView(Builder $query)
    {
        $query->with($this->getName());

        return parent::buildForView($query);
    }

    public function presentValue()
    {
        $this->buildRelationConfig();

        $related = Resource::of($this->relationConfig->getRelatedResource());

        $titleColumn = $related->getTitleFieldName();

        $relatedEntry = $this->getResourceEntry()->getRelation($this->getName());


        return $relatedEntry ?  $relatedEntry->getAttribute($titleColumn) : null;
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
        $titleField = FieldModel::find($this->relatedResource->getTitleFieldId());

        $titleFieldName = $titleField ? $titleField->getName() : 'name';

        $query = $this->relatedResource->resolveModel()->newQuery();

        if ($this->hasCallback('querying')) {
            $this->fire('querying', ['query' => $query]);

            // If parent exists, make sure we get the
            // current related entry in the list
            if (optional($this->getEntry())->exists) {
                $query->orWhere($query->getModel()->getQualifiedKeyName(), $this->getEntry()->getAttribute($this->getName()));
            }
        } else {
            $query->get();
        }

        $options = $query->get()->map(function ($item) use ($titleFieldName) {
            return ['value' => $item->id, 'text' => $item->{$titleFieldName}];
        })->all();

        $this->setConfigValue('options', $options);
    }

    protected function buildConfig(): void
    {
        $this->setConfigValue('placeholder', 'Select '.$this->relatedResource->getSlug());
    }

    public function setValue($value): ?Closure
    {
        if ($value instanceof ResourceEntryModel) {
            $value = $value->getId();
        }

        return parent::setValue($value);
    }

    public function getType(): string
    {
        return 'select';
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