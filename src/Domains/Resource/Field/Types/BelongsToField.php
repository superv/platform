<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Filter\SelectFilter;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Support\Composer\Payload;

class BelongsToField extends FieldType implements NeedsDatabaseColumn, ProvidesFilter, HandlesRpc
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $relatedResource;

    /** @var array */
    protected $options;

    public function getColumnName(): ?string
    {
        return $this->getConfigValue('foreign_key', $this->field->getName().'_id');
    }

    protected function boot()
    {
        $this->on('form.presenting', $this->presenter());
        $this->on('form.composing', $this->formComposer());

        $this->on('view.presenting', $this->viewPresenter());
        $this->on('view.composing', $this->viewComposer());

        $this->on('table.presenting', $this->presenter());
        $this->on('table.composing', $this->tableComposer());
        $this->on('table.querying', function ($query) {
            $query->with($this->getName());
        });
    }

    public function makeFilter(?array $params = [])
    {
        $this->buildOptions($params['query'] ?? null);

        return SelectFilter::make($this->getName(), $this->relatedResource->getSingularLabel())
                           ->setAttribute($this->getColumnName())
                           ->setOptions($this->options)
                           ->setDefaultValue($params['default_value'] ?? null);
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

    protected function formComposer()
    {
        return function (Payload $payload, ?EntryContract $entry = null) {
            if ($entry) {
                if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                    $resource = sv_resource($relatedEntry);
                    $payload->set('meta.link', $resource->route('view.page', $relatedEntry));
                }
            }
            $this->buildOptions();
            $payload->set('meta.options', $this->field->getResource()->route('fields', null,
                [
                    'field' => $this->getName(),
                    'rpc'   => 'options',
                ]));
//            $payload->set('meta.options', $this->options);
            $payload->set('placeholder', 'Select '.$this->relatedResource->getSingularLabel());
        };
    }

    protected function rpcOptions(array $params, array $request = [])
    {
        return $this->buildOptions($request['query'] ?? []);
    }

    protected function presenter()
    {
        return function (EntryContract $entry) {
            if (! $entry->relationLoaded($this->getName())) {
                $entry->load($this->getName());
            }
            if ($relatedEntry = $entry->getRelation($this->getName())) {
                return sv_resource($relatedEntry)->getEntryLabel($relatedEntry);
            }
        };
    }

    protected function viewPresenter()
    {
        return function (EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                return sv_resource($relatedEntry)->getEntryLabel($relatedEntry);
            }
        };
    }

    protected function viewComposer()
    {
        return function (Payload $payload, EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->getName()}()->newQuery()->first()) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view.page', $relatedEntry));
            }
        };
    }

    protected function tableComposer()
    {
        return function (Payload $payload, EntryContract $entry) {
            if (! $entry->relationLoaded($this->getName())) {
                $entry->load($this->getName());
            }
            if ($relatedEntry = $entry->getRelation($this->getName())) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view.page', $relatedEntry));
            }
        };
    }

    protected function buildOptions(?array $queryParams = [])
    {
        $this->relatedResource = $this->resolveRelatedResource();

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

    /**
     * @return \SuperV\Platform\Domains\Resource\Resource
     */
    protected function resolveRelatedResource()
    {
        $relationConfig = RelationConfig::create($this->getType(), $this->getConfig());

        return sv_resource($relationConfig->getRelatedResource());
    }
}