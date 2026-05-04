<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('promotions', 'slug')],
            'status' => ['required', Rule::in(['draft', 'active', 'paused', 'archived'])],
            'case_use' => ['nullable', Rule::in(['generic', 'take_away', 'delivery', 'table', 'gift'])],
            'type_discount' => ['nullable', Rule::in(['fixed', 'percentage', 'gift'])],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'minimum_pretest' => ['nullable', 'numeric', 'min:0'],
            'cta' => ['nullable', 'string', 'max:255'],
            'permanent' => ['boolean'],
            'schedule_at' => ['nullable', 'date'],
            'expiring_at' => ['nullable', 'date', 'after_or_equal:schedule_at'],
            'metadata.reusable' => ['nullable', 'boolean'],
            'target_scope' => ['nullable', Rule::in(['generic', 'specific'])],
            'targets' => ['nullable', 'array'],
            'targets.*.target_key' => ['nullable', 'string', 'max:120'],
            'targets.*.discount' => ['nullable', 'numeric', 'min:0'],
            'targets.*.type_discount' => ['nullable', Rule::in(['fixed', 'percentage', 'gift'])],
        ];
    }
}
