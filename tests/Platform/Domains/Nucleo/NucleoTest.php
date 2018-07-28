<?php

namespace Tests\Platform\Domains\Nucleo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use SuperV\Platform\Domains\Nucleo\Blueprint;
use SuperV\Platform\Domains\Nucleo\Member;
use SuperV\Platform\Domains\Nucleo\Prototypeable;
use SuperV\Platform\Domains\Nucleo\Struct;
use SuperV\Platform\Domains\Nucleo\Structable;
use Tests\Platform\TestCase;

class NucleoTest extends TestCase
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
    function create_struct_when_a_model_is_created()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
        });

        $task = Task::create([]);

        $this->assertEquals(1, Struct::count());
        $this->assertEquals($task->id, Struct::first()->related_id);
        $this->assertEquals($task->prototype()->id, Struct::first()->prototype_id);
    }

    /** @test */
    function delete_struct_when_model_is_deleted()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
        });

        $task = Task::create([]);

        $this->assertEquals(1, Struct::count());

        $task->delete();

        $this->assertEquals(0, Struct::count());
    }

    /** @test */
    function delete_members_when_a_struct_is_deleted()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('priority');
        });

        $task = Task::create([
            'title'    => 'My important task',
            'priority' => 'high',
        ]);

        $struct = $task->struct();

        $this->assertEquals(2, Member::where('struct_id', $struct->id)->count());

        $task->delete();

        $this->assertEquals(0, Member::where('struct_id', $struct->id)->count());
    }

    /** @test */
    function create_struct_members_and_save_values_when_a_model_is_created()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('priority');
        });

        $task = Task::create([
            'title'    => 'My important task',
            'priority' => 'high',
        ]);

        $task = $task->fresh();

        $this->assertEquals(2, $task->struct()->members()->count());
        $this->assertEquals('My important task', $task->struct()->member('title')->getValue());
        $this->assertEquals('high', $task->struct()->member('priority')->getValue());
    }

    /** @test */
    function update_members_values_when_the_model_is_updated()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('priority');
        });

        $task = Task::create([
            'title'    => 'My important task',
            'priority' => 'high',
        ]);
        $task->title = 'My ordinary task';
        $task->priority = 'very low';
        $task->save();

        $this->assertEquals('My ordinary task', $task->struct()->member('title')->getValue());
        $this->assertEquals('very low', $task->struct()->member('priority')->getValue());
    }

    /** @test */
    function creates_scattered_members()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('priority')->scatter();
        });

        $task = Task::create([
            'title'    => 'My important task',
            'priority' => 'high',
        ]);

        $task->priority = 'low';
        $task->save();

        $task = $task->fresh();

        $this->assertDatabaseMissing('tasks', ['priority' => 'high']);
        $this->assertArrayHasKey('priority', $task->toArray());
        $this->assertContains('low', $task->toArray());
    }

    /** @test */
    function validate_field_rules_before_saving_the_model()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('priority')->nullable()->rules('required');
        });

        try {
            Task::create([
            ]);

            $this->fail('failed to validate field rules - required');
        } catch (ValidationException $e) {
            $this->assertContains('priority', array_keys($e->errors()));
        }
    }

    /** @test */
    function validate_rules_for_scattered_members_when_creating()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('priority')->nullable()->rules('min:3')->scatter();
        });

        try {
            Task::create([
                'priority' => 'lo',
            ]);

            $this->fail('failed to validate field rules');
        } catch (ValidationException $e) {
            $this->assertContains('priority', array_keys($e->errors()));
        }
    }

    /** @test */
    function validate_rules_for_scattered_members_when_saving()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('priority')->nullable()->rules('min:3')->scatter();
        });

        try {
            $task = Task::create([
                'priority' => 'low',
            ]);

            $task->priority = 'lo';
            $task->save();

            $this->fail('failed to validate field rules');
        } catch (ValidationException $e) {
            $this->assertContains('priority', array_keys($e->errors()));
        }
    }
}

class Task extends Model
{
    use Structable;
    use Prototypeable;

    protected $guarded = [];

    public $timestamps = false;
}