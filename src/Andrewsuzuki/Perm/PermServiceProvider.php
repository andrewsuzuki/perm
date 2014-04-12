<?php namespace Andrewsuzuki\Perm;

use Illuminate\Support\ServiceProvider;

class PermServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->package('andrewsuzuki/perm');
	}

	/**
	 * Bootstrap the application events.
	 * 
	 * @return void
	 */
	public function boot()
	{
		$this->app->bind('perm', function($app) {
			return new Perm($app['files']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('perm');
	}
}
