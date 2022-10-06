<?php

namespace Tests\Platform\Domains\Resource\Field\Types\RelatesToOne;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesFieldComponent;
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
        $blueprint = Builder::blueprint('sv.testing.students', function (Blueprint $resource) {
            $resource->relatesToOne('sv.testing.addresss', 'address');
        });

        /** @var \SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Blueprint $addressField */
        $addressField = $blueprint->getField('address');
        $this->assertNotNull($addressField);
        $this->assertInstanceOf(RelatesToOne::class, $addressField);
        $this->assertEquals('sv.testing.addresss', $addressField->getRelated());
        $this->assertEquals('address_id', $addressField->getForeignKey());
    }

    function test__builder()
    {
        $students = Builder::create('sv.testing.students', function (Blueprint $resource) {
            $resource->relatesToOne('sv.testing.addresses', 'address');
        });

        $addressField = $students->getField('address');
        $this->assertNotNull($addressField);
        $this->assertEquals('relates_to_one', $addressField->getType());

        $this->assertEquals([
            'related'     => 'sv.testing.addresses',
            'foreign_key' => 'address_id',
        ], $addressField->getConfig());

        $this->assertColumnExists('students', 'address_id');
    }

    function test__instance()
    {
        $fieldType = RelatesToOneType::resolve();
        $this->assertInstanceOf(HandlesRpc::class, $fieldType);
        $this->assertInstanceOf(ProvidesFieldComponent::class, $fieldType);
        $this->assertInstanceOf(ProvidesRelationQuery::class, $fieldType);

        $this->assertEquals('sv_relates_to_one_field', $fieldType->getComponentName());
    }

    function test__query()
    {
        Builder::create('sv.testing.addresses', function (Blueprint $resource) {
            $resource->id('pk_address_id');
        });

        $students = Builder::create('sv.testing.students', function (Blueprint $resource) {
            $resource->id('student_id');
            $resource->relatesToOne('sv.testing.addresses', 'address')
                     ->foreignKey('student_address_id');
        });

        $studentEntry = $students->fake(['student_address_id' => 3]);

        /** @var BelongsTo $query */
        $query = $students->getField('address')->type()->getRelationQuery($studentEntry);
        $this->assertInstanceOf(BelongsTo::class, $query);

        $this->assertEquals('address', $query->getRelationName());
        $this->assertEquals('student_address_id', $query->getForeignKeyName());
        $this->assertEquals('pk_address_id', $query->getOwnerKeyName());
        $this->assertEquals('sv.testing.addresses', $query->getQuery()->getModel()->getResourceIdentifier());
    }

    function test__lookup_options()
    {
        $expectedOptions = ['abc' => 'ABC', 'def' => 'DEF'];
        $resource = ResourceFactory::make('sv.platform.resources');

        $this->bindMock(ResourceFactory::class)
             ->expects('withIdentifier')->with('sv.platform.resources')->andReturn($resource);

        $lookupOptionsMock = $this->bindMock(MakeLookupOptions::class);
        $lookupOptionsMock->expects('setResource')->with(\Mockery::on(function ($arg) {
            return $arg->getIdentifier() === 'sv.platform.resources';
        }));
        $lookupOptionsMock->shouldNotReceive('setQueryParams');
        $lookupOptionsMock->expects('make')->andReturn($expectedOptions);

        $fieldType = $this->makeFieldType(['related' => 'sv.platform.resources']);

        $this->assertEquals($expectedOptions, $fieldType->getRpcResult(['method' => 'options']));
    }

    function test__returns_related_entry()
    {
        $addressEntry = Builder::create('sv.testing.addresses', function (Blueprint $resource) { })->create()->fresh();

        $students = Builder::create('sv.testing.students', function (Blueprint $resource) {
            $resource->id('student_id');
            $resource->relatesToOne('sv.testing.addresses', 'address');
        });

        $studentEntry = $students->create(['address_id' => $addressEntry->getId()]);

        /** @var RelatesToOneType $fieldType */
        $fieldType = $students->getField('address')->type();

        /** @var Resource $related */ // stupid PHPSTORM
        $related = $fieldType->getRelated();
        $this->assertInstanceOf(Resource::class, $related);
        $this->assertEquals('sv.testing.addresses', $related->getIdentifier());

        $this->assertEquals($addressEntry, $fieldType->getRelatedEntry($studentEntry));
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