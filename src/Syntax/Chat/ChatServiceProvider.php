<?php namespace Syntax\Chat;

use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('syntax/chat');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->shareWithApp();
		$this->loadConfig();
		$this->registerViews();
		$this->registerAliases();
	}

	/**
	 * Share the package with application
	 *
	 * @return void
	 */
	protected function shareWithApp()
	{
		$this->app['chat'] = $this->app->share(function($app)
		{
			return true;
		});
	}

	/**
	 * Load the config for the package
	 *
	 * @return void
	 */
	protected function loadConfig()
	{
		$this->app['config']->package('syntax/chat', __DIR__.'/../../../config');
	}

	/**
	 * Register views
	 *
	 * @return void
	 */
	protected function registerViews()
	{
		$this->app['view']->addNamespace('chat', __DIR__.'/../../../views');
	}

	/**
	 * Register aliases
	 *
	 * @return void
	 */
	protected function registerAliases()
	{
		$aliases = [
			'Chat'                        => 'Syntax\Core\Chat', 
			'Chat_Room'                   => 'Syntax\Core\Chat_Room',
		];

		$appAliases = \Config::get('core::nonCoreAliases');

		foreach ($aliases as $alias => $class) {
			if (!is_null($appAliases)) {
				if (!in_array($alias, $appAliases)) {
					\Illuminate\Foundation\AliasLoader::getInstance()->alias($alias, $class);
				}
			} else {
				\Illuminate\Foundation\AliasLoader::getInstance()->alias($alias, $class);
			}
		}
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}