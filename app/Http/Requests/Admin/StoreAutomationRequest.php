<?php

namespace App\Http\Requests\Admin;

use App\Services\Marketing\Automation\AutomationTriggerEvaluator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAutomationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->triggerMetadataRules($this->input('trigger'))
        );
    }

    private function baseRules(): array
    {
        $evaluator = app(AutomationTriggerEvaluator::class);

        return [
            'name'          => ['required', 'string', 'max:255'],
            'submit_action' => ['required', Rule::in(['activate', 'draft'])],
            'trigger'       => ['nullable', Rule::in($evaluator->supportedKeys())],
            'model_id'      => ['nullable', 'exists:models,id'],
            'promotions'    => ['nullable', 'array'],
            'promotions.*'  => ['exists:promotions,id'],

            // Shared metadata fields present on all automations.
            'metadata.cooldown_days'   => ['nullable', 'integer', 'min:0', 'max:730'],
            'metadata.enabled_from'    => ['nullable', 'date'],
            'metadata.enabled_until'   => ['nullable', 'date', 'after_or_equal:metadata.enabled_from'],
        ];
    }

    /**
     * Build prefixed validation rules for the selected trigger's metadata params.
     * Keys are returned as 'metadata.<param>' so Laravel maps them to the right input path.
     */
    private function triggerMetadataRules(?string $trigger): array
    {
        if ($trigger === null) {
            return [];
        }

        $evaluator  = app(AutomationTriggerEvaluator::class);
        $triggerObj = $evaluator->get($trigger);

        if ($triggerObj === null) {
            return [];
        }

        $prefixed = [];

        foreach ($triggerObj->validationRules() as $param => $rules) {
            $prefixed['metadata.' . $param] = $rules;
        }

        return $prefixed;
    }
}
