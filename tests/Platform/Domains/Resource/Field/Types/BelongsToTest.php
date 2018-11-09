<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\BelongsTo;
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
        $adminsGroup = $groups->create(['id' => 110, 'title' => 'Admins']);

        $this->assertColumnExists('t_users', 'group_id');

        $field = $users->freshWithFake(['group_id' => 100])->build()->getFieldType('group');
        $field->build();

        $this->assertInstanceOf(BelongsTo::class, $field);
        $this->assertEquals('belongs_to', $field->getType());
        $this->assertEquals(100, $field->getValue());

        $this->assertEquals('t_groups', $field->getConfigValue('related_resource'));
        $this->assertEquals('group_id', $field->getConfigValue('foreign_key'));

        $this->assertEquals([
            ['value' => 100, 'text' => 'Users'],
            ['value' => 110, 'text' => 'Admins'],
        ], $field->getConfigValue('options'));

        $field->setValue($adminsGroup);
        $this->assertEquals(110, $field->getValue());
    }
}