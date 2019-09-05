<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;

class CreateTestingTasksTable extends Migration
{
    public function up()
    {
        $this->schema()->create('testing_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });
    }

    public function down()
    {
        $this->schema()->drop('testing_tasks');
    }
}
