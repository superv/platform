<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migration\Migration;

class PlatformCreatePortsTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ports', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('slug');
			$table->string('name');
			$table->string('hostname');
			$table->string('prefix')->nullable();
			$table->string('theme')->nullable();
			$table->timestamps();

			$table->unique(['slug']);
			$table->unique(['hostname', 'prefix']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ports');
	}
}
