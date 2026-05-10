<?php

namespace App\Providers;

use App\Models\Goal;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Reward;
use App\Models\Team;
use App\Policies\GoalPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RewardPolicy;
use App\Policies\TeamPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Reward::class, RewardPolicy::class);
        Gate::policy(Goal::class, GoalPolicy::class);

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
