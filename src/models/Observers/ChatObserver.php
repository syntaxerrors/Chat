<?php namespace Syntax\Core;

class ChatObserver {

	public function created($model)
	{
		$model->sendToNode($model);
	}
}