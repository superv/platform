<?php

namespace Tests\Platform\Domains\Resource\Field\Types\RelatesToMany;

use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesRelationQuery;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Blueprint as RelatesToManyTypeBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\RelatesToManyType;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class RelatesToManyTypeTest extends ResourceTestCase
{
    function test__blueprint()
    {
        $blueprint = Builder::blueprint('tst.students', function (Blueprint $resource) {
            $resource->relatesToMany('tst.payments', 'payments');
        });

        /** @var \SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Blueprint $paymentsField */
        $paymentsField = $blueprint->getField('payments');
        $this->assertNotNull($paymentsField);
        $this->assertInstanceOf(RelatesToManyTypeBlueprint::class, $paymentsField);
        $this->assertInstanceOf(RelatesToManyType::class, $paymentsField->getField()->type());
        $this->assertEquals('tst.payments', $paymentsField->getRelated());

        // auto-set from resource key
        $this->assertEquals('student_id', $paymentsField->getForeignKey());
    }

    function test__builder()
    {
        $students = Builder::create('tst.students', function (Blueprint $resource) {
            $resource->relatesToMany('tst.payments', 'payments')
                     ->foreignKey('fk_student_id');
        });

        $paymentsField = $students->getField('payments');
        $this->assertNotNull($paymentsField);
        $this->assertEquals('relates_to_many', $paymentsField->getType());

        $this->assertEquals([
            'related'     => 'tst.payments',
            'foreign_key' => 'fk_student_id',
        ], $paymentsField->getConfig());
    }

    function test__instance()
    {
        $fieldType = RelatesToManyType::resolve();
        $this->assertInstanceOf(HandlesRpc::class, $fieldType);
        $this->assertInstanceOf(ProvidesRelationQuery::class, $fieldType);
    }

    function test__query()
    {
        Builder::create('tst.payments', function (Blueprint $resource) {
            $resource->relatesToOne('tst.students', 'student')->foreignKey('fk_student_id');
        });

        $students = Builder::create('tst.students', function (Blueprint $resource) {
            $resource->id('student_id');
            $resource->relatesToMany('tst.payments', 'payments')
                     ->foreignKey('fk_student_id');
        });

        $student = $students->fake();

        /** @var EloquentHasMany $query */
        $query = $students->getField('payments')->type()->getRelationQuery($student);
        $this->assertInstanceOf(EloquentHasMany::class, $query);

        $this->assertEquals('fk_student_id', $query->getForeignKeyName());
        $this->assertEquals('student_id', $query->getLocalKeyName());
        $this->assertEquals($student->getId(), $query->getParentKey());
        $this->assertEquals('tst.payments', $query->getQuery()->getModel()->getResourceIdentifier());
    }

    function test__returns_related_entries()
    {
        $payments = Builder::create('tst.payments', function (Blueprint $resource) {
            $resource->relatesToOne('tst.students', 'student')->foreignKey('fk_student_id');
        });

        $students = Builder::create('tst.students', function (Blueprint $resource) {
            $resource->id('student_id');
            $resource->relatesToMany('tst.payments', 'payments')
                     ->foreignKey('fk_student_id');
        });

        $student = $students->create([]);

        $payments->fake(['fk_student_id' => $student->getId()], 3);
        $payments->create(['fk_student_id' => $student->getId() + 1]);

        $entries = $student->payments()->get();

        $this->assertEquals(3, $entries->count());
    }
}