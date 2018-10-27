<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Blueprint;

class CreateTasksTable extends Migration
{
    public function up()
    {
        $this->schema()->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });
    }

    public function down()
    {
        $this->schema()->drop('tasks');
    }
}
