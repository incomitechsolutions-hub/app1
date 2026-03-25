<?php

namespace App\Domain\CourseCatalog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupDiscountTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'min_participants' => ['required', 'integer', 'min:1'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
