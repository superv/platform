<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;

class CreateAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('sv_accounts', function (Blueprint $table) {
            $table->resourceBlueprint()->resourceKey('account');

            $table->increments('id');
            $table->string('name')->entryLabel();
            $table->createdBy()->updatedBy();
            $table->restorable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_accounts');
    }
}
