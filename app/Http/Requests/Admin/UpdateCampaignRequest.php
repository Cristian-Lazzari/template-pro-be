<?php

namespace App\Http\Requests\Admin;

use App\Models\Campaign;
use App\Services\Marketing\MarketingCustomerSegmentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $segmentService = app(MarketingCustomerSegmentService::class);
        $campaignType = $this->input('campaign_type');
        $segmentKeys = filled($campaignType)
            ? $segmentService->validSegmentKeysForCampaignType(Campaign::normalizeCampaignType($campaignType))
            : $segmentService->validSegmentKeys();

        return [
            'name' => ['required', 'string', 'max:255'],
            'submit_action' => ['required', Rule::in(['activate', 'draft'])],
            'campaign_type' => ['nullable', Rule::in(Campaign::campaignTypeValues())],
            'channel' => ['nullable', Rule::in(Campaign::channelValues())],
            'consent_basis' => ['nullable', Rule::in(Campaign::consentBasisValues())],
            'segment' => ['nullable', Rule::in($segmentKeys)],
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
