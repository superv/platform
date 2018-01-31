<?php

namespace Tests\SuperV\Platform\Domains\Nucleo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use SuperV\Platform\Domains\Nucleo\Blueprint;
use Tests\SuperV\Platform\BaseTestCase;

class TrackableTest extends BaseTestCase
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

    /**
     * @test
     */
    function can_track_value_history_of_a_struct_member()
    {
        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->trackable();
        });

        $task = Task::create(['title' => 'First Title']);
        $task->setAttribute('title', 'Second Title')->save();
        $task->setAttribute('title', 'Third Title')->save();

        $this->assertEquals([
            'Third Title',
            'Second Title',
            'First Title',
        ], $task->struct()->member('title')->values()->pluck('value')->toArray());
    }

    /**
     * @test
     */
    function can_track_user_who_created_the_member_values()
    {
        $userA = 1;
        $userB = 2;

        $this->builder()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->trackable();
        });

        Auth::shouldReceive('id')->once()->andReturn($userA);
        $task = Task::create(['title' => 'First Title']);

        Auth::shouldReceive('id')->once()->andReturn($userB);
        $task->setAttribute('title', 'Second Title')->save();

        Auth::shouldReceive('id')->once()->andReturn($userA);
        $task->setAttribute('title', 'Third Title')->save();

        $this->assertEquals([
            'Third Title'  => $userA,
            'Second Title' => $userB,
            'First Title'  => $userA,
        ], $task->struct()->member('title')->values()->pluck('created_by', 'value')->toArray());
    }
}