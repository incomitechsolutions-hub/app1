<?php

namespace App\Domain\Taxonomy\Http\Controllers\Admin;

use App\Domain\Taxonomy\Http\Requests\Admin\UpdateCategoryTaxonomySettingsRequest;
use App\Domain\Taxonomy\Models\CategoryTaxonomySetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryTaxonomySettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.categories.taxonomy-settings', [
            'settings' => CategoryTaxonomySetting::singleton(),
        ]);
    }

    public function update(UpdateCategoryTaxonomySettingsRequest $request): RedirectResponse
    {
        $settings = CategoryTaxonomySetting::singleton();
        $settings->update($request->validated());

        return redirect()
            ->route('admin.taxonomy.category-taxonomy-settings.edit')
            ->with('status', __('Einstellungen wurden gespeichert.'));
    }
}
