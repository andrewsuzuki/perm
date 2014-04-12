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
	 * Bootstrap the application events.
	 * 
	 * @return void
	 */
	public function boot()
	{
		$this->package('andrewsuzuki/perm', null, __DIR__.'/../..');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['config']->package('andrewsuzuki/perm', __DIR__.'/../../config');

		$this->app->bind('Andrewsuzuki\Perm\Perm', function($app) {
			return new Perm($app['files'], $app['config']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('Andrewsuzuki\Perm\Perm');
	}
}
