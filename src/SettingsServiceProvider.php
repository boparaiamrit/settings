<?php namespace Boparaiamrit\Settings;


use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
	
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
		$this->publishes([
			__DIR__ . '/config/settings.php' => config_path('settings.php')
		]);
		
		$this->mergeConfigFrom(
			__DIR__ . '/config/settings.php', 'settings'
		);
	}
	
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		require 'helpers.php';
		
		/** @noinspection PhpUndefinedMethodInspection */
		$this->app['settings'] = $this->app->share(function ($app) {
			$config = $app->config->get('settings');
			
			return new Settings($app['db'], $config);
		});
	}
	
	
	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['settings'];
	}
	
}