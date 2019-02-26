<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class AlterUsersTable extends Migration
{
    public function up()
    {
//        Schema::table('users', function (Blueprint $table) {
//            $table->unsignedInteger('account_id')->nullable();
//            $table->string('name')->change()->nullable();
//        });
    }

    public function down()
    {
//        Schema::table('users', function (Blueprint $table) {
//            $table->dropColumn('account_id');
//        });
    }
}
