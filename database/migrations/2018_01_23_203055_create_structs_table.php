<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Packs\Database\Migrations\Migration;

class CreateStructsTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('structs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('prototype_id');
			$table->unsignedInteger('related_id');
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
		Schema::drop('structs');
	}
}
