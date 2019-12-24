<?php

namespace Tests\Platform\Domains\Resource\Field\Types\RelatesToOne;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesRelationQuery;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Blueprint as RelatesToOne;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\RelatesToOneType;
use SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class RelatesToOneTypeTest extends ResourceTestCase
{
    function test__blueprint()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
            $resource->relatesToOne('testing.users', 'user')
                     ->ownerKey('post_id')
                     ->withLocalKey('user_id');

            $resource->relatesToOne('testing.posts_body', 'body')
                     ->withRemoteKey('post_id');
        });

        $userField = $blueprint->getField('user');
        $this->assertNotNull($userField);
        $this->assertInstanceOf(RelatesToOne::class, $userField);
        $this->assertEquals('testing.users', $userField->getRelated());
        $this->assertEquals('post_id', $userField->getOwnerKey());
        $this->assertEquals('user_id', $userField->getLocalKey());

        $bodyField = $blueprint->getField('body');
        $this->assertNotNull($bodyField);
        $this->assertInstanceOf(RelatesToOne::class, $bodyField);
        $this->assertEquals('testing.posts_body', $bodyField->getRelated());
        $this->assertEquals('post_id', $bodyField->getRemoteKey());
    }

    function test__builder()
    {
        $posts = Builder::create('testing.posts', function (Blueprint $resource) {
            $resource->relatesToOne('testing.users', 'user')
                     ->ownerKey('post_id')
                     ->withLocalKey('user_id');

            $resource->relatesToOne('testing.posts_body', 'body')
                     ->withRemoteKey('post_id');
        });

        $userField = $posts->getField('user');
        $this->assertNotNull($userField);
        $this->assertEquals('relates_to_one', $userField->getType());

        $this->assertEquals([
            'related'   => 'testing.users',
            'owner_key' => 'post_id',
            'local_key' => 'user_id',
        ], $userField->getConfig());

        $this->assertEquals([
            'related'    => 'testing.posts_body',
            'owner_key'  => 'id',
            'remote_key' => 'post_id',
        ], $posts->getField('body')->getConfig());
    }

    function test__query()
    {
        $fieldType = $this->makeFieldType([
            'related'   => 'platform.resources',
            'local_key' => 'resource_id',
            'owner_key' => 'owner_id',
        ]);

        $query = $fieldType->getRelationQuery($this->partialMock(ResourceEntry::class));
        $this->assertInstanceOf(BelongsTo::class, $query);

        $this->assertEquals('resource', $query->getRelationName());
        $this->assertEquals('resource_id', $query->getForeignKeyName());
        $this->assertEquals('owner_id', $query->getOwnerKeyName());
        $this->assertEquals('platform.resources', $query->getQuery()->getModel()->getResourceIdentifier());
    }

    function test__lookup_options()
    {
        $expectedOptions = ['abc' => 'ABC', 'def' => 'DEF'];
        $resource = ResourceFactory::make('platform.resources');

        $this->bindMock(ResourceFactory::class)
             ->expects('withIdentifier')->with('platform.resources')->andReturn($resource);

        $lookupOptionsMock = $this->bindMock(MakeLookupOptions::class);
        $lookupOptionsMock->expects('setResource')->with(\Mockery::on(function ($arg) {
            return $arg->getIdentifier() === 'platform.resources';
        }));
        $lookupOptionsMock->shouldNotReceive('setQueryParams');
        $lookupOptionsMock->expects('make')->andReturn($expectedOptions);

        $fieldType = $this->makeFieldType(['related' => 'platform.resources']);

        $this->assertEquals($expectedOptions, $fieldType->getRpcResult(['method' => 'options']));
    }

    function test__returns_related()
    {
        $fieldType = $this->makeFieldType(['related' => 'platform.resources']);

        /** @var Resource $related */ // stupid PHPSTORM
        $related = $fieldType->getRelated();
        $this->assertInstanceOf(Resource::class, $related);
        $this->assertEquals('platform.resources', $related->getIdentifier());
    }

    function test__instance()
    {
        $fieldType = RelatesToOneType::resolve();
        $this->assertInstanceOf(HandlesRpc::class, $fieldType);
        $this->assertInstanceOf(ProvidesRelationQuery::class, $fieldType);
    }

    protected function makeFieldType(array $config = []): RelatesToOneType
    {
        $field = $this->makeRelatesToOneField($config);

        return $field->getFieldType();
    }

    /**
     * @param array $config
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface|\SuperV\Platform\Domains\Resource\Form\Contracts\FormFieldInterface
     */
    protected function makeRelatesToOneField(array $config = ['related' => 'platform.resources'])
    {
        $field = FieldFactory::createFromArray([
            'handle' => 'resource',
            'type'   => RelatesToOneType::class,
            'config' => $config,
        ]);

        return $field;
    }
}