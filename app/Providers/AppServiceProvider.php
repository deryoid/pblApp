<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\ProjectCard;
use App\Policies\ProjectCardPolicy;
use App\Models\ProjectList;
use App\Policies\ProjectListPolicy;

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
    }
}
