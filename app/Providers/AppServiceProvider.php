<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\ProjectCard;
use App\Policies\ProjectCardPolicy;
use App\Models\ProjectList;
use App\Policies\ProjectListPolicy;
use App\Models\AktivitasCard;
use App\Policies\AktivitasCardPolicy;
use App\Models\AktivitasList;
use App\Policies\AktivitasListPolicy;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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

        // Set locale waktu ke Indonesia untuk Carbon
        try {
            Carbon::setLocale('id');
        } catch (\Throwable $e) {
            // ignore if locale not available; per-call locale still applied in controller
        }
    }
}
