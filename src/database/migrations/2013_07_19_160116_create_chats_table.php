<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChatsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('chats', function(Blueprint $table) {
            $table->increments('id');
            $table->string('chat_room_id', 10)->index();
            $table->string('user_id', 10)->index();
            $table->string('character_id', 10)->index()->nullable();
            $table->text('message');
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
        Schema::drop('chats');
	}

}