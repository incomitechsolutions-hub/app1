<?php

namespace App\Domain\Taxonomy\Http\Controllers\Admin;

use App\Domain\Taxonomy\Http\Requests\Admin\ExecuteCategoryImportRequest;
use App\Domain\Taxonomy\Http\Requests\Admin\PreviewCategoryImportRequest;
use App\Domain\Taxonomy\Services\CategoryCsvImportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class CategoryImportController extends Controller
{
    public function show(Request $request): View
    {
        $preview = $request->session()->get('category_import_preview');
        $result = $request->session()->get('category_import_result');

        return view('admin.categories.import', [
            'preview' => is_array($preview) ? $preview : null,
            'result' => is_array($result) ? $result : null,
            'defaultMapping' => $this->buildDefaultMapping(is_array($preview) ? ($preview['headers'] ?? []) : []),
        ]);
    }

    public function preview(
        PreviewCategoryImportRequest $request,
        CategoryCsvImportService $importService
    ): RedirectResponse {
        $validated = $request->validated();
        $delimiter = $this->normalizeDelimiter((string) $validated['delimiter']);
        $hasHeader = (bool) ($validated['has_header'] ?? false);

        $preview = $importService->preview(
            $request->file('csv_file'),
            $delimiter,
            $hasHeader
        );

        return redirect()
            ->route('admin.taxonomy.categories.import')
            ->withInput($request->except('csv_file'))
            ->with('category_import_preview', $preview)
            ->with('status', __('CSV wurde eingelesen. Bitte Mapping prüfen und Import starten.'));
    }

    public function import(
        ExecuteCategoryImportRequest $request,
        CategoryCsvImportService $importService
    ): RedirectResponse {
        try {
            $result = $importService->import($request->validated());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('admin.taxonomy.categories.import')
                ->withErrors($exception->errors())
                ->withInput();
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.taxonomy.categories.import')
                ->withErrors(['import' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('admin.taxonomy.categories.import')
            ->with('category_import_result', $result)
            ->with('category_import_preview', $result['preview'])
            ->with('status', __('CSV-Import abgeschlossen.'));
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, string>
     */
    private function buildDefaultMapping(array $headers): array
    {
        $targets = ['name', 'slug', 'description', 'parent_id', 'parent_slug', 'status'];
        $mapping = array_fill_keys($targets, '');

        foreach ($headers as $index => $header) {
            $normalized = Str::of($header)->lower()->replace([' ', '-'], '_')->toString();
            if (array_key_exists($normalized, $mapping) && $mapping[$normalized] === '') {
                $mapping[$normalized] = (string) $index;
            }
        }

        return $mapping;
    }

    private function normalizeDelimiter(string $delimiter): string
    {
        return $delimiter === '\t' ? "\t" : $delimiter;
    }
}
