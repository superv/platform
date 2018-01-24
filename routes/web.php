<?php

use App\Nucleo\Blueprint;
use App\Nucleo\IsStruct;
use Illuminate\Database\Eloquent\Model;

return [
    'devel/nucleo' => [
        'uses' => function () {
            $builder = \DB::getSchemaBuilder();
            $builder->blueprintResolver(function ($table, $callback) {
                return new Blueprint($table, $callback);
            });


            App\Nucleo\Struct::all()->map->delete();
            App\Nucleo\Prototype::all()->map->delete();
            $builder->dropIfExists('tasks');

            $builder->create('tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
            });

            class Task extends Model {
                    use IsStruct;

                    protected $guarded = [];

                    public $timestamps = false;
                }



            $task = Task::create(['title' => 'Important Task']);

            return $task->struct()->members;

        },
    ],
];