<?php

class Core_ChatController extends BaseController {

	public $type = 'chat';

	public function getIndex()
	{
		$chatRooms         = Chat_Room::active()->orderByNameAsc()->get();
		$inactiveChatRooms = Chat_Room::inactive()->orderByNameAsc()->get();

		$this->setViewData('chatRooms', $chatRooms);
		$this->setViewData('inactiveChatRooms', $inactiveChatRooms);
	}

	public function getAdd()
	{
		// Check permission to create a chat room
		$this->checkPermission('CHAT_CREATE');
	}

	public function getStart()
	{
		$root = Config::get('remote.connections.default.root');
		SSH::into('default')->run(array(
			'cd '. $root,
			'./chatStart.sh '. $root,
		));

		return $this->redirect('back', 'Chat server started.');
	}

	public function getRestart()
	{
		$root = Config::get('remote.connections.default.root');
		SSH::into('default')->run(array(
			'cd '. $root,
			'./chatStart.sh '. $root,
		));

		return $this->redirect('back', 'Chat server restarted.');
	}

	public function getStop()
	{
		$root = Config::get('remote.connections.default.root');
		SSH::into('default')->run(array(
			'cd '. $root,
			'./chatStop.sh '. $root,
		));

		return $this->redirect('back', 'Chat server stopped.');
	}

	public function postAdd()
	{
		// Handle the form input
		$input = e_array(Input::all());

		if ($input != null) {
			$room                   = new Chat_Room;
			$room->user_id          = $this->activeUser->id;
			$room->name             = $input['name'];
			$room->activeFlag       = (isset($input['activeFlag']) ? 1 : 0);

			$this->checkErrorsSave($room);

			return $this->redirect('chat', $room->name .' has been created.');
		}
	}

	public function getEdit($chatRoomId)
	{
		$chatRoom = Chat_Room::find($chatRoomId);

		$this->setViewData('chatRoom', $chatRoom);
	}

	public function postEdit($chatRoomId)
	{
		// Handle the form input
		$input = e_array(Input::all());

		if ($input != null) {
			$room             = Chat_Room::find($chatRoomId);
			$room->user_id    = $this->activeUser->id;
			$room->name       = $input['name'];
			$room->activeFlag = (isset($input['activeFlag']) ? 1 : 0);

			$this->checkErrorsSave($room);

			return $this->redirect('chat', $room->name .' has been updated.');
		}
	}

	public function getRoom($chatRoomId = null, $message = null)
	{
		if ($chatRoomId == null || Chat_Room::where('uniqueId', $chatRoomId)->first() == null) {
			return Redirect::back()->with('errors', array('The requested chat room does not exist.'));
		}
		// Get the chat room
		$chatRoom = Chat_Room::where('uniqueId', $chatRoomId)->first();
		if ($chatRoom->activeFlag == 0 && $this->activeUser->id != $chatRoom->user_id && !$this->hasPermission('DEVELOPER')) {
			return Redirect::back()->with('errors', array('The requested chat room is not active at this time.'));
		}

		// Get the data
		$chats = Chat::where('chat_room_id', $chatRoomId)->orderBy('created_at', 'desc')->take(30)->get();
		$lastChatTimes = $chatRoom->chats->created_at->toArray();

		$this->setViewData('chatRoom', $chatRoom);
		$this->setViewData('chats', $chats->reverse());
	}

	public function getFullChat($chatRoomId)
	{
		if ($chatRoomId == null || Chat_Room::find($chatRoomId) == null) {
			return $this->redirect('back', 'The requested chat room does not exist.');
		}

		$boards = Forum_Category::where('forum_category_type_id', '!=', Forum_Category::TYPE_SUPPORT)
				->orderByNameAsc()->get()
				->boards->toSelectArray('Select a Board');

		// Get the data
		$chatRoom = Chat_Room::with('chats')->find($chatRoomId);
		$this->setViewData('chatRoom', $chatRoom);
		$this->setViewData('boards', $boards);
	}

	public function postFullChat($chatRoomId)
	{
		$postId  = Input::get('post_id');
		$boardId = Input::get('board_id');
		$title   = Input::get('title');

		if ($postId == '0') $postId = null;
		if ($boardId == '0') $boardId = null;

		// Run through some error checking to make sure everything is in order
		if ($postId != null && !is_null($boardId)) {
			return $this->redirect('back', 'You have set a post AND a board.  Please set one or the other.');
		}
		if (!is_null($boardId) && $title == null) {
			return $this->redirect('back', 'To create a new post you must specify a title.');
		}
		if ($postId != null) {
			$post = Forum_Post::find($postId);

			if ($post == null) {
				return $this->redirect('back', 'No post found with id '. $postId);
			}
		}
		if (!is_null($boardId)) {
			$board = Forum_Board::find($boardId);

			if ($board == null) {
				return $this->redirect('back', 'No board found with id '. $boardId);
			}
		}

		// Get the requested chats
		$chats = Chat::where('chat_room_id', $chatRoomId)
			->orderBy('created_at', 'asc')
			->skip(Input::get('start') - 1)
			->take(Input::get('end'))
			->get();

		if ($chats->count() == 0) {
			return $this->redirect('back', 'Due to your selections, no chats remained to be moved.');
		}

		if ($postId != null) {
			$reply                      = new Forum_Reply;
			$reply->forum_post_id       = $postId;
			$reply->forum_reply_type_id = Forum_Reply::TYPE_STANDARD;
			$reply->user_id             = $this->activeUser->id;
			$reply->morph_id            = null;
			$reply->morph_type          = null;
			$reply->name                = 'Re:'. $post->name;
			$reply->keyName             = Str::studly($reply->name);
			$reply->content             = $this->convertChatsToPost($chats);

			$this->save($reply);

			return $this->redirect('back', 'Chat added as a reply to '. $post->name);
		}

		if (!is_null($boardId)) {
			$post                     = new Forum_Post;
			$post->forum_board_id     = $boardId;
			$post->forum_post_type_id = Forum_Post::TYPE_STANDARD;
			$post->user_id            = $this->activeUser->id;
			$post->morph_id           = null;
			$post->morph_type         = null;
			$post->name               = $title;
			$post->keyName            = Str::studly($title);
			$post->content            = $this->convertChatsToPost($chats);

			$this->save($post);

			$post->modified_at = $post->created_at;
			$this->save($post);

			return $this->redirect('back', 'Chat added as a new post in '. $board->name);
		}
	}

	protected function convertChatsToPost($chats)
	{
		$content = array();

		foreach ($chats as $chat) {
			$newChat = '';

			if (!Input::has('noTimestamps')) {
				$newChat .= '[spanClass=text-muted][small]('. $chat->created_at .')[/small][/spanClass] ';
			}

			$newChat .= '[b][url=/user/view/'. $chat->user_id.']'. $chat->user->username .'[/url][/b]';

			$newChat .= ': '. $chat->message ."\n";
			$content[] = $newChat;
		}

		return implode($content);
	}

	public function postAddmessage()
	{
		$this->skipView();

		// Handle the form input
		$input = Input::all();

		if ($input != null) {
			$message = e($input['message']);
			$message            = preg_replace_callback('/\/roll/', array($this, 'roll'), $message);
			$chat               = new Chat;
			$chat->user_id      = $this->activeUser->id;
			$chat->chat_room_id = $input['chat_room_id'];
			$chat->message      = $message;
			$chat->save();
			// $chat->sendErsatz();

			$errors = $this->checkErrors($chat);

			if ($errors == true) {
				return $chat->getErrors()->toJson();
			}
		}
	}

	public function getUpdate($chatRoomId, $property, $value)
	{
		if(!$this->hasPermission('CHAT_CREATE')) {
			$this->errorRedirect();
		}

		$chatRoom = Chat_Room::find($chatRoomId);
		$chatRoom->{$property} = $value;

		$this->checkErrorsSave($chatRoom);

		return $this->redirect('back', $chatRoom->name .' has been updated.');
	}

	public function getDelete($chatRoomId)
	{
		if(!$this->hasPermission('CHAT_CREATE')) {
			$this->errorRedirect();
		}

		$chatRoom = Chat_Room::find($chatRoomId);
		$chatRoom->delete();

		return $this->redirect('back', $chatRoom->name .' has been deleted.');
	}

	public function getClear($chatRoomId)
	{
		if(!$this->hasPermission('CHAT_CREATE')) {
			$this->errorRedirect();
		}

		$chats = Chat::where('chat_room_id', $chatRoomId)->get();

		if (count($chats) > 0) {
			foreach ($chats as $chat) {
				$chat->delete();
			}
		}

		return $this->redirect('back', 'Chat room cleared.');
	}

	public function getUsercount($chatRoomId)
	{
		$this->skipView = true;

		$rawData = substr(Chat::getUserCount($chatRoomId), 4);
		$jsonData = json_decode($rawData);

		return $jsonData->args[0];
	}
}
