<?php

namespace Tests\Platform\Domains\Resource\Field\Types\Relation;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\Relation\RelationFieldConfig;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class RelationFieldTest extends ResourceTestCase
{
    function test__config_one_to_one()
    {
        $config = $this->getFieldConfig('students', 'address');
        $this->assertEquals('students', $config->getSelf());
        $this->assertEquals('testing.addresses', $config->getRelated());
        $this->assertEquals('student_id', $config->getForeignKey());
        $this->assertTrue($config->getRelationType()->isOneToOne());
        $this->assertTrue($config->isRequired());

        $config = $this->getFieldConfig('addresses', 'student');
        $this->assertEquals('testing.students', $config->getRelated());
        $this->assertEquals('student_id', $config->getLocalKey());
        $this->assertTrue($config->getRelationType()->isOneToOne());
        $this->assertFalse($config->isRequired());

        $this->assertColumnExists('addresses', 'student_id');
        $this->assertColumnNotExists('students', 'address_id');
    }

    function test__config_one_to_many()
    {
        $config = $this->getFieldConfig('teachers', 'courses');
        $this->assertEquals('testing.courses', $config->getRelated());
        $this->assertEquals('teacher_id', $config->getForeignKey());
        $this->assertTrue($config->getRelationType()->isOneToMany());
    }

    function test__config_many_to_many()
    {
        $config = $this->getFieldConfig('students', 'courses');
        $this->assertEquals('testing.courses', $config->getRelated());
        $this->assertEquals('students_courses', $config->getPivotTable());
        $this->assertEquals('student_id', $config->getPivotForeignKey());
        $this->assertEquals('course_id', $config->getPivotRelatedKey());
        $this->assertTrue($config->getRelationType()->isManyToMany());

        $this->assertTableExists('students_courses');
    }

    function test__create()
    {
        $student = $this->makeStudent();
        $address = $student->address()->create(['id' => 34, 'title' => 'Home']);

        $this->assertEquals($student->getId(), $address->student_id);
        $this->assertEquals('Home', $student->fresh()->getAddress()->title);
        $this->assertEquals('Super Student', $address->fresh()->getStudent()->name);
    }

    function test__associate()
    {
        $student = $this->makeStudent();
        $address = sv_resource('testing.addresses')->create(['id' => 34, 'title' => 'Home']);

        $address->student()->associate($student);
        $this->assertEquals($student->getId(), $address->student_id);
    }

    function test__one_to_many()
    {
        $teacher = sv_resource('testing.teachers')->create(['name' => 'Some Guy']);
        $course = $teacher->courses()->create([
            'title' => 'Course A',
        ]);

        $this->assertEquals(1, $teacher->fresh()->courses()->count());
        $this->assertEquals($teacher->getId(), $course->teacher_id);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');

            $table->relatedToOne('testing.students', 'student')
                  ->withLocalKey('student_id')
                  ->required(false);
        });

        $this->create('students', function (Blueprint $table, Config $config) {
            $config->nav('acp.supreme');
            $table->increments('id');
            $table->string('name');

            $table->relatedToOne('testing.addresses')
                  ->withForeignKey('student_id');

            $table->relatedManyToMany('testing.courses')
                  ->withPivotTable('students_courses');
        });

        $this->create('courses', function (Blueprint $table, Config $config) {
            $config->nav('acp.supreme');
            $table->increments('id');
            $table->string('title');

            $table->relatedToOne('testing.teachers')
                  ->withLocalKey('teacher_id');
        });

        $this->create('teachers', function (Blueprint $table, Config $config) {
            $config->nav('acp.supreme');
            $table->increments('id');
            $table->string('name');

            $table->relatedToMany('testing.courses')
                  ->withForeignKey('teacher_id');
        });
    }

    protected function makeStudent(): \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
    {
        $student = sv_resource('testing.students')->create(['id' => 12, 'name' => 'Super Student']);

        return $student;
    }

    protected function getFieldConfig(string $resource, string $fieldName): RelationFieldConfig
    {
        $field = ResourceFactory::make('testing.'.$resource)->getField($fieldName);

        return $field->getFieldType()->getConfig();
    }
}
