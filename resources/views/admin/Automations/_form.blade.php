@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $triggerDefinitions = $triggerDefinitions ?? [];
    $triggers = $triggers ?? [];
    $statuses = $statuses ?? [];
    $mailModels = $mailModels ?? collect();
    $promotions = $promotions ?? collect();
    $supportedTriggerKeys = [
        'no_interaction_since',
        'no_order_since',
        'no_booking_since',
        'birthday_before',
        'first_order_completed',
        'first_booking_completed',
        'orders_without_bookings',
        'bookings_without_orders',
        'customer_reaches_value',
        'valuable_customer_at_risk',
        'customer_anniversary',
        'high_average_order_value',
    ];
    $triggerIcons = [
        'no_interaction_since' => 'bi-person-x-fill',
        'no_order_since' => 'bi-bag-x-fill',
        'no_booking_since' => 'bi-calendar-x-fill',
        'birthday_before' => 'bi-cake2-fill',
        'first_order_completed' => 'bi-bag-check-fill',
        'first_booking_completed' => 'bi-calendar2-check-fill',
        'orders_without_bookings' => 'bi-bag-plus-fill',
        'bookings_without_orders' => 'bi-calendar-plus-fill',
        'customer_reaches_value' => 'bi-trophy-fill',
        'valuable_customer_at_risk' => 'bi-exclamation-diamond-fill',
        'customer_anniversary' => 'bi-stars',
        'high_average_order_value' => 'bi-graph-up-arrow',
    ];
    $metadataOptionLabels = [
        'threshold_type' => [
            'total_spent' => __('admin.marketing.automations.option_total_spent'),
            'orders_count' => __('admin.marketing.automations.option_orders_count'),
            'bookings_count' => __('admin.marketing.automations.option_bookings_count'),
        ],
        'value_type' => [
            'total_spent' => __('admin.marketing.automations.option_total_spent'),
            'orders_count' => __('admin.marketing.automations.option_orders_count'),
            'bookings_count' => __('admin.marketing.automations.option_bookings_count'),
            'customer_score' => __('admin.marketing.automations.option_customer_score'),
        ],
        'anniversary_source' => [
            'first_order' => __('admin.marketing.automations.option_first_order'),
            'first_booking' => __('admin.marketing.automations.option_first_booking'),
        ],
    ];
    $triggerMetaFields = [
        'no_interaction_since' => [
            ['key' => 'days', 'type' => 'number', 'label' => __('admin.marketing.automations.field_days'), 'icon' => 'bi-calendar-range', 'min' => 1, 'max' => 730, 'step' => 1, 'placeholder' => '30'],
        ],
        'no_order_since' => [
            ['key' => 'days', 'type' => 'number', 'label' => __('admin.marketing.automations.field_days'), 'icon' => 'bi-calendar-range', 'min' => 1, 'max' => 730, 'step' => 1, 'placeholder' => '30'],
        ],
        'no_booking_since' => [
            ['key' => 'days', 'type' => 'number', 'label' => __('admin.marketing.automations.field_days'), 'icon' => 'bi-calendar-range', 'min' => 1, 'max' => 730, 'step' => 1, 'placeholder' => '30'],
        ],
        'birthday_before' => [
            ['key' => 'days_before', 'type' => 'number', 'label' => __('admin.marketing.automations.field_days_before'), 'icon' => 'bi-calendar-heart', 'min' => 0, 'max' => 30, 'step' => 1, 'placeholder' => '0'],
        ],
        'first_order_completed' => [
            ['key' => 'delay_days', 'type' => 'number', 'label' => __('admin.marketing.automations.field_delay_days'), 'icon' => 'bi-hourglass-split', 'min' => 1, 'max' => 365, 'step' => 1, 'placeholder' => '30'],
        ],
        'first_booking_completed' => [
            ['key' => 'delay_days', 'type' => 'number', 'label' => __('admin.marketing.automations.field_delay_days'), 'icon' => 'bi-hourglass-split', 'min' => 1, 'max' => 365, 'step' => 1, 'placeholder' => '30'],
        ],
        'orders_without_bookings' => [
            ['key' => 'min_orders', 'type' => 'number', 'label' => __('admin.marketing.automations.field_min_orders'), 'icon' => 'bi-bag-plus', 'min' => 1, 'max' => 9999, 'step' => 1, 'placeholder' => '1'],
        ],
        'bookings_without_orders' => [
            ['key' => 'min_bookings', 'type' => 'number', 'label' => __('admin.marketing.automations.field_min_bookings'), 'icon' => 'bi-calendar-plus', 'min' => 1, 'max' => 9999, 'step' => 1, 'placeholder' => '1'],
        ],
        'customer_reaches_value' => [
            ['key' => 'threshold_type', 'type' => 'select', 'label' => __('admin.marketing.automations.field_threshold_type'), 'icon' => 'bi-sliders', 'options' => $metadataOptionLabels['threshold_type']],
            ['key' => 'threshold_value', 'type' => 'number', 'label' => __('admin.marketing.automations.field_threshold_value'), 'icon' => 'bi-trophy', 'min' => 0, 'step' => '0.01', 'placeholder' => '100'],
        ],
        'valuable_customer_at_risk' => [
            ['key' => 'value_type', 'type' => 'select', 'label' => __('admin.marketing.automations.field_value_type'), 'icon' => 'bi-gem', 'options' => $metadataOptionLabels['value_type']],
            ['key' => 'value_threshold', 'type' => 'number', 'label' => __('admin.marketing.automations.field_value_threshold'), 'icon' => 'bi-trophy', 'min' => 0, 'step' => '0.01', 'placeholder' => '50'],
            ['key' => 'inactive_days', 'type' => 'number', 'label' => __('admin.marketing.automations.field_inactive_days'), 'icon' => 'bi-calendar-x', 'min' => 1, 'max' => 730, 'step' => 1, 'placeholder' => '60'],
        ],
        'customer_anniversary' => [
            ['key' => 'anniversary_source', 'type' => 'select', 'label' => __('admin.marketing.automations.field_anniversary_source'), 'icon' => 'bi-stars', 'options' => $metadataOptionLabels['anniversary_source']],
            ['key' => 'days_before', 'type' => 'number', 'label' => __('admin.marketing.automations.field_days_before'), 'icon' => 'bi-calendar-heart', 'min' => 0, 'max' => 30, 'step' => 1, 'placeholder' => '0'],
        ],
        'high_average_order_value' => [
            ['key' => 'average_order_value', 'type' => 'number', 'label' => __('admin.marketing.automations.field_average_order_value'), 'icon' => 'bi-cash-coin', 'min' => 0, 'step' => '0.01', 'placeholder' => '30'],
            ['key' => 'min_orders', 'type' => 'number', 'label' => __('admin.marketing.automations.field_min_orders_optional'), 'icon' => 'bi-bag-plus', 'min' => 1, 'max' => 9999, 'step' => 1, 'placeholder' => __('admin.marketing.automations.optional_placeholder')],
        ],
    ];

    $triggerOptions = collect($supportedTriggerKeys)
        ->mapWithKeys(function ($key) use ($triggerDefinitions, $triggers, $triggerIcons, $triggerMetaFields) {
            $definition = $triggerDefinitions[$key] ?? [];
            $translationKey = 'admin.marketing.automations.trigger_' . $key;
            $translatedLabel = __($translationKey);
            $label = $triggers[$key] ?? ($definition['label'] ?? ($translatedLabel !== $translationKey ? $translatedLabel : $key));
            $description = $definition['description'] ?? __('admin.marketing.automations.trigger_description_fallback');

            return [$key => [
                'key' => $key,
                'label' => $label,
                'description' => $description,
                'icon' => $triggerIcons[$key] ?? 'bi-lightning-charge-fill',
                'default_metadata' => $definition['default_metadata'] ?? [],
                'fields' => collect($triggerMetaFields[$key] ?? [])->pluck('key')->all(),
            ]];
        })
        ->all();

    $selectedPromotionIds = collect(old('promotions', $automation->exists ? $automation->promotions->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $selectedTrigger = old('trigger', $automation->trigger);
    $selectedTrigger = array_key_exists($selectedTrigger, $triggerOptions) ? $selectedTrigger : '';
    $selectedMailModelId = (string) old('model_id', $automation->model_id);
    $previewMailModel = collect($mailModels)->first(fn ($mailModel) => (string) $mailModel->id === $selectedMailModelId) ?: $automation->model;
    $previewPromotions = collect($promotions)
        ->filter(fn ($promotion) => in_array((string) $promotion->id, $selectedPromotionIds, true))
        ->values();
    $cooldownValue = old('metadata.cooldown_days', data_get($automation->metadata, 'cooldown_days'));
    $enabledFromValue = old('metadata.enabled_from', data_get($automation->metadata, 'enabled_from'));
    $enabledUntilValue = old('metadata.enabled_until', data_get($automation->metadata, 'enabled_until'));
    $primaryActionLabel = $method === 'POST'
        ? __('admin.marketing.automations.create_activate')
        : __('admin.marketing.automations.save_activate');
    $cancelUrl = $automation->exists ? route('admin.automations.show', $automation) : route('admin.automations.index');
    $statusPreviewLabel = $automation->exists
        ? ($statuses[$automation->status] ?? $automation->status)
        : __('admin.marketing.automations.status_draft');

    $triggerErrorFields = collect($triggerMetaFields[$selectedTrigger] ?? [])
        ->pluck('key')
        ->map(fn ($key) => 'metadata.' . $key)
        ->all();
    $sharedErrorFields = ['model_id', 'promotions', 'promotions.*', 'metadata.cooldown_days', 'metadata.enabled_from', 'metadata.enabled_until'];
    $hasErrors = $errors->any();
    if ($hasErrors && $errors->hasAny(['name', 'trigger'])) {
        $initialStep = 1;
    } elseif ($hasErrors && $errors->hasAny($triggerErrorFields)) {
        $initialStep = 2;
    } elseif ($hasErrors && $errors->hasAny($sharedErrorFields)) {
        $initialStep = 3;
    } elseif ($hasErrors) {
        $initialStep = 4;
    } elseif ($automation->exists) {
        $initialStep = 4;
    } else {
        $initialStep = 1;
    }

    $metadataSummaryTemplates = [
        'no_interaction_since' => __('admin.marketing.automations.summary_no_interaction_since', ['days' => '__DAYS__']),
        'no_order_since' => __('admin.marketing.automations.summary_no_order_since', ['days' => '__DAYS__']),
        'no_booking_since' => __('admin.marketing.automations.summary_no_booking_since', ['days' => '__DAYS__']),
        'birthday_before' => __('admin.marketing.automations.summary_birthday_before', ['days' => '__DAYS__']),
        'first_order_completed' => __('admin.marketing.automations.summary_first_order_completed', ['days' => '__DAYS__']),
        'first_booking_completed' => __('admin.marketing.automations.summary_first_booking_completed', ['days' => '__DAYS__']),
        'orders_without_bookings' => __('admin.marketing.automations.summary_orders_without_bookings', ['orders' => '__ORDERS__']),
        'bookings_without_orders' => __('admin.marketing.automations.summary_bookings_without_orders', ['bookings' => '__BOOKINGS__']),
        'customer_reaches_value' => __('admin.marketing.automations.summary_customer_reaches_value', ['type' => '__TYPE__', 'value' => '__VALUE__']),
        'valuable_customer_at_risk' => __('admin.marketing.automations.summary_valuable_customer_at_risk', ['type' => '__TYPE__', 'value' => '__VALUE__', 'days' => '__DAYS__']),
        'customer_anniversary' => __('admin.marketing.automations.summary_customer_anniversary', ['source' => '__SOURCE__', 'days' => '__DAYS__']),
        'high_average_order_value' => __('admin.marketing.automations.summary_high_average_order_value', ['value' => '__VALUE__', 'orders' => '__ORDERS__']),
    ];

    $metadataPreviewValue = function (string $key, $default = null) use ($automation) {
        return old('metadata.' . $key, data_get($automation->metadata, $key, $default));
    };
@endphp

@include('admin.Marketing.partials.form-style')

@if ($errors->any())
    <div class="alert alert-danger">
        {{ __('admin.marketing.automations.check_fields') }}
    </div>
@endif

<form
    class="creation marketing-form-shell automation-form-shell mt-4"
    action="{{ $action }}"
    method="POST"
    data-automation-form
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="promo-wiz__bar">
        @foreach ([1 => __('admin.marketing.automations.step_identity'), 2 => __('admin.marketing.automations.step_parameters'), 3 => __('admin.marketing.automations.step_delivery'), 4 => __('admin.marketing.automations.step_summary')] as $stepNumber => $stepLabel)
            <div class="promo-wiz__dot {{ $initialStep === $stepNumber ? 'is-active' : ($initialStep > $stepNumber ? 'is-done' : '') }}" data-step-dot="{{ $stepNumber }}">
                <span class="promo-wiz__dot-num">
                    @if ($initialStep > $stepNumber) <i class="bi bi-check-lg"></i> @else {{ $stepNumber }} @endif
                </span>
                <span class="promo-wiz__dot-lbl">{{ $stepLabel }}</span>
            </div>
            @if ($stepNumber < 4)
                <div class="promo-wiz__line {{ $initialStep > $stepNumber ? 'is-done' : '' }}" data-step-line="{{ $stepNumber }}"></div>
            @endif
        @endforeach
    </div>

    <div class="marketing-form-grid">
        <div class="marketing-form-main">
            <div class="promo-wiz__panel" data-wiz-panel="1" @if ($initialStep !== 1) hidden @endif>
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-card-text"></i>
                            </span>
                            {{ __('admin.marketing.automations.information') }}
                        </h3>
                    </div>

                    <div>
                        <label class="label_c" for="name">
                            <i class="bi bi-type"></i>
                            {{ __('admin.marketing.automations.name') }}
                        </label>
                        <p>
                            <input
                                value="{{ old('name', $automation->name) }}"
                                type="text"
                                name="name"
                                id="name"
                                placeholder="{{ __('admin.marketing.automations.name_placeholder') }}"
                                autocomplete="off"
                                data-automation-name
                            >
                        </p>
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </span>
                            {{ __('admin.marketing.automations.trigger') }}
                        </h3>
                    </div>

                    <div class="model-type-picker">
                        @foreach ($triggerOptions as $triggerKey => $triggerOption)
                            <label class="model-type-option">
                                <input
                                    type="radio"
                                    name="trigger"
                                    value="{{ $triggerKey }}"
                                    data-trigger-radio
                                    @checked($selectedTrigger === $triggerKey)
                                >
                                <div class="model-type-option__card">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span class="model-type-option__icon">
                                            <i class="bi {{ $triggerOption['icon'] }}"></i>
                                        </span>
                                        <span class="model-type-option__dot"></span>
                                    </div>
                                    <div class="model-type-option__label">
                                        <strong>{{ $triggerOption['label'] }}</strong>
                                        <small>{{ $triggerOption['description'] }}</small>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('trigger') <p class="error mt-2">{{ $message }}</p> @enderror
                </section>
            </div>

            <div class="promo-wiz__panel" data-wiz-panel="2" @if ($initialStep !== 2) hidden @endif>
                @foreach ($triggerOptions as $triggerKey => $triggerOption)
                    <section
                        class="order-detail__section promo-reveal"
                        data-trigger-panel="{{ $triggerKey }}"
                        @if ($selectedTrigger !== $triggerKey) hidden @endif
                    >
                        <div class="order-detail__section-head">
                            <h3>
                                <span class="order-detail__section-icon">
                                    <i class="bi {{ $triggerOption['icon'] }}"></i>
                                </span>
                                {{ $triggerOption['label'] }}
                            </h3>
                        </div>

                        <p class="marketing-form-preview__note" style="margin-bottom: 14px;">
                            {{ $triggerOption['description'] }}
                        </p>

                        <div class="split">
                            @foreach ($triggerMetaFields[$triggerKey] ?? [] as $field)
                                @php
                                    $fieldKey = $field['key'];
                                    $fieldDefault = data_get($triggerDefinitions, $triggerKey . '.default_metadata.' . $fieldKey, null);
                                    if ($fieldDefault === null && array_key_exists('options', $field)) {
                                        $fieldDefault = array_key_first($field['options']);
                                    }
                                    $fieldValue = $metadataPreviewValue($fieldKey, $fieldDefault);
                                    $inputId = 'metadata_' . $triggerKey . '_' . $fieldKey;
                                    $isDisabled = $selectedTrigger !== $triggerKey;
                                @endphp
                                <div>
                                    <label class="label_c" for="{{ $inputId }}">
                                        <i class="bi {{ $field['icon'] }}"></i>
                                        {{ $field['label'] }}
                                    </label>
                                    <p>
                                        @if (($field['type'] ?? 'number') === 'select')
                                            <select
                                                name="metadata[{{ $fieldKey }}]"
                                                id="{{ $inputId }}"
                                                data-metadata-key="{{ $fieldKey }}"
                                                data-default-value="{{ $fieldDefault }}"
                                                @disabled($isDisabled)
                                            >
                                                @foreach ($field['options'] as $optionValue => $optionLabel)
                                                    <option value="{{ $optionValue }}" @selected((string) $fieldValue === (string) $optionValue)>{{ $optionLabel }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input
                                                type="number"
                                                name="metadata[{{ $fieldKey }}]"
                                                id="{{ $inputId }}"
                                                value="{{ $fieldValue }}"
                                                min="{{ $field['min'] ?? 0 }}"
                                                @if (isset($field['max'])) max="{{ $field['max'] }}" @endif
                                                step="{{ $field['step'] ?? 1 }}"
                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                                data-metadata-key="{{ $fieldKey }}"
                                                data-default-value="{{ $fieldDefault }}"
                                                @disabled($isDisabled)
                                            >
                                        @endif
                                    </p>
                                    @if ($errors->has('metadata.' . $fieldKey))
                                        <p class="error">{{ $errors->first('metadata.' . $fieldKey) }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="order-detail__section" data-empty-trigger-panel @if ($selectedTrigger) hidden @endif>
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </span>
                            {{ __('admin.marketing.automations.step_parameters') }}
                        </h3>
                    </div>
                    <p class="marketing-form-preview__note">
                        {{ __('admin.marketing.automations.choose_trigger_parameters') }}
                    </p>
                </section>
            </div>

            <div class="promo-wiz__panel" data-wiz-panel="3" @if ($initialStep !== 3) hidden @endif>
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-megaphone-fill"></i>
                            </span>
                            {{ __('admin.marketing.automations.linked_promotions') }}
                        </h3>
                    </div>

                    <div class="model-type-picker" data-automation-promotion-picker>
                        @forelse ($promotions as $promotion)
                            @php
                                $promotionId = (string) $promotion->id;
                                $promotionChecked = in_array($promotionId, $selectedPromotionIds, true);
                                $isSelectable = $promotion->isActive() || $promotionChecked;
                            @endphp
                            <label class="model-type-option {{ ! $isSelectable ? 'model-type-option--disabled' : '' }}" @if (! $isSelectable) aria-disabled="true" @endif>
                                <input
                                    type="checkbox"
                                    name="promotions[]"
                                    value="{{ $promotion->id }}"
                                    data-promotion-checkbox
                                    data-promotion-label="{{ $promotion->name }}"
                                    @checked($promotionChecked)
                                    @disabled(! $isSelectable)
                                >
                                <div class="model-type-option__card">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span class="model-type-option__icon">
                                            <i class="bi bi-megaphone-fill"></i>
                                        </span>
                                        <span class="model-type-option__dot"></span>
                                    </div>
                                    <div class="model-type-option__label">
                                        <strong>{{ $promotion->name }}</strong>
                                        <small>{{ $promotion->slug }} · {{ $statuses[$promotion->status] ?? $promotion->status }}</small>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="marketing-form-preview__note">
                                {{ __('admin.marketing.campaigns.no_promotions_available') }}
                            </div>
                        @endforelse
                    </div>
                    @error('promotions') <p class="error mt-2">{{ $message }}</p> @enderror
                    @error('promotions.*') <p class="error mt-2">{{ $message }}</p> @enderror
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-send-fill"></i>
                            </span>
                            {{ __('admin.marketing.automations.delivery_settings') }}
                        </h3>
                    </div>

                    <div class="split">
                        <div>
                            <label class="label_c" for="model_id">
                                <i class="bi bi-envelope-fill"></i>
                                {{ __('admin.marketing.automations.mail_model') }}
                            </label>
                            <p>
                                <select name="model_id" id="model_id" data-mail-model-select>
                                    <option value="" data-preview-label="{{ __('admin.marketing.automations.to_choose') }}">{{ __('admin.marketing.automations.no_model') }}</option>
                                    @foreach ($mailModels as $mailModel)
                                        <option value="{{ $mailModel->id }}" data-preview-label="{{ $mailModel->name }}" @selected((string) old('model_id', $automation->model_id) === (string) $mailModel->id)>
                                            {{ $mailModel->name }}
                                            @if ($mailModel->object)
                                                - {{ $mailModel->object }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </p>
                            @error('model_id') <p class="error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <span class="label_c">
                                <i class="bi bi-broadcast-pin"></i>
                                {{ __('admin.marketing.automations.channel') }}
                            </span>
                            <div class="model-type-picker" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                                <label class="model-type-option">
                                    <input type="radio" value="email" checked data-channel-radio>
                                    <div class="model-type-option__card">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span class="model-type-option__icon"><i class="bi bi-envelope-fill"></i></span>
                                            <span class="model-type-option__dot"></span>
                                        </div>
                                        <div class="model-type-option__label">
                                            <strong>{{ __('admin.common.email') }}</strong>
                                        </div>
                                    </div>
                                </label>
                                <label class="model-type-option model-type-option--disabled" aria-disabled="true">
                                    <input type="radio" value="whatsapp" disabled>
                                    <div class="model-type-option__card">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span class="model-type-option__icon"><i class="bi bi-whatsapp"></i></span>
                                            <span class="model-type-option__dot"></span>
                                        </div>
                                        <div class="model-type-option__label">
                                            <strong>{{ __('admin.marketing.automations.whatsapp') }}</strong>
                                            <small>{{ __('admin.marketing.automations.not_available') }}</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <p class="marketing-form-preview__note mt-3" data-send-window-label>
                        <i class="bi bi-clock-history" style="margin-right: 6px; opacity: 0.7;"></i>{{ __('admin.marketing.automations.runner_window_note') }}
                    </p>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-sliders"></i>
                            </span>
                            {{ __('admin.marketing.automations.limits_period') }}
                        </h3>
                    </div>

                    <div class="split">
                        <div>
                            <label class="label_c" for="metadata_cooldown_days">
                                <i class="bi bi-hourglass-split"></i>
                                {{ __('admin.marketing.automations.cooldown_days') }}
                            </label>
                            <p>
                                <input
                                    value="{{ $cooldownValue }}"
                                    type="number"
                                    min="0"
                                    max="730"
                                    step="1"
                                    name="metadata[cooldown_days]"
                                    id="metadata_cooldown_days"
                                    placeholder="30"
                                    data-cooldown-input
                                >
                                <span>{{ __('admin.marketing.automations.cooldown_days_suffix') }}</span>
                            </p>
                            @error('metadata.cooldown_days') <p class="error">{{ $message }}</p> @enderror
                            <p class="marketing-form-preview__note mt-2">
                                {{ __('admin.marketing.automations.cooldown_help') }}
                            </p>
                        </div>
                        <div>
                            <label class="label_c" for="metadata_enabled_from">
                                <i class="bi bi-calendar-plus"></i>
                                {{ __('admin.marketing.automations.enabled_from') }}
                            </label>
                            <p>
                                <input value="{{ $enabledFromValue }}" type="date" name="metadata[enabled_from]" id="metadata_enabled_from" data-enabled-from-input>
                            </p>
                            @error('metadata.enabled_from') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label_c" for="metadata_enabled_until">
                                <i class="bi bi-calendar-x"></i>
                                {{ __('admin.marketing.automations.enabled_until') }}
                            </label>
                            <p>
                                <input value="{{ $enabledUntilValue }}" type="date" name="metadata[enabled_until]" id="metadata_enabled_until" data-enabled-until-input>
                            </p>
                            @error('metadata.enabled_until') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-toggle-on"></i>
                            </span>
                            {{ __('admin.marketing.automations.status') }}
                        </h3>
                    </div>
                    <div class="model-type-picker">
                        <div class="model-type-option">
                            <div class="model-type-option__card">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="model-type-option__icon"><i class="bi bi-clock-history"></i></span>
                                    <span class="model-type-option__dot"></span>
                                </div>
                                <div class="model-type-option__label">
                                    <strong>{{ __('admin.marketing.automations.status_draft') }}</strong>
                                    <small>{{ __('admin.marketing.automations.status_draft_note') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="model-type-option">
                            <div class="model-type-option__card">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="model-type-option__icon"><i class="bi bi-check2-circle"></i></span>
                                    <span class="model-type-option__dot"></span>
                                </div>
                                <div class="model-type-option__label">
                                    <strong>{{ __('admin.marketing.automations.status_active') }}</strong>
                                    <small>{{ __('admin.marketing.automations.status_active_note') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="promo-wiz__panel" data-wiz-panel="4" @if ($initialStep !== 4) hidden @endif>
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-check2-square"></i>
                            </span>
                            {{ __('admin.marketing.automations.final_review') }}
                        </h3>
                    </div>

                    <div class="marketing-detail__compact-grid">
                        <article class="marketing-detail__fact">
                            <span>{{ __('admin.marketing.automations.name') }}</span>
                            <strong data-final-name>{{ old('name', $automation->name) ?: __('admin.marketing.automations.to_choose') }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>{{ __('admin.marketing.automations.trigger') }}</span>
                            <strong data-final-trigger>{{ $selectedTrigger ? ($triggerOptions[$selectedTrigger]['label'] ?? $selectedTrigger) : __('admin.marketing.automations.to_choose') }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>{{ __('admin.marketing.automations.parameters') }}</span>
                            <strong data-final-parameters>{{ __('admin.marketing.automations.to_choose') }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>{{ __('admin.marketing.automations.promotions') }}</span>
                            <strong data-final-promotion>{{ $previewPromotions->first()?->name ?? __('admin.marketing.automations.no_promotion') }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>{{ __('admin.marketing.automations.channel') }}</span>
                            <strong>{{ __('admin.common.email') }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>{{ __('admin.marketing.automations.mail_model') }}</span>
                            <strong data-final-model>{{ $previewMailModel?->name ?? __('admin.marketing.automations.to_choose') }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>{{ __('admin.marketing.automations.send_window') }}</span>
                            <strong>{{ __('admin.marketing.automations.runner_window_short') }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>{{ __('admin.marketing.automations.cooldown') }}</span>
                            <strong data-final-cooldown>{{ $cooldownValue !== null && $cooldownValue !== '' ? __('admin.marketing.automations.days_count', ['count' => $cooldownValue]) : __('admin.marketing.automations.no_cooldown') }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>{{ __('admin.marketing.automations.period') }}</span>
                            <strong data-final-period>{{ __('admin.marketing.automations.always_active') }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>{{ __('admin.marketing.automations.status') }}</span>
                            <strong>{{ __('admin.marketing.automations.status_by_cta') }}</strong>
                        </article>
                    </div>
                </section>
            </div>
        </div>

        <aside class="marketing-form-sidebar automation-form-sidebar">
            <div class="cpv2-nav">
                <button type="button" class="cpv2-nav-btn" data-wiz-prev @if ($initialStep === 1) hidden @endif>
                    <i class="bi bi-chevron-left"></i>
                    <span>{{ __('admin.common.back') }}</span>
                </button>
                <span class="cpv2-nav-step" data-nav-step-indicator>{{ $initialStep }}/4</span>
                <button type="button" class="cpv2-nav-btn cpv2-nav-btn--primary" data-wiz-next @if ($initialStep === 4) hidden @endif>
                    <span>{{ __('admin.common.next') }}</span>
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>

            <div class="cpv2-card">
                <div class="cpv2-header">
                    <div class="cpv2-header-icon">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <span class="cpv2-eyebrow">{{ __('admin.marketing.automations.summary') }}</span>
                        <strong class="cpv2-name-display" data-preview-name>{{ old('name', $automation->name) ?: __('admin.marketing.automations.name_placeholder') }}</strong>
                        <div class="cpv2-badges">
                            <span class="cpv2-badge">{{ $statusPreviewLabel }}</span>
                            <span class="cpv2-badge cpv2-badge--muted">{{ __('admin.common.email') }}</span>
                        </div>
                    </div>
                </div>

                <ul class="cpv2-rows" role="list">
                    <li class="cpv2-row {{ old('name', $automation->name) ? 'cpv2-row--done' : 'cpv2-row--empty' }}" data-preview-row="name">
                        <span class="cpv2-row-icon"><i class="bi {{ old('name', $automation->name) ? 'bi-check-lg' : 'bi-circle' }}" data-row-icon-name></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.automations.name') }}</span>
                        <span class="cpv2-row-val" data-preview-name-val>{{ old('name', $automation->name) ?: __('admin.marketing.automations.to_choose') }}</span>
                    </li>
                    <li class="cpv2-row {{ $selectedTrigger ? 'cpv2-row--done' : 'cpv2-row--empty' }}" data-preview-row="trigger">
                        <span class="cpv2-row-icon"><i class="bi {{ $selectedTrigger ? 'bi-check-lg' : 'bi-circle' }}" data-row-icon-trigger></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.automations.trigger') }}</span>
                        <span class="cpv2-row-val" data-preview-trigger>{{ $selectedTrigger ? ($triggerOptions[$selectedTrigger]['label'] ?? $selectedTrigger) : __('admin.marketing.automations.to_choose') }}</span>
                    </li>
                    <li class="cpv2-row {{ $selectedTrigger ? 'cpv2-row--done' : 'cpv2-row--empty' }}" data-preview-row="parameters">
                        <span class="cpv2-row-icon"><i class="bi {{ $selectedTrigger ? 'bi-check-lg' : 'bi-circle' }}" data-row-icon-parameters></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.automations.parameters') }}</span>
                        <span class="cpv2-row-val" data-preview-parameters>{{ __('admin.marketing.automations.to_choose') }}</span>
                    </li>
                    <li class="cpv2-row {{ $previewPromotions->isNotEmpty() ? 'cpv2-row--done' : 'cpv2-row--optional' }}" data-preview-row="promotion">
                        <span class="cpv2-row-icon"><i class="bi {{ $previewPromotions->isNotEmpty() ? 'bi-check-lg' : 'bi-dash-lg' }}" data-row-icon-promotion></i></span>
                        <span class="cpv2-row-label">{{ __('admin.common.promo') }}</span>
                        <span class="cpv2-row-val" data-preview-promotion>{{ $previewPromotions->first()?->name ?? __('admin.marketing.automations.no_promotion') }}</span>
                    </li>
                    <li class="cpv2-row cpv2-row--done" data-preview-row="channel">
                        <span class="cpv2-row-icon"><i class="bi bi-check-lg"></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.automations.channel') }}</span>
                        <span class="cpv2-row-val">{{ __('admin.common.email') }}</span>
                    </li>
                    <li class="cpv2-row {{ $previewMailModel ? 'cpv2-row--done' : 'cpv2-row--empty' }}" data-preview-row="model">
                        <span class="cpv2-row-icon"><i class="bi {{ $previewMailModel ? 'bi-check-lg' : 'bi-circle' }}" data-row-icon-model></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.automations.model') }}</span>
                        <span class="cpv2-row-val" data-preview-model>{{ $previewMailModel?->name ?? __('admin.marketing.automations.to_choose') }}</span>
                    </li>
                    <li class="cpv2-row cpv2-row--done" data-preview-row="window">
                        <span class="cpv2-row-icon"><i class="bi bi-check-lg"></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.automations.send_window') }}</span>
                        <span class="cpv2-row-val">{{ __('admin.marketing.automations.runner_window_short') }}</span>
                    </li>
                    <li class="cpv2-row {{ $cooldownValue !== null && $cooldownValue !== '' ? 'cpv2-row--done' : 'cpv2-row--optional' }}" data-preview-row="cooldown">
                        <span class="cpv2-row-icon"><i class="bi {{ $cooldownValue !== null && $cooldownValue !== '' ? 'bi-check-lg' : 'bi-dash-lg' }}" data-row-icon-cooldown></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.automations.cooldown') }}</span>
                        <span class="cpv2-row-val" data-preview-cooldown>{{ $cooldownValue !== null && $cooldownValue !== '' ? __('admin.marketing.automations.days_count', ['count' => $cooldownValue]) : __('admin.marketing.automations.no_cooldown') }}</span>
                    </li>
                    <li class="cpv2-row cpv2-row--optional" data-preview-row="status">
                        <span class="cpv2-row-icon"><i class="bi bi-dash-lg"></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.automations.status') }}</span>
                        <span class="cpv2-row-val">{{ __('admin.marketing.automations.status_by_cta') }}</span>
                    </li>
                </ul>
            </div>
        </aside>
    </div>

    <div class="marketing-form-actions">
        <a class="order-detail__contact marketing-form-action--cancel" href="{{ $cancelUrl }}">
            <i class="bi bi-x-lg"></i>
            <span>{{ __('admin.common.cancel') }}</span>
        </a>
        <button class="order-detail__contact marketing-form-action--secondary" type="submit" name="submit_action" value="draft" data-wiz-draft>
            <i class="bi bi-clock-history"></i>
            <span>{{ __('admin.marketing.automations.complete_later') }}</span>
        </button>
        <button class="order-detail__contact marketing-form-action--primary" type="submit" name="submit_action" value="activate" data-wiz-submit @if ($initialStep !== 4) hidden @endif>
            <i class="bi bi-check2-circle"></i>
            <span>{{ $primaryActionLabel }}</span>
        </button>
        @error('submit_action') <p class="error">{{ $message }}</p> @enderror
    </div>
</form>

@php
    $automationFormCopy = [
        'toChoose' => __('admin.marketing.automations.to_choose'),
        'namePlaceholder' => __('admin.marketing.automations.name_placeholder'),
        'noPromotion' => __('admin.marketing.automations.no_promotion'),
        'noModel' => __('admin.marketing.automations.to_choose'),
        'noCooldown' => __('admin.marketing.automations.no_cooldown'),
        'daysCount' => __('admin.marketing.automations.days_count', ['count' => '__COUNT__']),
        'periodAlways' => __('admin.marketing.automations.always_active'),
        'periodFrom' => __('admin.marketing.automations.period_from', ['date' => '__FROM__']),
        'periodUntil' => __('admin.marketing.automations.period_until', ['date' => '__UNTIL__']),
        'periodRange' => __('admin.marketing.automations.period_range', ['from' => '__FROM__', 'until' => '__UNTIL__']),
        'optionalOrders' => __('admin.marketing.automations.optional_orders'),
    ];
@endphp

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-automation-form]');
    if (!form) return;

    const triggerDefinitions = @json($triggerOptions);
    const summaryTemplates = @json($metadataSummaryTemplates);
    const optionLabels = @json($metadataOptionLabels);
    const copy = @json($automationFormCopy);
    const interpolate = (template, values) => Object.entries(values).reduce(
        (text, [key, value]) => text.split(key).join(value ?? ''),
        template || ''
    );

    let currentStep = {{ $initialStep }};
    const totalSteps = 4;
    const nameInput = form.querySelector('[data-automation-name]');
    const triggerRadios = form.querySelectorAll('[data-trigger-radio]');
    const triggerPanels = form.querySelectorAll('[data-trigger-panel]');
    const emptyTriggerPanel = form.querySelector('[data-empty-trigger-panel]');
    const promotionInputs = form.querySelectorAll('[data-promotion-checkbox]');
    const mailModelSelect = form.querySelector('[data-mail-model-select]');
    const cooldownInput = form.querySelector('[data-cooldown-input]');
    const enabledFromInput = form.querySelector('[data-enabled-from-input]');
    const enabledUntilInput = form.querySelector('[data-enabled-until-input]');
    const btnPrev = form.querySelectorAll('[data-wiz-prev]');
    const btnNext = form.querySelectorAll('[data-wiz-next]');
    const btnSubmit = form.querySelector('[data-wiz-submit]');

    const setHidden = (target, hidden) => {
        if (!target) return;
        if (target instanceof NodeList || Array.isArray(target)) {
            target.forEach((el) => { el.hidden = hidden; });
            return;
        }
        target.hidden = hidden;
    };

    const setRowDone = (rowAttr, done, optional = false) => {
        const row = form.querySelector(`[data-preview-row="${rowAttr}"]`);
        if (!row) return;
        row.classList.toggle('cpv2-row--done', done);
        row.classList.toggle('cpv2-row--empty', !done && !optional);
        row.classList.toggle('cpv2-row--optional', !done && optional);
    };

    const selectedTrigger = () => Array.from(triggerRadios).find((radio) => radio.checked)?.value || '';

    const selectedOption = (select) => select?.options[select.selectedIndex] || null;

    const enabledMetadataInput = (key) => Array.from(form.querySelectorAll(`[data-metadata-key="${key}"]`))
        .find((input) => !input.disabled) || null;

    const metadataValue = (key) => enabledMetadataInput(key)?.value?.trim() || '';

    const optionLabel = (group, value) => optionLabels[group]?.[value] || value || copy.toChoose;

    const activeTriggerDefinition = () => triggerDefinitions[selectedTrigger()] || null;

    const setInputsDisabled = (container, disabled) => {
        container?.querySelectorAll('input[name], select[name], textarea[name]').forEach((input) => {
            input.disabled = disabled;
        });
    };

    const syncTriggerPanels = () => {
        const trigger = selectedTrigger();
        triggerPanels.forEach((panel) => {
            const active = panel.dataset.triggerPanel === trigger;
            panel.hidden = !active;
            setInputsDisabled(panel, !active);

            if (active) {
                panel.querySelectorAll('[data-metadata-key]').forEach((input) => {
                    if ((input.value || '').trim() !== '') return;
                    const defaultValue = input.dataset.defaultValue;
                    if (defaultValue !== undefined && defaultValue !== '') {
                        input.value = defaultValue;
                    }
                });
            }
        });

        if (emptyTriggerPanel) {
            emptyTriggerPanel.hidden = Boolean(trigger);
        }
    };

    const buildParameterSummary = () => {
        const trigger = selectedTrigger();
        const template = summaryTemplates[trigger];
        if (!template) return copy.toChoose;

        if (trigger === 'no_interaction_since' || trigger === 'no_order_since' || trigger === 'no_booking_since') {
            return interpolate(template, {'__DAYS__': metadataValue('days') || '0'});
        }

        if (trigger === 'birthday_before') {
            return interpolate(template, {'__DAYS__': metadataValue('days_before') || '0'});
        }

        if (trigger === 'first_order_completed' || trigger === 'first_booking_completed') {
            return interpolate(template, {'__DAYS__': metadataValue('delay_days') || '0'});
        }

        if (trigger === 'orders_without_bookings') {
            return interpolate(template, {'__ORDERS__': metadataValue('min_orders') || '0'});
        }

        if (trigger === 'bookings_without_orders') {
            return interpolate(template, {'__BOOKINGS__': metadataValue('min_bookings') || '0'});
        }

        if (trigger === 'customer_reaches_value') {
            return interpolate(template, {
                '__TYPE__': optionLabel('threshold_type', metadataValue('threshold_type')),
                '__VALUE__': metadataValue('threshold_value') || '0',
            });
        }

        if (trigger === 'valuable_customer_at_risk') {
            return interpolate(template, {
                '__TYPE__': optionLabel('value_type', metadataValue('value_type')),
                '__VALUE__': metadataValue('value_threshold') || '0',
                '__DAYS__': metadataValue('inactive_days') || '0',
            });
        }

        if (trigger === 'customer_anniversary') {
            return interpolate(template, {
                '__SOURCE__': optionLabel('anniversary_source', metadataValue('anniversary_source')),
                '__DAYS__': metadataValue('days_before') || '0',
            });
        }

        if (trigger === 'high_average_order_value') {
            return interpolate(template, {
                '__VALUE__': metadataValue('average_order_value') || '0',
                '__ORDERS__': metadataValue('min_orders') || copy.optionalOrders,
            });
        }

        return copy.toChoose;
    };

    const selectedPromotion = () => Array.from(promotionInputs).find((input) => input.checked);

    const syncPromotions = () => {
        const selected = selectedPromotion();

        if (selected) {
            promotionInputs.forEach((input) => {
                if (input !== selected && !input.disabled) {
                    input.checked = false;
                }
            });
        }

        const label = selected?.dataset.promotionLabel || copy.noPromotion;
        const preview = form.querySelector('[data-preview-promotion]');
        const final = form.querySelector('[data-final-promotion]');
        if (preview) preview.textContent = label;
        if (final) final.textContent = label;

        const hasPromotion = Boolean(selected);
        const icon = form.querySelector('[data-row-icon-promotion]');
        if (icon) icon.className = hasPromotion ? 'bi bi-check-lg' : 'bi bi-dash-lg';
        setRowDone('promotion', hasPromotion, true);
    };

    const periodSummary = () => {
        const from = enabledFromInput?.value || '';
        const until = enabledUntilInput?.value || '';

        if (from && until) {
            return interpolate(copy.periodRange, {'__FROM__': from, '__UNTIL__': until});
        }

        if (from) {
            return interpolate(copy.periodFrom, {'__FROM__': from});
        }

        if (until) {
            return interpolate(copy.periodUntil, {'__UNTIL__': until});
        }

        return copy.periodAlways;
    };

    const syncPreview = () => {
        const name = nameInput?.value?.trim() || '';
        const trigger = selectedTrigger();
        const triggerDefinition = activeTriggerDefinition();
        const parameterSummary = buildParameterSummary();

        const previewName = form.querySelector('[data-preview-name]');
        const previewNameVal = form.querySelector('[data-preview-name-val]');
        const finalName = form.querySelector('[data-final-name]');
        if (previewName) previewName.textContent = name || copy.namePlaceholder;
        if (previewNameVal) previewNameVal.textContent = name || copy.toChoose;
        if (finalName) finalName.textContent = name || copy.toChoose;
        const nameIcon = form.querySelector('[data-row-icon-name]');
        if (nameIcon) nameIcon.className = name ? 'bi bi-check-lg' : 'bi bi-circle';
        setRowDone('name', Boolean(name));

        const triggerLabel = triggerDefinition?.label || copy.toChoose;
        const previewTrigger = form.querySelector('[data-preview-trigger]');
        const finalTrigger = form.querySelector('[data-final-trigger]');
        if (previewTrigger) previewTrigger.textContent = triggerLabel;
        if (finalTrigger) finalTrigger.textContent = triggerLabel;
        const triggerIcon = form.querySelector('[data-row-icon-trigger]');
        if (triggerIcon) triggerIcon.className = trigger ? 'bi bi-check-lg' : 'bi bi-circle';
        setRowDone('trigger', Boolean(trigger));

        const previewParameters = form.querySelector('[data-preview-parameters]');
        const finalParameters = form.querySelector('[data-final-parameters]');
        if (previewParameters) previewParameters.textContent = parameterSummary;
        if (finalParameters) finalParameters.textContent = parameterSummary;
        const parametersIcon = form.querySelector('[data-row-icon-parameters]');
        if (parametersIcon) parametersIcon.className = trigger ? 'bi bi-check-lg' : 'bi bi-circle';
        setRowDone('parameters', Boolean(trigger));

        const modelLabel = mailModelSelect?.value
            ? (selectedOption(mailModelSelect)?.dataset.previewLabel || selectedOption(mailModelSelect)?.textContent?.trim() || copy.noModel)
            : copy.noModel;
        const previewModel = form.querySelector('[data-preview-model]');
        const finalModel = form.querySelector('[data-final-model]');
        if (previewModel) previewModel.textContent = modelLabel;
        if (finalModel) finalModel.textContent = modelLabel;
        const modelIcon = form.querySelector('[data-row-icon-model]');
        if (modelIcon) modelIcon.className = mailModelSelect?.value ? 'bi bi-check-lg' : 'bi bi-circle';
        setRowDone('model', Boolean(mailModelSelect?.value));

        const cooldownValue = cooldownInput?.value?.trim() || '';
        const cooldownLabel = cooldownValue !== ''
            ? interpolate(copy.daysCount, {'__COUNT__': cooldownValue})
            : copy.noCooldown;
        const previewCooldown = form.querySelector('[data-preview-cooldown]');
        const finalCooldown = form.querySelector('[data-final-cooldown]');
        if (previewCooldown) previewCooldown.textContent = cooldownLabel;
        if (finalCooldown) finalCooldown.textContent = cooldownLabel;
        const cooldownIcon = form.querySelector('[data-row-icon-cooldown]');
        if (cooldownIcon) cooldownIcon.className = cooldownValue !== '' ? 'bi bi-check-lg' : 'bi bi-dash-lg';
        setRowDone('cooldown', cooldownValue !== '', true);

        const finalPeriod = form.querySelector('[data-final-period]');
        if (finalPeriod) finalPeriod.textContent = periodSummary();

        syncPromotions();
    };

    const renderStepBar = (step) => {
        form.querySelectorAll('[data-wiz-panel]').forEach((panel) => {
            panel.hidden = Number(panel.dataset.wizPanel) !== step;
        });

        form.querySelectorAll('[data-step-dot]').forEach((dot) => {
            const stepNumber = Number(dot.dataset.stepDot);
            dot.classList.toggle('is-active', stepNumber === step);
            dot.classList.toggle('is-done', stepNumber < step);

            const num = dot.querySelector('.promo-wiz__dot-num');
            if (num) {
                num.innerHTML = stepNumber < step ? '<i class="bi bi-check-lg"></i>' : String(stepNumber);
            }
        });

        form.querySelectorAll('[data-step-line]').forEach((line) => {
            line.classList.toggle('is-done', Number(line.dataset.stepLine) < step);
        });

        form.querySelectorAll('[data-nav-step-indicator]').forEach((el) => {
            el.textContent = `${step}/${totalSteps}`;
        });

        setHidden(btnPrev, step === 1);
        setHidden(btnNext, step === totalSteps);
        setHidden(btnSubmit, step !== totalSteps);
    };

    nameInput?.addEventListener('input', syncPreview);
    triggerRadios.forEach((radio) => {
        radio.addEventListener('change', () => {
            if (!radio.checked) return;
            syncTriggerPanels();
            syncPreview();
        });
    });
    form.querySelectorAll('[data-metadata-key]').forEach((input) => input.addEventListener('input', syncPreview));
    form.querySelectorAll('[data-metadata-key]').forEach((input) => input.addEventListener('change', syncPreview));
    promotionInputs.forEach((input) => input.addEventListener('change', () => {
        if (input.checked) {
            promotionInputs.forEach((other) => {
                if (other !== input && !other.disabled) {
                    other.checked = false;
                }
            });
        }
        syncPromotions();
    }));
    mailModelSelect?.addEventListener('change', syncPreview);
    cooldownInput?.addEventListener('input', syncPreview);
    enabledFromInput?.addEventListener('change', syncPreview);
    enabledUntilInput?.addEventListener('change', syncPreview);

    btnNext.forEach((btn) => btn.addEventListener('click', () => {
        if (currentStep >= totalSteps) return;
        currentStep++;
        renderStepBar(currentStep);
    }));

    btnPrev.forEach((btn) => btn.addEventListener('click', () => {
        if (currentStep <= 1) return;
        currentStep--;
        renderStepBar(currentStep);
    }));

    form.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;
        if (event.target?.tagName?.toLowerCase() === 'textarea') return;
        if (currentStep < totalSteps) {
            event.preventDefault();
            currentStep++;
            renderStepBar(currentStep);
            return;
        }
        if (event.target?.type !== 'submit') {
            event.preventDefault();
        }
    });

    syncTriggerPanels();
    syncPreview();
    renderStepBar(currentStep);
});
</script>
