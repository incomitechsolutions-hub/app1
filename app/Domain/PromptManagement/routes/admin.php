<?php

use App\Domain\PromptManagement\Http\Controllers\Admin\AiPromptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:prompt_management'])->prefix('admin')->name('admin.prompt-management.')->group(function () {
    Route::resource('prompt-management/prompts', AiPromptController::class)
        ->parameters(['prompts' => 'ai_prompt'])
        ->except(['show']);
});
