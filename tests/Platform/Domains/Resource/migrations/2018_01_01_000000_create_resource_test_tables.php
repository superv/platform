<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Schema;
use Tests\Platform\Domains\Resource\Fixtures\TestUser;

class CreateResourceTestTables extends Migration
{
    public function up()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->rules(['min:6', 'max:32']);
            $table->string('email')->rules('email|unique');
            $table->timestamps();

            $resource->model(TestUser::class);
        });
    }

    public function down()
    {
        $this->schema()->drop('test_users');
    }
}
