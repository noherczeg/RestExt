<?php

use Illuminate\Database\Migrations\Migration;

class CreateCacheTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('log', function($t)
        {
            $t->increments('id');
            $t->string('php_sapi_name');
            $t->string('level');
            $t->text('message');
            $t->text('context');
            $t->timestamp('created_at');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('log');
	}

}