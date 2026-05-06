<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'submit_action' => ['required', Rule::in(['activate', 'draft'])],
            'case_use' => ['nullable', Rule::in(['generic', 'take_away', 'delivery', 'table'])],
            'type_discount' => ['nullable', Rule::in(['fixed', 'percentage', 'gift'])],
            'discount' => ['exclude_if:type_discount,gift', 'required_if:type_discount,fixed,percentage', 'nullable', 'numeric', 'min:0'],
            'minimum_pretest' => ['nullable', 'numeric', 'min:0'],
            'cta' => ['nullable', 'string', 'max:255'],
            'permanent' => ['boolean'],
            'schedule_at' => ['exclude_if:permanent,1', 'nullable', 'date'],
            'expiring_at' => [
                'exclude_if:permanent,1',
                'nullable',
                'date',
                Rule::when($this->filled('schedule_at'), ['after_or_equal:schedule_at']),
            ],
            'metadata.reusable' => ['nullable', 'boolean'],
            'target_type' => ['required', Rule::in(['generic', 'product'])],
            'product_ids' => ['required_if:target_type,product', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'distinct', Rule::exists('products', 'id')],
        ];
    }
}
