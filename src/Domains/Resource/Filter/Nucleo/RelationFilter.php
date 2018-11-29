<?php

namespace SuperV\Modules\Nucleo\Domains\Resource\Table\Filter;

use Illuminate\Database\Eloquent\Builder;
use SuperV\Platform\Exceptions\PlatformException;

class RelationFilter extends Filter
{
    protected $type = 'select';

    public function build()
    {
        if (!$column = $this->resource->prototype()->getColumn($this->getRelationName())) {
            throw new PlatformException("Relation [{$this->getRelationName()}] not found in prototype");
        }

        $relatedModel = $column->getRelationEntry()->getRelatedModel();

        if (! $relatedResource = superv('resources')->withModel($relatedModel)) {
            throw new PlatformException("Resource for model [{$relatedModel} not found]");
        }

        $options = $relatedModel::query()->get()->pluck($relatedResource::titleColumn(), 'id')->all();

        $this->config['options'] = $options;
    }

    public function apply(Builder $query, $value)
    {
        $query->where($this->slug.'_id', '=', $value);
    }

    public function getRelationName()
    {
        return $this->slug;
    }
}