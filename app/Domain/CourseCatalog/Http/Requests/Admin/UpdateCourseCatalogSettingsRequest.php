<?php

namespace App\Domain\CourseCatalog\Http\Requests\Admin;

use App\Domain\CourseCatalog\Enums\DeliveryFormat;
use App\Domain\CourseCatalog\Enums\GroupDiscountLayout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCourseCatalogSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'early_bird_enabled' => $this->boolean('early_bird_enabled'),
            'group_discount_enabled' => $this->boolean('group_discount_enabled'),
        ]);
        if ($this->input('early_bird_days_before') === '') {
            $this->merge(['early_bird_days_before' => null]);
        }
        if ($this->input('early_bird_discount_percent') === '') {
            $this->merge(['early_bird_discount_percent' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'default_currency' => ['required', 'string', 'size:3'],
            'default_delivery_format' => ['required', new Enum(DeliveryFormat::class)],
            'default_language_code' => ['required', 'string', 'max:16'],
            'default_min_participants' => ['required', 'integer', 'min:1'],
            'tax_rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'early_bird_enabled' => ['boolean'],
            'early_bird_days_before' => ['nullable', 'integer', 'min:0'],
            'early_bird_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'group_discount_enabled' => ['boolean'],
            'group_discount_layout' => ['required', new Enum(GroupDiscountLayout::class)],
        ];
    }
}
