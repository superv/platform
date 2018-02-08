<?php

use SuperV\Platform\Packs\Database\Migrations\Migration;
use SuperV\Platform\Packs\Nucleo\Blueprint;

class CreateWebUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       $this->builder()->create('users_web', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       $this->builder()->dropIfExists('users_web');
    }

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
}
