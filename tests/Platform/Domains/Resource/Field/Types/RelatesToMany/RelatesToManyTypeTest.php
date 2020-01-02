<?php

namespace Tests\Platform\Domains\Resource\Field\Types\RelatesToMany;

use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesRelationQuery;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Blueprint as RelatesToManyTypeBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\RelatesToManyType;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Blueprint as RelatesToOne;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class RelatesToManyTypeTest extends ResourceTestCase
{
    function test__blueprint_one_to_many()
    {
        $blueprint = Builder::blueprint('tst.students', function (Blueprint $resource) {
            $resource->relatesToMany('tst.payments', 'payments');
        });

        /** @var \SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Blueprint $payments */
        $payments = $blueprint->getField('payments');

        $this->assertInstanceOf(RelatesToManyTypeBlueprint::class, $payments);
        $this->assertInstanceOf(RelatesToManyType::class, $payments->getField()->type());

        $this->assertEquals('tst.payments', $payments->getRelated());
        $this->assertEquals('student_id', $payments->getForeignKey());
    }

    function test__blueprint_many_to_many()
    {
        $blueprint = Builder::blueprint('tst.students', function (Blueprint $resource) {
            $resource->relatesToMany('tst.courses', 'courses')->through('tst.students_courses');
        });

        /** @var \SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Blueprint $courses */
        $courses = $blueprint->getField('courses');

        $this->assertEquals('tst.courses', $courses->getRelated());

        $pivot = $courses->getPivot();
        $this->assertEquals('tst.students_courses', $pivot->getIdentifier());
        $this->assertEquals('students_courses', $pivot->getHandle());

        $this->assertNotNull($student = $pivot->getField('student'));
        $this->assertInstanceOf(RelatesToOne::class, $student);
        $this->assertEquals('tst.students', $student->getRelated());
        $this->assertEquals('student_id', $student->getForeignKey());

        $this->assertNotNull($course = $pivot->getField('course'));
        $this->assertInstanceOf(RelatesToOne::class, $course);
        $this->assertEquals('tst.courses', $course->getRelated());
        $this->assertEquals('course_id', $course->getForeignKey());
    }

    function test__builder_one_to_many()
    {
        $students = Builder::create('tst.students', function (Blueprint $resource) {
            $resource->relatesToMany('tst.payments', 'payments')
                     ->foreignKey('fk_student_id');
        });

        $paymentsField = $students->getField('payments');
        $this->assertEquals('relates_to_many', $paymentsField->getType());

        $this->assertEquals([
            'related'     => 'tst.payments',
            'foreign_key' => 'fk_student_id',
        ], $paymentsField->getConfig());
    }

    function test__builder_many_to_many()
    {
        $students = Builder::create('tst.students', function (Blueprint $resource) {
            $resource->relatesToMany('tst.courses', 'courses')->through('tst.students_courses');
        });

        $courses = $students->getField('courses');
        $this->assertEquals('relates_to_many', $courses->getType());
        $this->assertEquals([
            'related' => 'tst.courses',
            'pivot'   => 'tst.students_courses',
        ], $courses->getConfig());

        $pivot = ResourceFactory::make('tst.students_courses');
        $this->assertTrue($pivot->isPivot());

        $this->assertNotNull($pivot->getField('student'));
        $this->assertNotNull($pivot->getField('course'));
    }

    function test__instance()
    {
        $fieldType = RelatesToManyType::resolve();
        $this->assertInstanceOf(ProvidesRelationQuery::class, $fieldType);
    }

    function test__query_one_to_many()
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

    function test__query_many_to_many()
    {
        Builder::create('tst.courses', function (Blueprint $resource) { });

        $students = Builder::create('tst.students', function (Blueprint $resource) {
            $resource->relatesToMany('tst.courses', 'courses')->through('tst.students_courses');
        });

        $student = $students->fake();

        /** @var  EloquentBelongsToMany $query */
        $query = $students->getField('courses')->type()->getRelationQuery($student);
        $this->assertInstanceOf(EloquentBelongsToMany::class, $query);

        $this->assertEquals('student_id', $query->getForeignPivotKeyName());
        $this->assertEquals('course_id', $query->getRelatedPivotKeyName());
        $this->assertEquals('students_courses', $query->getTable());
//        $this->assertEquals($student->getId(), $query->getParentKey());
//        $this->assertEquals('tst.payments', $query->getQuery()->getModel()->getResourceIdentifier());
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