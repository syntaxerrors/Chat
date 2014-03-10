<?php

class Core_Chat_AdminController extends BaseController {

	public function getIndex()
	{
		LeftTab::
			addPanel()
				->setTitle('Chat Server')
				->setBasePath('chat/admin')
				->addTab('Controls', 'controls')
				->addTab('Config', 'config')
				->buildPanel()
		->make();
	}

	public function getConfig()
	{
		$chatConfig = json_decode(File::get(app_path('config/packages/syntax/chat/chatConfig.json')));

		$this->setViewData('chatConfig', $chatConfig);
	}

	public function postConfig()
	{
		$this->skipView();

		$input = e_array(Input::all());

		if ($input != null) {
			unset($input['_token']);

			$input['debug']             = $input['debug'] == 1 ? true : false;
			$input['connectionMessage'] = $input['connectionMessage'] == 1 ? true : false;

			$config = json_encode($input, JSON_NUMERIC_CHECK);

			File::put(app_path('config/packages/syntax/chat/chatConfig.json'), $config);

			// Restart the server to have it take effect
			$this->getRestartServer();
		}

		return $this->redirect('/chat/admin#config', 'Chat config updated.');
	}

	public function getControls()
	{
		$rootDirectory = Config::get('remote.connections.default.root');

		// Get the status of the server
		$chatPID = trim(File::get($rootDirectory .'/vendor/syntax/chat/src/node/ChatPID'));

		switch ($chatPID) {
			case 'STOPPED':
				$status = 'Halted';
			break;
			case "":
				$status = 'Restarting...';
			break;
			case is_numeric($chatPID):
				$status = 'Running';
			break;
			default:
				$running = null;

				$commands = [
					'cd '. $rootDirectory .'/vendor/syntax/chat/src/node',
					'ps -p`cat ChatPID` -o "%p %a" --no-header'
				];

				SSH::into('default')->run($commands, function($line) use (&$running) {
					$running = $line.PHP_EOL;
				});

				$status = trim(str_replace('stdin: is not a tty', '', $running));
			break;
		}

		// Get the configuration
		$chatConfig = json_decode(File::get(app_path('config/packages/syntax/chat/chatConfig.json')));

		$this->setViewData('status', $status);
		$this->setViewData('chatConfig', $chatConfig);
	}

	public function getStartServer()
	{
		$rootDirectory = Config::get('remote.connections.default.root');

		$commands = [
			'cd '. $rootDirectory .'/vendor/syntax/chat/src/node',
			'bash 1chat_master &'
		];

		SSH::into('default')->run($commands);

		return $this->redirect('back', 'Chat server started.');
	}

	public function getStopServer()
	{
		$rootDirectory = Config::get('remote.connections.default.root');

		$commands = [
			'cd '. $rootDirectory .'/vendor/syntax/chat/src/node',
			'echo STOP > ChatPID'
		];

		SSH::into('default')->run($commands);

		return $this->redirect('back', 'Chat server halted.');
	}

	public function getRestartServer()
	{
		$rootDirectory = Config::get('remote.connections.default.root');

		$commands = [
			'cd '. $rootDirectory .'/vendor/syntax/chat/src/node',
			'echo "" > ChatPID'
		];

		SSH::into('default')->run($commands);

		return $this->redirect('back', 'Chat server restarted.');
	}
}