<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\BelongsToMany;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Field\Types\RelationFieldType;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class BelongsToManyType extends RelationFieldType implements HandlesRpc, DoesNotInteractWithTable, HasModifier
{
    protected $handle = 'belongs_to_many';

    protected $component = 'sv_belongs_to_many_field';

    protected $value;

    protected function boot()
    {
//        $this->field->on('form.composing', $this->formComposer());
        $this->field->addFlag('view.hide');
    }

    public function getModifier(): Closure
    {
        return function ($value, ?EntryContract $entry) {
            $this->value = $value ? json_decode($value) : [];

            return function () use ($entry) {
                if (! $entry) {
                    return null;
                }

                $entry->{$this->getFieldHandle()}()->sync($this->value ?? []);
            };
        };
    }



    /**
     * @return \SuperV\Platform\Domains\Resource\Resource
     */
    public function resolveRelatedResource()
    {
        $relationConfig = RelationConfig::create($this->field->getType(), $this->field->getConfig());

        return sv_resource($relationConfig->getRelatedResource());
    }

    public function getRpcResult(array $params, array $request = [])
    {
        if (! $method = $params['method'] ?? null) {
            return;
        }

        if (method_exists($this, $method = 'rpc'.studly_case($method))) {
            return call_user_func_array([$this, $method], [$params, $request]);
        }
    }

//    protected function formComposer()
//    {
//        return function (Payload $payload, ?EntryContract $entry = null) {
//            $this->relatedResource = $this->resolveRelatedResource();
//
//            if ($entry) {
//                if ($relatedEntry = $entry->{$this->getFieldHandle()}()->newQuery()->first()) {
//                    $payload->set('meta.link', $relatedEntry->router()->view());
//                }
//            }
//
//            $payload->set('meta.options', $this->getOptionsUrl($entry));
//
//            if ($entry && $entry->exists) {
//                $payload->set('meta.values', $this->getValuesUrl($entry));
//            }
//            $payload->set('meta.full', true);
//            $payload->set('placeholder', 'Select '.$this->relatedResource->getSingularLabel());
//        };
//    }

    protected function rpcOptions(array $params, array $request = [])
    {
        $entry = null;
        if ($entryId = $request['entry'] ?? null) {
            $parentResource = ResourceFactory::make($this->field->identifier()->parent());
            $entry = $parentResource->find($request['entry']);
        }

        $resource = $this->resolveRelatedResource();
        $query = $resource->newQuery();

        if ($queryParams = $request['query'] ?? null) {
            $query->where($queryParams);
        }

        $keyName = $query->getModel()->getKeyName();
        $tableName = $query->getModel()->getTable();
        if ($entry) {
            $alreadyAttachedItems = $entry->{$this->getFieldHandle()}()
                                          ->pluck($tableName.'.'.$keyName);
            $query->whereNotIn($keyName, $alreadyAttachedItems);
        }

        $entryLabel = $resource->config()->getEntryLabel('#{id}');

        return $query->get()
                     ->map(function (EntryContract $item) use ($resource, $entryLabel) {
                         if ($keyName = $resource->config()->getKeyName()) {
                             // @todo.Ali aga bu neydi ?
                             $item->setKeyName($keyName);
                         }

                         return [
                             'value' => $item->getId(),
                             'text'  => sv_parse($entryLabel, $item->toArray()),
                         ];
                     })->all();
    }

    protected function rpcValues(array $params, array $request = [])
    {
        $parentResource = ResourceFactory::make($this->field->identifier()->parent());
        $relatedResource = $this->resolveRelatedResource();

        $entryLabel = $relatedResource->config()->getEntryLabel('#{id}');

        if (! $entry = $parentResource->find($request['entry'])) {
            return [];
        }

        return $entry->{$this->getFieldHandle()}()
                     ->get()
                     ->map(function (EntryContract $item) use ($relatedResource, $entryLabel) {
                         if ($keyName = $relatedResource->config()->getKeyName()) {
                             $item->setKeyName($keyName);
                         }

                         return [
                             'value' => $item->getId(),
                             'text'  => sv_parse($entryLabel, $item->toArray()),
                         ];
                     })->all();
    }
}
