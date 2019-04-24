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
            $this->value = explode(',', $value);

            return function () use ($entry) {
                if (! $this->value || ! $entry) {
                    return null;
                }

                $entry->{$this->getName()}()->sync($this->value);
            };
        };
    }

    protected function formComposer()
    {
        return function (Payload $payload, ?EntryContract $entry = null) {
            if ($entry) {
                if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                    $resource = sv_resource($relatedEntry);
                    $payload->set('meta.link', $resource->route('view.page', $relatedEntry));
                }
            }
            $this->relatedResource = $this->resolveRelatedResource();

            $url = sprintf("sv/forms/%s/fields/%s/options", $this->field->getForm()->uuid(), $this->getName());
            $payload->set('meta.options', $url);
//            $payload->set('meta.options', $this->field->getResource()->route('fields', null,
//                [
//                    'field' => $this->getName(),
//                    'rpc'   => 'options',
//                ]));
//            $payload->set('meta.options', $this->options);
            $payload->set('meta.full', true);
            $payload->set('placeholder', 'Select '.$this->relatedResource->getSingularLabel());
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

    public function buildOptions(?array $queryParams = [])
    {
        $query = $this->relatedResource->newQuery();
        if ($queryParams) {
            $query->where($queryParams);
        }

        $entryLabel = $this->relatedResource->getConfigValue('entry_label', '#{id}');

        $this->options = $query->get()->map(function (EntryContract $item) use ($entryLabel) {
            if ($keyName = $this->relatedResource->getConfigValue('key_name')) {
                $item->setKeyName($keyName);
            }

            return ['value' => $item->getId(), 'text' => sv_parse($entryLabel, $item->toArray())];
        })->all();

        return $this->options;
    }

    protected function rpcOptions(array $params, array $request = [])
    {
        $this->relatedResource = $this->resolveRelatedResource();

        return $this->buildOptions($request['query'] ?? []);
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