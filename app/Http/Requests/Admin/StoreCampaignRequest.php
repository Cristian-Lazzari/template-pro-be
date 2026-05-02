<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'active', 'paused', 'archived', 'sent'])],
            'segment' => ['nullable', Rule::in(['all', 'new_customers', 'inactive_customers', 'loyal_customers', 'high_spending_customers'])],
            'model_id' => ['nullable', 'exists:models,id'],
            'scheduled_at' => ['nullable', 'date'],
            'promotions' => ['nullable', 'array'],
            'promotions.*' => ['exists:promotions,id'],
        ];
    }
}
