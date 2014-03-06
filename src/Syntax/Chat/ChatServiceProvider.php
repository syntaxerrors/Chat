<?php namespace Syntax\Core;

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
		// $this->registerAlias();
		$this->loadConfig();
		$this->registerViews();
		// $this->activateProfiler();
		// $this->registerProfilerRouting();
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
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}