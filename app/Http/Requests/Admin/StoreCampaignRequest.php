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
            'submit_action' => ['required', Rule::in(['activate', 'draft'])],
            'segment' => ['nullable', Rule::in(['all', 'new_customers', 'inactive_customers', 'loyal_customers', 'high_spending_customers'])],
            'model_id' => ['nullable', 'exists:models,id'],
            'schedule_window' => ['nullable', Rule::in(['next_available', 'today_afternoon', 'today_evening', 'tomorrow_morning', 'tomorrow_lunch', 'tomorrow_evening', 'custom'])],
            'scheduled_at' => [
                Rule::requiredIf(fn () => $this->input('submit_action') === 'activate' && $this->input('schedule_window') === 'custom'),
                'nullable',
                'date',
            ],
            'promotions' => ['nullable', 'array'],
            'promotions.*' => ['exists:promotions,id'],
        ];
    }
}
