<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\BelongsTo;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BelongsToTest extends ResourceTestCase
{
    /** @test */
    function type_belongs_to()
    {
        $groups = $this->create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->entryLabel();
        });

        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->entryLabel();
            $table->belongsTo('t_groups', 'group');
        });

        $groups->create(['id' => 100, 'title' => 'Users']);
        $groups->create(['id' => 110, 'title' => 'Admins']);

        $this->assertColumnExists('t_users', 'group_id');
        $fieldType = $users->fake(['group_id' => 100])->getFieldType('group');

        $this->assertInstanceOf(BelongsTo::class, $fieldType);
        $this->assertEquals('belongs_to', $fieldType->getType());
//        $this->assertEquals(100, $fieldType->getValue());

        $this->assertEquals('t_groups', $fieldType->getConfigValue('related_resource'));
        $this->assertEquals('group_id', $fieldType->getConfigValue('foreign_key'));

        $fieldType->build();
        $this->assertEquals([
            ['value' => 100, 'text' => 'Users'],
            ['value' => 110, 'text' => 'Admins'],
        ], $fieldType->getConfigValue('options'));

//        $field->setValue($adminsGroup);
//        $this->assertEquals(110, $field->getValue());
    }
}