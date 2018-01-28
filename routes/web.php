<?php

use App\Nucleo\Blueprint;
use App\Nucleo\Structable;
use Illuminate\Database\Eloquent\Model;


class Task extends Model
{
    use Structable;

    protected $guarded = [];

    public $timestamps = false;
}


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

            $task = Task::create(['title' => 'Important Task']);
            $task->setAttribute('title', 'Second Title')->save();
            $task->setAttribute('title', 'Third Title')->save();

            return $task->struct()->members;
        },
    ],
];
