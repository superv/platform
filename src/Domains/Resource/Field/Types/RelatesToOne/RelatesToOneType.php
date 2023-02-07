<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne;

use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesFieldComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesRelationQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class RelatesToOneType extends FieldType implements
    HandlesRpc,
    ProvidesRelationQuery,
    ProvidesFieldComponent
{
    protected $handle = 'relates_to_one';

    protected $component = 'sv_relates_to_one_field';

    /**
     * @var \SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions
     */
    protected $lookupOptions;

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceFactory
     */
    protected $factory;

    public function __construct(MakeLookupOptions $lookupOptions, ResourceFactory $factory)
    {
        $this->lookupOptions = $lookupOptions;
        $this->factory = $factory;
    }

    public function getRelatedEntry(EntryContract $parent)
    {
        return $this->getRelationQuery($parent)->first();
    }

    public function getRelationQuery(EntryContract $parent)
    {
        return new EloquentBelongsTo(
            $this->getRelated()->newQuery(),
            $parent,
            $this->getConfigValue('foreign_key'),
            $this->getRelated()->config()->getKeyName(),
            $this->getFieldHandle()
        );
    }

    public function getColumnName(): ?string
    {
        return $this->getConfigValue('foreign_key', $this->getFieldHandle().'_id');
    }

    public function handleDatabaseDriver(DatabaseDriver $driver, FieldBlueprint $blueprint, array $options = [])
    {
        $driver->getTable()->addColumn($blueprint->getForeignKey() ?? $this->getFieldHandle().'_id', 'integer', $options);
    }

    public function getRelated(): \SuperV\Platform\Domains\Resource\Resource
    {
        return $this->factory->withIdentifier($this->getConfigValue('related'));
    }

    public function rpcOptions()
    {
        $config = $this->field->getConfig();

        $this->lookupOptions->setResource($this->factory->withIdentifier($config['related']));

        return $this->lookupOptions->make();
    }

    public function getRpcResult(array $params, array $request = [])
    {
        if (! $method = $params['method'] ?? null) {
            return null;
        }

        if (method_exists($this, $method = 'rpc'.studly_case($method))) {
            return call_user_func_array([$this, $method], [$params, $request]);
        }
    }

    public function getComponentName(): string
    {
        return $this->component;
    }
}