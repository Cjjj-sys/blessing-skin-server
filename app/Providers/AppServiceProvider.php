<?php

namespace App\Providers;

use View;
use Event;
use Parsedown;
use App\Events;
use ReflectionException;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\UserRepository;
use App\Services\Repositories\OptionRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Support *.tpl extension name
        View::addExtension('tpl', 'blade');

        // Control the URL generated by url() function
        $this->configureUrlGenerator();

        // Expose some app information to front-end
        Event::listen(Events\RenderingHeader::class, function ($event) {
            $blessing = array_merge(array_except(config('app'), ['key', 'providers', 'aliases', 'cipher', 'log', 'url']), [
                'base_url' => url('/'),
                'site_name' => option_localized('site_name')
            ]);

            $event->addContent('<script>var blessing = '.json_encode($blessing).';</script>');
        });

        try {
            $this->app->make('cipher');
        } catch (ReflectionException $e) {
            throw new PrettyPageException(trans('errors.cipher.unsupported', ['cipher' => config('secure.cipher')]));
        }
    }

    /**
     * Configure the \Illuminate\Routing\UrlGenerator.
     *
     * @return void
     */
    protected function configureUrlGenerator()
    {
        if (! option('auto_detect_asset_url')) {
            $rootUrl = option('site_url');

            // Replace HTTP_HOST with site_url set in options,
            // to prevent CDN source problems.
            if ($this->app['url']->isValidUrl($rootUrl)) {
                $this->app['url']->forceRootUrl($rootUrl);
            }
        }

        if (option('force_ssl') || is_request_secure()) {
            $this->app['url']->forceSchema('https');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cipher', 'App\Services\Cipher\\'.config('secure.cipher'));
        $this->app->singleton('parsedown', Parsedown::class);
        $this->app->singleton('users', UserRepository::class);
        $this->app->singleton('options', OptionRepository::class);
    }
}
