<?php

namespace Tests\Platform\Domains\Resource\Field\Types\RelatesToOne;

use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Blueprint as RelatesToOne;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\RelatesToOneType;
use SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class RelatesToOneTypeTest extends ResourceTestCase
{
    function test__lookup_options()
    {
//        $addresses = Builder::create('testing.addresses', function(Blueprint $resource) {
//            $resource->text('title');
//        });

//       $students = Builder::create('testing.students', function(Blueprint $resource) {
//            $resource->relatesToOne('testing.addresses', 'address')->withLocalKey('address_id');
//        });

//       $addresses->fake([], 3);
//       $students->fake([], 3);
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

        $fieldMock = $this->makeMock(FieldInterface::class);
        $fieldMock->expects('getConfig')->andReturns(['related' => 'platform.addons']);
        $fieldType = RelatesToOneType::resolve()
                                     ->setField($fieldMock);

        $this->assertEquals($expectedOptions, $fieldType->getRpcResult(['method' => 'options']));
    }

    function test__returns_related()
    {
        $fieldMock = $this->makeMock(FieldInterface::class);
        $fieldMock->expects('getConfig')->andReturns(['related' => 'platform.addons']);
        $fieldType = RelatesToOneType::resolve()
                                     ->setField($fieldMock);

        /** @var Resource $related */ // stupid PHPSTORM
        $related = $fieldType->getRelated();
        $this->assertInstanceOf(Resource::class, $related);
        $this->assertEquals('platform.addons', $related->getIdentifier());
    }

    function test__rpc()
    {
        ;
    }

    function test__resolve()
    {
        $fieldType = RelatesToOneType::resolve();
        $this->assertInstanceOf(RelatesToOneType::class, $fieldType);
        $this->assertInstanceOf(HandlesRpc::class, $fieldType);
    }

    function test__blueprint()
    {
        $blueprint = Builder::blueprint('sv.posts', function (Blueprint $resource) {
            $resource->relatesToOne('sv.users', 'user')
                     ->withLocalKey('user_id');

            $resource->relatesToOne('sv.posts_body', 'body')
                     ->withRemoteKey('post_id');
        });

        $userField = $blueprint->getField('user');
        $this->assertNotNull($userField);
        $this->assertInstanceOf(RelatesToOne::class, $userField);
        $this->assertEquals('sv.users', $userField->getRelated());
        $this->assertEquals('user_id', $userField->getLocalKey());

        $bodyField = $blueprint->getField('body');
        $this->assertNotNull($bodyField);
        $this->assertInstanceOf(RelatesToOne::class, $bodyField);
        $this->assertEquals('sv.posts_body', $bodyField->getRelated());
        $this->assertEquals('post_id', $bodyField->getRemoteKey());
    }

    function test__builder()
    {
        Builder::create('sv.posts', function (Blueprint $resource) {
            $resource->relatesToOne('sv.users', 'user')
                     ->withLocalKey('user_id');

            $resource->relatesToOne('sv.posts_body', 'body')
                     ->withRemoteKey('post_id');
        });

        $posts = ResourceFactory::make('sv.posts');

        $userField = $posts->getField('user');
        $this->assertNotNull($userField);
        $this->assertEquals('relates_to_one', $userField->getType());

        $this->assertEquals([
            'related'   => 'sv.users',
            'local_key' => 'user_id',
        ], $userField->getConfig());

        $this->assertEquals([
            'related'    => 'sv.posts_body',
            'remote_key' => 'post_id',
        ], $posts->getField('body')->getConfig());
    }
}