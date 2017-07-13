<?php

namespace Temperworks\RussianDollCache;

use Blade;
use Illuminate\Support\ServiceProvider;

class RussianDollCacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../resources/config/russian-doll-cache.php' => config_path('russian-doll-cache.php'),
        ], 'config');

        $directive = config('russian-doll-cache.directive');

        Blade::directive($directive, function ($expression) {
            if (starts_with($expression, '(')) {
                $expression = substr($expression, 1, -1);
            }

            return "<?php echo app()->make('russian-doll-cache')
                ->cache({$expression}); ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../resources/config/russian-doll-cache.php', 'russian-doll-cache');

        $this->app->alias(RussianDollCache::class, 'russian-doll-cache');
    }
}
