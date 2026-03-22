<?php

use App\Domain\CourseCatalog\Http\Controllers\Admin\CourseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.course-catalog.')->group(function () {
    Route::resource('courses', CourseController::class);
});
