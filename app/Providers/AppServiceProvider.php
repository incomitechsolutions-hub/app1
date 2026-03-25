<?php

namespace App\Providers;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\Program;
use App\Domain\CourseCatalog\Policies\CoursePolicy;
use App\Domain\CourseCatalog\Policies\ProgramPolicy;
use App\Domain\PromptManagement\Models\AiPrompt;
use App\Domain\PromptManagement\Policies\AiPromptPolicy;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
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
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(AiPrompt::class, AiPromptPolicy::class);
        Gate::define('manage-modules', fn () => true);
        Gate::define('manageAiSettings', fn (?User $user): bool => $user !== null);

        Route::bind('course', function (string $value): Course {
            return Course::query()->withTrashed()->whereKey($value)->firstOrFail();
        });
    }
}
