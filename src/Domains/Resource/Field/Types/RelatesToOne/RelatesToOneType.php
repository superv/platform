<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne;

use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Database\Entry\EntryRepository;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesRelationQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class RelatesToOneType extends FieldType implements HandlesRpc, ProvidesRelationQuery
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
        return EntryRepository::for($this->field->getConfigValue('related'))
                              ->newQuery()
                              ->where(
                                  $this->field->getConfigValue('owner_key'),
                                  $parent->getAttribute($this->field->getConfigValue('local_key'))
                              )->first();
    }

    public function getRelationQuery(EntryContract $parent)
    {
        $config = $this->field->getConfig();

        return new EloquentBelongsTo(
            $this->getRelated()->newQuery(),
            $parent,
            $config['local_key'],
            $config['owner_key'],
            $this->getFieldHandle()
        );
    }



    public function getColumnName(): ?string
    {
        return $this->getConfigValue('local_key', $this->getFieldHandle().'_id');
    }

    public function getRelated(): \SuperV\Platform\Domains\Resource\Resource
    {
        $config = $this->field->getConfig();

        return $this->factory->withIdentifier($config['related']);
    }

    public function rpcOptions()
    {
        $config = $this->field->getConfig();
        $this->lookupOptions->setResource($this->factory->withIdentifier($config['related']));

        return $this->lookupOptions->make();
    }

    public function driverCreating(DriverInterface $driver, FieldBlueprint $blueprint)
    {
        if ($driver instanceof DatabaseDriver) {
            $driver->getTable()->addColumn($this->getFieldHandle().'_id', 'integer');
        }
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
}