<?php

namespace Tests\SuperV\Platform\Domains\Nucleo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Nucleo\Blueprint;
use SuperV\Platform\Domains\Nucleo\Field;
use SuperV\Platform\Domains\Nucleo\Prototype;
use Tests\SuperV\Platform\BaseTestCase;

class BlueprintTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @return \Illuminate\Database\Schema\MySqlBuilder
     */
    protected function builder()
    {
        $builder = \DB::getSchemaBuilder();
        $builder->blueprintResolver(function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }

    /** @test */
    function create_prototype_when_a_table_is_created()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
        });

        $this->assertNotNull(Prototype::byTable('tasks'));
    }

    /** @test */
    function delete_prototype_when_a_table_is_dropped()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
        });
        $this->assertNotNull(Prototype::byTable('tasks'));

        $this->builder()->drop('tasks');
        $this->assertNull(Prototype::byTable('tasks'));

        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
        });
        $this->assertNotNull(Prototype::byTable('tasks'));

        $this->builder()->dropIfExists('tasks');
        $this->assertNull(Prototype::byTable('tasks'));
    }

    /** @test */
    function create_field_when_a_column_is_added()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->string('title');
        });

        $prototype = Prototype::byTable('tasks');
        $this->assertEquals(1, $prototype->fields()->count());

        $this->builder()->table('tasks', function (Blueprint $table) {
            $table->string('priority')->nullable();
        });

        $this->assertEquals(2, $prototype->fields()->count());
    }

    /** @test */
    function save_rules_for_field()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->string('title')->rules(['required', 'min:6']);
        });

        $prototype = Prototype::byTable('tasks');
        $this->assertEquals(['required', 'min:6'], $prototype->field('title')->rules);
    }

    /** @test */
    function do_not_create_a_field_for_primary_key_column()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
        });

        $prototype = Prototype::byTable('tasks');
        $this->assertEquals(0, $prototype->fields()->count());
    }

    /** @test */
    function delete_field_when_a_column_is_dropped()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        $prototype = Prototype::byTable('tasks');
        $this->assertNotNull($prototype->field('title'));

        $this->builder()->table('tasks', function (Blueprint $table) {
            $table->dropColumn('title');
        });
        $prototype->load('fields');
        $this->assertNull($prototype->field('title'));
    }

    /** @test */
    function delete_fields_when_a_prototype_is_deleted()
    {
        $prototype = Prototype::create(['table' => 'tasks']);
        $prototype->fields()->create(['slug' => 'title', 'type' => 'string']);

        $this->assertEquals(1, Field::where('prototype_id', $prototype->id)->count());

        $prototype->delete();

        $this->assertEquals(0, Field::where('prototype_id', $prototype->id)->count());
    }

    /** @test */
    function not_nullable_columns_are_required_fields()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
        });

        $field = Field::where('slug', 'title')->first();

        $this->assertFalse($field->required);
    }

    /** @test */
    function save_column_default_value()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->string('title')->default('Important Task');
        });

        $field = Field::where('slug', 'title')->first();
        $this->assertEquals('Important Task', $field->default_value);
    }
}