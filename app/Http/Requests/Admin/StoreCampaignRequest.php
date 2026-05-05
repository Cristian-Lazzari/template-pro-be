<?php

namespace App\Http\Requests\Admin;

use App\Services\Marketing\MarketingCustomerSegmentService;
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
        $segmentService = app(MarketingCustomerSegmentService::class);

        return [
            'name' => ['required', 'string', 'max:255'],
            'submit_action' => ['required', Rule::in(['activate', 'draft'])],
            'segment' => ['nullable', Rule::in($segmentService->validSegmentKeys())],
            'model_id' => ['nullable', 'exists:models,id'],
            'schedule_window' => ['nullable', Rule::in(['next_available', 'today_afternoon', 'today_evening', 'tomorrow_morning', 'tomorrow_lunch', 'tomorrow_evening'])],
            'scheduled_at' => [
                'nullable',
                'date',
            ],
            'promotions' => ['nullable', 'array'],
            'promotions.*' => ['exists:promotions,id'],
        ];
    }
}
