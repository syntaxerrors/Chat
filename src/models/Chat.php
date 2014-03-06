<?php
namespace Syntax\Core;
use SocketIOClient;
use BBCode;
use Config;

class Chat extends \BaseModel
{
	/********************************************************************
	 * Declarations
	 *******************************************************************/

	/********************************************************************
	 * Aware validation rules
	 *******************************************************************/
	public static $rules = array(
		'user_id'      => 'required|exists:users,uniqueId',
		'message'      => 'required',
		'chat_room_id' => 'required|exists:chat_rooms,uniqueId',
	);

	/********************************************************************
	 * Relationships
	 *******************************************************************/
	public static $relationsData = array(
		'user' => array('belongsTo', 'User',		'foreignKey' => 'user_id'),
		'room' => array('belongsTo', 'Chat_Room',	'foreignKey' => 'chat_room_id'),
	);

	/********************************************************************
	 * Getter and Setter methods
	 *******************************************************************/

	/********************************************************************
	 * Extra Methods
	 *******************************************************************/

	public function sendToNode ($messageObject) 
	{
		$parsedChatMessage = BBCode::parse($messageObject->message);

		$newMessage['text'] 		= "<small class='muted'>({$messageObject->created_at})</small> ". HTML::link('/profile/'. $messageObject->user->uniqueId, $messageObject->user->username, array('target' => '_blank')) .": {$parsedChatMessage} <br />";
		$newMessage['room'] 		= $messageObject->chat_room_id;
		$newMessage['username'] 	= $messageObject->user->username;
		$newMessage['userId']		= $messageObject->user->uniqueId;

		$node = new SocketIOClient(Config::get('app.url') .':1337', 'socket.io', 1, false, true, true);
		$node->init();
		$node->send(
			SocketIOClient::TYPE_EVENT,
			null,
			null,
			json_encode(array('name' => 'message', 'args' => $newMessage))
			);
		$node->close();
	}

	public static function getUserCount($chatRoomId)
	{
		$node = new SocketIOClient(Config::get('app.url') .':1337', 'socket.io', 1, true, true, true);
		$node->init();
		$node->send(
			SocketIOClient::TYPE_EVENT,
			null,
			null,
			json_encode(array('name' => 'getUserCount', 'args' => $chatRoomId))
			);

		$data = $node->read();

		$node->close();

		return $data;
	}
}