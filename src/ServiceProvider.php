<?php namespace Newway\TurboSms;


class ServiceProvider extends \Illuminate\Support\ServiceProvider {

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
            __DIR__.'/../config/config.php' => config_path('turbo_sms.php'),
        ], 'config');
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// init theme with default finder
        $this->app['turbo_sms'] = $this->app->share(function($app) {
            $sms = new TurboSms($app);
            return $sms;
        });


        // merge & publihs config
        $configPath = __DIR__ . '/../config/config.php';
        $this->mergeConfigFrom($configPath, 'turbo_sms');
        $this->publishes([$configPath => config_path('turbo_sms.php')]);

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}
