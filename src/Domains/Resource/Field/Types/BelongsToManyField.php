<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Support\Composer\Payload;

class BelongsToManyField extends FieldType implements HandlesRpc, DoesNotInteractWithTable, HasModifier
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $relatedResource;

    protected $value;

    protected function boot()
    {
        $this->field->on('form.composing', $this->formComposer());
    }

    public function getModifier(): Closure
    {
        return function ($value, EntryContract $entry) {
            $this->value =  $value ? explode(',', $value) : [];

            return function () use ($entry) {
                if (! $entry) {
                    return null;
                }

                $entry->{$this->getName()}()->sync($this->value ?? []);
            };
        };
    }

    protected function formComposer()
    {
        return function (Payload $payload, ?EntryContract $entry = null) {
            $this->relatedResource = $this->resolveRelatedResource();

            if ($entry) {
                if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                    $resource = sv_resource($relatedEntry);
                    $payload->set('meta.link', $resource->route('view.page', $relatedEntry));
                }
            }


            $payload->set('meta.options', $this->getOptionsUrl($entry));
            $payload->set('meta.values', $this->getValuesUrl($entry));
            $payload->set('meta.full', true);
            $payload->set('placeholder', 'Select '.$this->relatedResource->getSingularLabel());
        };
    }

    public function getOptionsUrl(?EntryContract $entry = null) {
        return sprintf("sv/forms/%s/fields/%s/options?entry=%o", $this->field->getForm()->uuid(), $this->getName(), $entry ? $entry->getId() : null);
    }

    public function getValuesUrl(?EntryContract $entry = null) {
        return sprintf("sv/forms/%s/fields/%s/values?entry=%o", $this->field->getForm()->uuid(), $this->getName(), $entry ? $entry->getId() : null);
    }

    /**
     * @return \SuperV\Platform\Domains\Resource\Resource
     */
    public function resolveRelatedResource()
    {
        $relationConfig = RelationConfig::create($this->field->getType(), $this->field->getConfig());

        return sv_resource($relationConfig->getRelatedResource());
    }


    protected function rpcOptions(array $params, array $request = [])
    {
        $entry  = $this->field->getResource()->find($request['entry']);

        $resource = $this->resolveRelatedResource();
        $query = $resource->newQuery();

        if ($queryParams = $request['query'] ?? null) {
            $query->where($queryParams);
        }

        $keyName = $query->getModel()->getKeyName();
        $alreadyAttachedItems = $entry->{$this->getName()}()
                                      ->pluck($resource->getHandle().'.'.$keyName);


        $query->whereNotIn($keyName, $alreadyAttachedItems);

        $entryLabel = $resource->getConfigValue('entry_label', '#{id}');

        return $query->get()->map(function (EntryContract $item) use ($resource, $entryLabel) {
            if ($keyName = $resource->getConfigValue('key_name')) {
                $item->setKeyName($keyName);
            }

            return ['value' => $item->getId(), 'text' => sv_parse($entryLabel, $item->toArray())];
        })->all();
    }

    protected function rpcValues(array $params, array $request = [])
    {
        $entry  = $this->field->getResource()->find($request['entry']);

        $resource = $this->resolveRelatedResource();

        $entryLabel = $resource->getConfigValue('entry_label', '#{id}');
     return  $entry->{$this->getName()}()
            ->get()->map(function (EntryContract $item) use ($resource, $entryLabel) {
                if ($keyName = $resource->getConfigValue('key_name')) {
                    $item->setKeyName($keyName);
                }

                return ['value' => $item->getId(), 'text' => sv_parse($entryLabel, $item->toArray())];
            })->all();


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
}