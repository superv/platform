<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany;

use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesRelationQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class RelatesToManyType extends FieldType implements
    HandlesRpc,
    ProvidesRelationQuery
{
    protected $handle = 'relates_to_many';

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceFactory
     */
    protected $factory;

    public function __construct(MakeLookupOptions $lookupOptions, ResourceFactory $factory)
    {
        $this->lookupOptions = $lookupOptions;
        $this->factory = $factory;
    }

    public function getRelatedEntries(EntryContract $parent)
    {
        return $this->getRelationQuery($parent)->get();
    }

    public function getRelationQuery(EntryContract $parent)
    {
        $parentResource = ResourceFactory::make($parent);

        $config = $this->field->getConfig();

        return new EloquentHasMany(
            $this->getRelated()->newQuery(),
            $parent,
            $config['foreign_key'] ?? $parent->getForeignKey(),
            $parentResource->config()->getKeyName(),
        );
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