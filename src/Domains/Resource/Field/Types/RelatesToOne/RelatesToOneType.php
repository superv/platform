<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne;

use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class RelatesToOneType extends FieldType implements HandlesRpc
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

    public function resolveDataFromRequest(FormData $data, Request $request, ?EntryContract $entry = null)
    {
        if (! $request->has($this->getFieldHandle()) && ! $request->has($this->getColumnName())) {
            return null;
        }

        [$value, $requestValue] = $this->resolveValueFromRequest($request, $entry);

        $data->toSave($this->getColumnName(), $value);
    }

    public function newQuery(EntryContract $parent)
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