<?php

namespace App\Providers;

use App\Support\Money;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Blade::directive('money', fn ($expression) => "<?php echo \App\Support\Money::format($expression); ?>");
        Blade::directive('moneyshort', fn ($expression) => "<?php echo \App\Support\Money::short($expression); ?>");
    }
}