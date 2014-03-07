<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChatRoomsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('chat_rooms', function(Blueprint $table) {
            $table->string('uniqueId', 10);
            $table->primary('uniqueId');
            $table->string('user_id', 10)->index();
            $table->string('game_id', 10)->index()->nullable();
            $table->text('name');
            $table->boolean('activeFlag')->default(1);
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
        Schema::drop('chat_rooms');
	}

}