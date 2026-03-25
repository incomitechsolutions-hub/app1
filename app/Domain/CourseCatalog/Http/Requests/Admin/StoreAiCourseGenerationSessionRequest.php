<?php

namespace App\Domain\CourseCatalog\Http\Requests\Admin;

use App\Domain\CourseCatalog\Services\PromptPlaceholderInterpolationService;
use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Domain\PromptManagement\Models\AiPrompt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAiCourseGenerationSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('ai_prompt_id') === '' || $this->input('ai_prompt_id') === null) {
            $this->merge(['ai_prompt_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'brief' => ['required', 'string', 'max:12000'],
            'ai_prompt_id' => ['nullable', 'integer', 'exists:ai_prompts,id'],
            'placeholders' => ['nullable', 'array'],
            'placeholders.*' => ['nullable', 'string', 'max:8000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $id = $this->input('ai_prompt_id');
            if ($id === null || $id === '') {
                return;
            }

            $prompt = AiPrompt::query()->whereKey((int) $id)->first();
            if ($prompt === null) {
                return;
            }

            if ($prompt->use_case !== PromptUseCase::CourseCreation || ! $prompt->is_active) {
                $validator->errors()->add('ai_prompt_id', __('Die gewählte Vorlage ist für diesen Workflow nicht verfügbar.'));

                return;
            }

            /** @var PromptPlaceholderInterpolationService $interpolation */
            $interpolation = app(PromptPlaceholderInterpolationService::class);
            $keys = $interpolation->resolvePlaceholderKeys($prompt);

            foreach ($prompt->placeholder_definitions ?? [] as $row) {
                if (! is_array($row) || empty($row['name']) || empty($row['required'])) {
                    continue;
                }
                $name = (string) $row['name'];
                $val = $this->input('placeholders.'.$name);
                if ($val === null || trim((string) $val) === '') {
                    $validator->errors()->add('placeholders.'.$name, __('Dieses Feld ist erforderlich.'));
                }
            }

            foreach ($keys as $key) {
                $val = $this->input('placeholders.'.$key);
                if ($val !== null && ! is_string($val)) {
                    $validator->errors()->add('placeholders.'.$key, __('Ungültiger Wert.'));
                }
            }
        });
    }
}

