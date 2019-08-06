<?php

namespace Tests\Platform\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class RelationFieldTest extends ResourceTestCase
{
    function test__s()
    {
    }

    function test__field_config()
    {
        $field = ResourceFactory::make('students')->getField('address');

        dd($field->getConfig());
        $this->assertNotNull($field);
        $this->assertEquals('addresses', $field->getConfigValue('related'));
        $this->assertEquals('address_id', $field->getConfigValue('local_key'));
        $this->assertEquals('one_to_one', $field->getConfigValue('relation_type'));
    }

    function test__creates_table_column()
    {
        $this->assertColumnExists('students', 'address_id');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->create('addresses',
            function (Blueprint $table, ResourceConfig $config) {
                $config->resourceKey('address');
                $table->increments('id');
            });

        $this->create('students', function (Blueprint $table) {
            $table->increments('id');
            $table->string('student_name');

            $table->relatedToOneOf('addresses', 'address')
                  ->localKey('address_id');
        });

//        $this->create('courses', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('course_name');
//        });
//
//        $this->create('teachers', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('teacher_name');
//
//            $table->relation('courses', RelationType::on())
//                  ->related('addresses')
//                  ->localKey('address_id');
//        });

    }
}
