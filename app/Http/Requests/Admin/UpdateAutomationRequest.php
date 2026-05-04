<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAutomationRequest extends FormRequest
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
            'trigger' => ['nullable', Rule::in(['order_inactive_30_days', 'reservation_inactive_30_days', 'birthday', 'first_order_completed', 'abandoned_profile'])],
            'model_id' => ['nullable', 'exists:models,id'],
            'promotions' => ['nullable', 'array'],
            'promotions.*' => ['exists:promotions,id'],
            'metadata.cooldown_days' => ['nullable', 'integer', 'min:0'],
            'metadata.enabled_from' => ['nullable', 'date'],
            'metadata.enabled_until' => ['nullable', 'date', 'after_or_equal:metadata.enabled_from'],
        ];
    }
}
