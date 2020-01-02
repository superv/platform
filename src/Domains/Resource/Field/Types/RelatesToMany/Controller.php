<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany;

use SuperV\Platform\Domains\Resource\Field\FieldController;
use SuperV\Platform\Domains\Resource\Filter\ApplyFilters;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;

class Controller extends FieldController
{
    public function lookup()
    {
        $fieldIdentifier = sv_identifier($this->request->get('field'));
        $resource = ResourceFactory::make($fieldIdentifier->getParent());
        $entry = $resource->find($this->request->get('entry'));
        $field = $resource->getField($fieldIdentifier->getTypeId());
        $related = $field->getFieldType()->getRelated();

        $table = $related->resolveTable();
        $table->setDataUrl($this->request->getUri().'&data=1')
              ->makeSelectable();

        if ($this->request->get('data')) {
            /** @var \Illuminate\Database\Eloquent\Builder $query */
            $query = $table->getQuery();

            $alreadyAttachedItems = $entry->{$field->getHandle()}()
                                          ->pluck($related->config()->getTable().'.'.$related->config()->getKeyName());

            $query->whereNotIn($query->getModel()->getQualifiedKeyName(), $alreadyAttachedItems);

            return $table->setRequest($this->request)->build();
        }

        return MakeComponentTree::dispatch($table)->withTokens(['res' => $related->toArray()]);
    }

    public function attach()
    {
        $fieldIdentifier = sv_identifier($this->request->get('field'));
        $resource = ResourceFactory::make($fieldIdentifier->getParent());
        $entry = $resource->find($this->request->get('entry'));
        $field = $resource->getField($fieldIdentifier->getTypeId());
        $related = $field->getFieldType()->getRelated();

        // single or multiple
        if ($this->request->has('items')) {
            $items = $this->request->get('items');
        } else {
            $table = $related->resolveTable();
            $query = $table->getQuery();

            $selection = $this->request->get('selected');

            if ($selection['type'] === 'filter') {
                ApplyFilters::dispatch($table->getFilters(), $query, $this->request);

                $query->whereNotIn($query->getModel()->getQualifiedKeyName(), array_get($selection, 'excluding', []));
            } else {
                $query->whereIn($query->getModel()->getQualifiedKeyName(), array_get($selection, 'including', []));
            }

            $items = $query->pluck('id');
        }

//        if ($pivotColumns = $this->resolveRelation()->getRelationConfig()->getPivotColumns()) {
//            $formData = $this->request->get('form_data');
//            $_items = [];
//            foreach ($items as $item) {
//                $pivotData = [];
//                foreach ($pivotColumns as $column) {
//                    $pivotData[$column] = array_get($formData, $column);
//                }
//                $_items[$item] = $pivotData;
//            }
//
//            $items = $_items;
//        }

        $res = $entry->{$field->getHandle()}()->syncWithoutDetaching($items);

        return $res;
    }

    public function detach()
    {
        $fieldIdentifier = sv_identifier($this->request->get('field'));
        $resource = ResourceFactory::make($fieldIdentifier->getParent());
        $entry = $resource->find($this->request->get('entry'));
        $field = $resource->getField($fieldIdentifier->getTypeId());
        $related = $field->getFieldType()->getRelated();

        return $field->type()->getPivot()->newQuery()->where('id', $this->request->get('related'))->delete();
    }
}