<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Packs\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    protected $scope = 'platform.nucleo';

    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('struct_id');
            $table->unsignedInteger('field_id');
            $table->unsignedInteger('value_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('members');
    }
}
