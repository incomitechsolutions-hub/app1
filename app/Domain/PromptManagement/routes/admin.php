<?php

use App\Domain\PromptManagement\Http\Controllers\Admin\AiPromptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:prompt_management'])->prefix('admin')->name('admin.prompt-management.')->group(function () {
    Route::delete('prompt-management/use-cases/{slug}', [AiPromptController::class, 'destroyUseCase'])
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
        ->name('use-cases.destroy');

    Route::resource('prompt-management/prompts', AiPromptController::class)
        ->parameters(['prompts' => 'ai_prompt'])
        ->except(['show']);
});
