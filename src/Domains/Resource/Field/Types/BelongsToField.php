<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Modules\Nucleo\Domains\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class BelongsToField extends FieldType
{


    /** @var \SuperV\Modules\Nucleo\Domains\Relation\RelationConfig */
    protected $relationConfig;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $relatedResource;

    public function getType(): string
    {
        return 'select';
   }

    public function getValue()
    {
        if ($this->resourceExists()) {
            return $this->getResourceEntry()->getAttribute($this->getName().'_id');
        }
    }

    public function setValue($value): ?Closure
    {
        $this->getResourceEntry()->setAttribute($this->getName().'_id', (int)$value);

        return null;
    }

    public function build(): FieldType
    {
        $this->buildRelationConfig();

        $this->buildOptions();

        $this->setConfigValue('placeholder', 'Select '.$this->relatedResource->getSlug());

        return parent::build();
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
        $this->relatedResource = ResourceFactory::make($this->relationConfig->getRelatedResource());

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
}