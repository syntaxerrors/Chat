<?php
namespace Syntax\Core;

class Chat_Room extends \BaseModel
{
	/********************************************************************
	 * Declarations
	 *******************************************************************/

	/**
	 * Table declaration
	 *
	 * @var string $table The table this model uses
	 */
	protected $table = 'chat_rooms';
	protected $primaryKey = 'uniqueId';

	/********************************************************************
	 * Aware validation rules
	 *******************************************************************/

    /**
     * Validation rules
     *
     * @static
     * @var array $rules All rules this model must follow
     */
	public static $rules = array(
		'user_id'          => 'required|exists:users,uniqueId',
		'name'             => 'required',
	);

	/********************************************************************
	 * Scopes
	 *******************************************************************/

	/********************************************************************
	 * Relationships
	 *******************************************************************/
	public static $relationsData = array(
		'user'  => array('belongsTo',	'User', 'foreignKey' => 'user_id'),
		'chats' => array('hasMany',		'Chat', 'foreignKey' => 'chat_room_id', 'orderBy' => array('created_at', 'asc')),
	);

	/********************************************************************
	 * Model events
	 *******************************************************************/

	/********************************************************************
	 * Getter and Setter methods
	 *******************************************************************/
	public function getRecentChatsAttribute()
	{
		return $this->chats->where('created_at', '>', date('Y-m-d H:i:s', strtotime('-30 minutes')));
	}

	public function getUsersOnlineAttribute()
	{
		return 0;
		$rawData = substr(Chat::getUserCount($this->id), 4);
		$jsonData = json_decode($rawData);

		return $jsonData->args[0];
	}

	/********************************************************************
	 * Extra Methods
	 *******************************************************************/
}