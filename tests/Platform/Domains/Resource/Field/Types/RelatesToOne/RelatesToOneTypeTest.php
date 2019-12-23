<?php

namespace Tests\Platform\Domains\Resource\Field\Types\RelatesToOne;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Blueprint as RelatesToOne;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\RelatesToOneType;
use SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class RelatesToOneTypeTest extends ResourceTestCase
{
    function test__new_query()
    {
        $fieldType = $this->makeFieldType([
            'related'   => 'platform.addons',
            'local_key' => 'addon_id',
            'owner_key' => 'owner_id',
        ]);

        $query = $fieldType->newQuery($this->partialMock(ResourceEntry::class));
        $this->assertInstanceOf(BelongsTo::class, $query);

        $this->assertEquals('addon', $query->getRelationName());
        $this->assertEquals('addon_id', $query->getForeignKeyName());
        $this->assertEquals('owner_id', $query->getOwnerKeyName());
        $this->assertEquals('platform.addons', $query->getQuery()->getModel()->getResourceIdentifier());
    }

    function test__lookup_options()
    {
        $expectedOptions = ['abc' => 'ABC', 'def' => 'DEF'];
        $resource = ResourceFactory::make('platform.addons');

        $this->bindMock(ResourceFactory::class)
             ->expects('withIdentifier')->with('platform.addons')->andReturn($resource);

        $lookupOptionsMock = $this->bindMock(MakeLookupOptions::class);
        $lookupOptionsMock->expects('setResource')->with(\Mockery::on(function ($arg) {
            return $arg->getIdentifier() === 'platform.addons';
        }));
        $lookupOptionsMock->shouldNotReceive('setQueryParams');
        $lookupOptionsMock->expects('make')->andReturn($expectedOptions);

        $fieldType = $this->makeFieldType(['related' => 'platform.addons']);

        $this->assertEquals($expectedOptions, $fieldType->getRpcResult(['method' => 'options']));
    }

    function test__returns_related()
    {
        $fieldType = $this->makeFieldType(['related' => 'platform.addons']);

        /** @var Resource $related */ // stupid PHPSTORM
        $related = $fieldType->getRelated();
        $this->assertInstanceOf(Resource::class, $related);
        $this->assertEquals('platform.addons', $related->getIdentifier());
    }

    function test__resolve()
    {
        $fieldType = RelatesToOneType::resolve();
        $this->assertInstanceOf(HandlesRpc::class, $fieldType);
    }

    function test__blueprint()
    {
        $blueprint = Builder::blueprint('sv.posts', function (Blueprint $resource) {
            $resource->relatesToOne('sv.users', 'user')
                     ->ownerKey('post_id')
                     ->withLocalKey('user_id');

            $resource->relatesToOne('sv.posts_body', 'body')
                     ->withRemoteKey('post_id');
        });

        $userField = $blueprint->getField('user');
        $this->assertNotNull($userField);
        $this->assertInstanceOf(RelatesToOne::class, $userField);
        $this->assertEquals('sv.users', $userField->getRelated());
        $this->assertEquals('post_id', $userField->getOwnerKey());
        $this->assertEquals('user_id', $userField->getLocalKey());

        $bodyField = $blueprint->getField('body');
        $this->assertNotNull($bodyField);
        $this->assertInstanceOf(RelatesToOne::class, $bodyField);
        $this->assertEquals('sv.posts_body', $bodyField->getRelated());
        $this->assertEquals('post_id', $bodyField->getRemoteKey());
    }

    function test__builder()
    {
        $posts = Builder::create('sv.posts', function (Blueprint $resource) {
            $resource->relatesToOne('sv.users', 'user')
                     ->ownerKey('post_id')
                     ->withLocalKey('user_id');

            $resource->relatesToOne('sv.posts_body', 'body')
                     ->withRemoteKey('post_id');
        });

        $userField = $posts->getField('user');
        $this->assertNotNull($userField);
        $this->assertEquals('relates_to_one', $userField->getType());

        $this->assertEquals([
            'related'   => 'sv.users',
            'owner_key' => 'post_id',
            'local_key' => 'user_id',
        ], $userField->getConfig());

        $this->assertEquals([
            'related'    => 'sv.posts_body',
            'owner_key'  => 'id',
            'remote_key' => 'post_id',
        ], $posts->getField('body')->getConfig());
    }

    protected function makeFieldType(array $config = []): RelatesToOneType
    {
        $field = FieldFactory::createFromArray([
            'handle' => 'addon',
            'type'   => RelatesToOneType::class,
            'config' => $config,
        ]);

        return $field->getFieldType();
    }
}