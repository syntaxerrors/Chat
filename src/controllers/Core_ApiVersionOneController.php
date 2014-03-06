<?php

class Core_ApiVersionOneController extends BaseController {

	public function getChatRoomLog($chatRoomId, $backLog = 30)
	{
		$this->skipView();

		$chatRoomId = e($chatRoomId);

		$messageOutput = array();

		$chatMessages = Chat::where('chat_room_id', '=', $chatRoomId)
			->orderBy('created_at','desc')
			->take($backLog)
			->get();

		foreach ($chatMessages as $messageObject) {
			$parsedChatMessage = BBCode::parse($messageObject->message);

			$newMessage = array();
			$newMessage['text'] 		= "<small class='muted'>({$messageObject->created_at})</small> ".HTML::link('/profile/'. $messageObject->user->uniqueId, $messageObject->user->username, array('target' => '_blank')) .": {$parsedChatMessage} <br />";
			$messageOutput[] = $newMessage;
		}

		$messageOutput = array_reverse($messageOutput);

		return json_encode($messageOutput);
	}
}