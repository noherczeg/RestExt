<?php

namespace Noherczeg\RestExt;

use Illuminate\Support\ServiceProvider;
use JMS\Serializer\SerializerBuilder;

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
        $this->app['restlinker'] = $this->app->share(function($app)
        {
            return new RestLinker();
        });

        $this->app['restresponse'] = $this->app->share(function($app)
        {
            return new RestResponse(SerializerBuilder::create()->build(), $app['config']);
        });

        $this->app['restext'] = $this->app->share(function($app)
        {
            return new RestExt($app['restlinker']);
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
        return array('restlinker', 'restext', 'restresponse');
	}

}