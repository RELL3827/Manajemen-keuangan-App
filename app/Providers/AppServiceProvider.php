<?php

namespace App\Providers;

use App\Support\Money;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production') ||
            config('app.env') === 'production' ||
            (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'vercel.app')) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        Blade::directive('money', fn ($expression) => "<?php echo \App\Support\Money::format($expression); ?>");
        Blade::directive('moneyshort', fn ($expression) => "<?php echo \App\Support\Money::short($expression); ?>");
    }
}