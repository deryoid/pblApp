<?php

namespace App\Providers;

use App\Models\AktivitasCard;
use App\Models\AktivitasList;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use App\Policies\AktivitasCardPolicy;
use App\Policies\AktivitasListPolicy;
use App\Policies\ProjectCardPolicy;
use App\Policies\ProjectListPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Increase memory limit for handling large images and datasets
        $memoryLimit = config('app.memory_limit', '512M');
        ini_set('memory_limit', $memoryLimit);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies here (Laravel 11 style without AuthServiceProvider)
        Gate::policy(ProjectCard::class, ProjectCardPolicy::class);
        // ProjectBoard removed per latest design; no policy binding
        Gate::policy(ProjectList::class, ProjectListPolicy::class);
        Gate::policy(AktivitasCard::class, AktivitasCardPolicy::class);
        Gate::policy(AktivitasList::class, AktivitasListPolicy::class);

        // Force HTTPS in production or when APP_URL is HTTPS
        if (config('app.env') === 'production' || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Set locale waktu ke Indonesia untuk Carbon
        try {
            Carbon::setLocale('id');
        } catch (\Throwable $e) {
            // ignore if locale not available; per-call locale still applied in controller
        }
    }
}
