<?php

namespace App\Providers;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Policies\CoursePolicy;
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

        Route::bind('course', function (string $value): Course {
            return Course::query()->whereKey($value)->firstOrFail();
        });
    }
}
