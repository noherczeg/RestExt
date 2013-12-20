<?php

namespace Noherczeg\RestExt;

use Illuminate\Support\ServiceProvider;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Noherczeg\RestExt\Http\QueryStringOperations;

class RestExtServiceProvider extends ServiceProvider {

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
		$this->package('noherczeg/restext');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app['linker'] = $this->app->share(function($app)
        {
            return new RestLinker(new QueryStringOperations());
        });

        $this->app['restResponse'] = $this->app->share(function($app)
        {
            return new RestResponse();
        });

        $this->app['restExt'] = $this->app->share(function($app)
        {
            return new RestExt($app['linker'], $app['config']);
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
        return array('linker', 'restExt', 'restResponse');
	}

}