@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $selectedPromotionIds = collect(old('promotions', $campaign->exists ? $campaign->promotions->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $primaryActionLabel = $method === 'POST'
        ? __('admin.marketing.campaigns.create_and_schedule')
        : __('admin.marketing.campaigns.save_and_schedule');
    $cancelUrl = $campaign->exists ? route('admin.campaigns.show', $campaign) : route('admin.campaigns.index');
    $selectedScheduleWindow = old('schedule_window', data_get($campaign->metadata, 'schedule_window', 'next_available'));
    $legacySegmentMap = [
        'inactive_customers' => 'at_risk_customers',
        'high_spending_customers' => 'high_value_customers',
    ];
    $selectedSegment = old('segment', $campaign->segment ?: 'all');
    $selectedSegment = $legacySegmentMap[$selectedSegment] ?? $selectedSegment;
    $selectedMailModelId = (string) old('model_id', $campaign->model_id);
    $selectedCampaignType = \App\Models\Campaign::normalizeCampaignType(
        old('campaign_type', $campaign->campaign_type ?? \App\Models\Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING)
    );
    $campaignTypeOptions = $campaignTypeOptions ?? \App\Models\Campaign::campaignTypeOptions();
    $campaignTypeIcons = [
        \App\Models\Campaign::CAMPAIGN_TYPE_SOFT_MARKETING => 'bi-envelope-heart-fill',
        \App\Models\Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING => 'bi-shield-check',
        \App\Models\Campaign::CAMPAIGN_TYPE_PROFILING => 'bi-diagram-3-fill',
    ];
    $campaignTypeDescriptions = [
        \App\Models\Campaign::CAMPAIGN_TYPE_SOFT_MARKETING => __('admin.marketing.campaigns.soft_marketing_description'),
        \App\Models\Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING => __('admin.marketing.campaigns.explicit_marketing_description'),
        \App\Models\Campaign::CAMPAIGN_TYPE_PROFILING => __('admin.marketing.campaigns.profiling_description'),
    ];
    $selectedCampaignTypeLabel = $campaignTypeOptions[$selectedCampaignType] ?? __('admin.marketing.campaigns.consent_explicit');
    $baseSegmentOptions = [
        'all' => __('admin.marketing.campaigns.base_segment_all'),
        'reservations' => __('admin.marketing.campaigns.base_segment_reservations'),
        'orders' => __('admin.marketing.campaigns.base_segment_orders'),
        'both' => __('admin.marketing.campaigns.base_segment_both'),
    ];
    $advancedSegmentOptions = $segments ?? [];
    $visibleSegmentOptions = $selectedCampaignType === \App\Models\Campaign::CAMPAIGN_TYPE_PROFILING
        ? $advancedSegmentOptions
        : $baseSegmentOptions;
    if (! array_key_exists($selectedSegment, $visibleSegmentOptions)) {
        $selectedSegment = array_key_first($visibleSegmentOptions) ?? '';
    }
    $segmentPreviewLabels = array_replace($advancedSegmentOptions, $baseSegmentOptions);
    $selectedConsentBasis = \App\Models\Campaign::consentBasisForCampaignType($selectedCampaignType);
    $consentBasisPreviewLabels = [
        \App\Models\Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING => __('admin.marketing.campaigns.consent_explicit'),
        \App\Models\Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING => __('admin.marketing.campaigns.consent_soft'),
        \App\Models\Campaign::CONSENT_BASIS_WHATSAPP_MARKETING => __('admin.marketing.campaigns.consent_whatsapp_inactive'),
    ];
    $selectedConsentBasisLabel = $consentBasisPreviewLabels[$selectedConsentBasis] ?? __('admin.marketing.campaigns.consent_explicit');
    $audiencePreviewUrl = $audiencePreviewUrl ?? route('admin.campaigns.audience-preview');
    $selectedScheduleWindow = array_key_exists($selectedScheduleWindow, $scheduleWindows)
        ? $selectedScheduleWindow
        : 'next_available';
    $previewMailModel = collect($mailModels)->first(fn ($mailModel) => (string) $mailModel->id === $selectedMailModelId) ?: $campaign->model;
    $previewPromotions = collect($promotions)
        ->filter(fn ($promotion) => in_array((string) $promotion->id, $selectedPromotionIds, true))
        ->values();
    $previewScheduleWindowLabel = $scheduleWindows[$selectedScheduleWindow] ?? __('admin.marketing.campaigns.next_available_window');
    $selectedScheduledAtValue = old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\TH:i'));
    $formatPreviewDate = function ($value) {
        if (! $value) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return $value;
        }
    };
    $previewScheduledAtLabel = $formatPreviewDate($selectedScheduledAtValue)
        ?: ($selectedScheduleWindow === 'next_available' ? __('admin.marketing.campaigns.next_available_window') : __('admin.marketing.campaigns.chosen_window'));
    $statusPreviewLabel = $campaign->exists
        ? ($statuses[$campaign->status] ?? $campaign->status)
        : __('admin.marketing.campaigns.new');
    $formatPromotionDiscount = function ($promotion) {
        if ($promotion->type_discount === 'gift') {
            return __('admin.marketing.campaigns.discount_gift');
        }

        if ($promotion->discount === null) {
            return __('admin.marketing.campaigns.discount_to_define');
        }

        $value = number_format((float) $promotion->discount, 2, ',', '.');
        $value = str_ends_with($value, ',00') ? substr($value, 0, -3) : $value;

        return match ($promotion->type_discount) {
            'fixed' => $value . '€',
            'percentage' => $value . '%',
            default => $value,
        };
    };

    // Wizard step detection for server-side error recovery
    $hasErrors = $errors->any();
    if ($hasErrors && $errors->has('name')) {
        $initialStep = 1;
    } elseif ($hasErrors && $errors->hasAny(['scheduled_at', 'schedule_window'])) {
        $initialStep = 2;
    } elseif ($hasErrors && $errors->has('model_id')) {
        $initialStep = 3;
    } elseif ($hasErrors && $errors->hasAny(['campaign_type', 'consent_basis', 'channel'])) {
        $initialStep = 4;
    } elseif ($hasErrors && $errors->hasAny(['segment', 'promotions', 'promotions.*', 'submit_action'])) {
        $initialStep = 5;
    } elseif ($hasErrors) {
        $initialStep = 5;
    } elseif ($campaign->exists) {
        $initialStep = 5;
    } else {
        $initialStep = 1;
    }
@endphp

@include('admin.Marketing.partials.form-style')

<style>
    @media (min-width: 1121px) {
        .campaign-form-shell .marketing-form-sidebar {
            position: sticky;
            top: 90px;
            align-self: start;
        }
    }

    @media (max-width: 1120px) {
        .campaign-form-shell .marketing-form-sidebar {
            position: static;
        }
    }

    .promo-wiz__bar {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 28px;
    }

    .promo-wiz__dot {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        flex: 0 0 auto;
        min-width: 0;
    }

    .promo-wiz__dot-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1.5px solid rgba(216, 221, 232, 0.2);
        background: rgba(9, 3, 51, 0.5);
        color: rgba(216, 221, 232, 0.55);
        font-size: 13px;
        font-weight: 700;
        transition: border-color .2s, background .2s, color .2s;
    }

    .promo-wiz__dot-lbl {
        color: rgba(216, 221, 232, 0.45);
        font-size: var(--fs-100);
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: 0;
        text-align: center;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .promo-wiz__dot.is-active .promo-wiz__dot-num {
        border-color: rgba(14, 183, 146, 0.7);
        background: rgba(14, 183, 146, 0.18);
        color: rgba(142, 246, 219, 0.95);
    }

    .promo-wiz__dot.is-active .promo-wiz__dot-lbl {
        color: rgba(142, 246, 219, 0.85);
    }

    .promo-wiz__dot.is-done .promo-wiz__dot-num {
        border-color: rgba(14, 183, 146, 0.42);
        background: rgba(14, 183, 146, 0.12);
        color: rgba(142, 246, 219, 0.7);
    }

    .promo-wiz__dot.is-done .promo-wiz__dot-lbl {
        color: rgba(142, 246, 219, 0.55);
    }

    .promo-wiz__line {
        flex: 1;
        height: 1.5px;
        min-width: 18px;
        background: rgba(216, 221, 232, 0.1);
        margin: 0 10px;
        margin-bottom: 22px;
        transition: background .2s;
    }

    .promo-wiz__line.is-done {
        background: rgba(14, 183, 146, 0.3);
    }

    .promo-wiz__panel {
        animation: panelIn .22s ease;
    }

    .promo-wiz__panel[hidden] {
        display: none !important;
    }

    @keyframes panelIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .promo-reveal[hidden] {
        display: none !important;
    }

    .promo-reveal {
        animation: panelIn .18s ease;
    }

    .promo-wiz__panel .order-detail__section + .order-detail__section {
        margin-top: 16px;
    }

    @media (max-width: 680px) {
        .promo-wiz__bar {
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .promo-wiz__dot-lbl {
            white-space: normal;
        }
    }

    /* ── Audience block ────────────────────────────────────────── */
    .cpv2-audience {
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.08);
        background: rgba(216, 221, 232, 0.03);
        display: grid;
        gap: 5px;
    }

    .cpv2-audience-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 8px;
        min-width: 0;
    }

    .cpv2-audience-label {
        color: rgba(216, 221, 232, 0.5);
        font-size: var(--fs-100);
        font-weight: 700;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .cpv2-audience-val {
        color: var(--c3);
        font-size: var(--fs-100);
        font-weight: 900;
        text-align: right;
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .cpv2-audience-note {
        margin-top: 4px;
        padding-top: 6px;
        border-top: 1px solid rgba(216, 221, 232, 0.06);
    }

    .cpv2-audience-status {
        display: block;
        color: rgba(216, 221, 232, 0.48);
        font-size: var(--fs-100);
        font-weight: 700;
        line-height: 1.4;
    }

    .cpv2-audience-status.is-warning {
        color: #ffb4a8;
    }

    .cpv2-audience-status.is-error {
        color: #ffb4a8;
        font-weight: 800;
    }

    .campaign-promo-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 1px 6px;
        border-radius: 999px;
        border: 1px solid rgba(255, 180, 168, 0.3);
        background: rgba(255, 180, 168, 0.08);
        color: #ffb4a8;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 3px;
    }

</style>

@if ($errors->any())
    <div class="alert alert-danger">
        {{ __('admin.marketing.campaigns.check_fields') }}
    </div>
@endif

@if (in_array($campaign->status, ['completed', 'sent'], true))
    <div class="alert alert-warning">
        {{ __('admin.marketing.campaigns.completed_warning') }}
    </div>
@endif

<form
    class="creation marketing-form-shell campaign-form-shell mt-4"
    action="{{ $action }}"
    method="POST"
    data-campaign-form
    data-audience-preview-url="{{ $audiencePreviewUrl }}"
    data-campaign-id="{{ $campaign->exists ? $campaign->getKey() : '' }}"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <input type="hidden" name="campaign_type" id="campaign_type" value="{{ $selectedCampaignType }}" data-campaign-type-select>
    <input type="hidden" name="consent_basis" id="consent_basis" value="{{ $selectedConsentBasis }}" data-consent-basis-select>

    <div class="promo-wiz__bar">
        <div class="promo-wiz__dot {{ $initialStep === 1 ? 'is-active' : 'is-done' }}" data-step-dot="1">
            <span class="promo-wiz__dot-num">
                @if ($initialStep > 1) <i class="bi bi-check-lg"></i> @else 1 @endif
            </span>
            <span class="promo-wiz__dot-lbl">{{ __('admin.marketing.campaigns.step_name') }}</span>
        </div>
        <div class="promo-wiz__line {{ $initialStep > 1 ? 'is-done' : '' }}" data-step-line="1"></div>
        <div class="promo-wiz__dot {{ $initialStep === 2 ? 'is-active' : ($initialStep > 2 ? 'is-done' : '') }}" data-step-dot="2">
            <span class="promo-wiz__dot-num">
                @if ($initialStep > 2) <i class="bi bi-check-lg"></i> @else 2 @endif
            </span>
            <span class="promo-wiz__dot-lbl">{{ __('admin.marketing.campaigns.date') }}</span>
        </div>
        <div class="promo-wiz__line {{ $initialStep > 2 ? 'is-done' : '' }}" data-step-line="2"></div>
        <div class="promo-wiz__dot {{ $initialStep === 3 ? 'is-active' : ($initialStep > 3 ? 'is-done' : '') }}" data-step-dot="3">
            <span class="promo-wiz__dot-num">
                @if ($initialStep > 3) <i class="bi bi-check-lg"></i> @else 3 @endif
            </span>
            <span class="promo-wiz__dot-lbl">{{ __('admin.marketing.campaigns.step_model') }}</span>
        </div>
        <div class="promo-wiz__line {{ $initialStep > 3 ? 'is-done' : '' }}" data-step-line="3"></div>
        <div class="promo-wiz__dot {{ $initialStep === 4 ? 'is-active' : ($initialStep > 4 ? 'is-done' : '') }}" data-step-dot="4">
            <span class="promo-wiz__dot-num">
                @if ($initialStep > 4) <i class="bi bi-check-lg"></i> @else 4 @endif
            </span>
            <span class="promo-wiz__dot-lbl">{{ __('admin.marketing.campaigns.step_type') }}</span>
        </div>
        <div class="promo-wiz__line {{ $initialStep > 4 ? 'is-done' : '' }}" data-step-line="4"></div>
        <div class="promo-wiz__dot {{ $initialStep === 5 ? 'is-active' : '' }}" data-step-dot="5">
            <span class="promo-wiz__dot-num">5</span>
            <span class="promo-wiz__dot-lbl">{{ __('admin.marketing.campaigns.segment') }}</span>
        </div>
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
                            {{ __('admin.marketing.campaigns.name_placeholder') }}
                        </h3>
                    </div>

                    <div>
                        <label class="label_c" for="name">
                            <i class="bi bi-type"></i>
                            {{ __('admin.marketing.campaigns.name') }}
                        </label>
                        <p>
                            <input value="{{ old('name', $campaign->name) }}" type="text" name="name" id="name" placeholder="{{ __('admin.marketing.campaigns.name_placeholder') }}" autocomplete="off">
                        </p>
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </section>
            </div>

            <div class="promo-wiz__panel" data-wiz-panel="2" @if ($initialStep !== 2) hidden @endif>
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-calendar-plus"></i>
                            </span>
                            {{ __('admin.marketing.campaigns.schedule_date') }}
                        </h3>
                    </div>

                    <div>
                        <label class="label_c" for="schedule_window">
                            <i class="bi bi-calendar-plus"></i>
                            {{ __('admin.marketing.campaigns.schedule_window') }}
                        </label>
                        <p>
                            <select name="schedule_window" id="schedule_window" data-schedule-window-select data-initial-value="{{ $selectedScheduleWindow }}">
                                @foreach ($scheduleWindows as $value => $label)
                                    <option value="{{ $value }}" data-label="{{ $label }}" @selected($selectedScheduleWindow === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </p>
                        @error('schedule_window') <p class="error">{{ $message }}</p> @enderror
                        @error('scheduled_at') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <p class="menu-dashboard__copy mt-3">{{ __('admin.marketing.campaigns.schedule_note') }}</p>
                </section>
            </div>

            <div class="promo-wiz__panel" data-wiz-panel="3" @if ($initialStep !== 3) hidden @endif>
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </span>
                            {{ __('admin.marketing.campaigns.model_email') }}
                        </h3>
                    </div>

                    <div>
                        <label class="label_c" for="model_id">
                            <i class="bi bi-envelope-fill"></i>
                            {{ __('admin.marketing.campaigns.mail_model') }}
                        </label>
                        <p>
                            <select name="model_id" id="model_id" data-mail-model-select>
                                <option value="" data-preview-label="{{ __('admin.marketing.campaigns.to_select') }}">{{ __('admin.marketing.campaigns.no_model') }}</option>
                                @foreach ($mailModels as $mailModel)
                                    <option value="{{ $mailModel->id }}" data-preview-label="{{ $mailModel->name }}" @selected((string) old('model_id', $campaign->model_id) === (string) $mailModel->id)>
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
                </section>
            </div>

            <div class="promo-wiz__panel" data-wiz-panel="4" @if ($initialStep !== 4) hidden @endif>
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-diagram-3-fill"></i>
                            </span>
                            {{ __('admin.marketing.campaigns.campaign_type') }}
                        </h3>
                    </div>

                    <div class="model-type-picker">
                        @foreach ($campaignTypeOptions as $value => $label)
                            <label class="model-type-option">
                                <input
                                    type="radio"
                                    name="_campaign_type"
                                    value="{{ $value }}"
                                    data-campaign-type-radio
                                    @checked($selectedCampaignType === $value)
                                >
                                <div class="model-type-option__card">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span class="model-type-option__icon">
                                            <i class="bi {{ $campaignTypeIcons[$value] ?? 'bi-envelope-fill' }}"></i>
                                        </span>
                                        <span class="model-type-option__dot"></span>
                                    </div>
                                    <div class="model-type-option__label">
                                        <strong>{{ $label }}</strong>
                                        <small>{{ $campaignTypeDescriptions[$value] ?? '' }}</small>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('campaign_type') <p class="error mt-2">{{ $message }}</p> @enderror
                    @error('consent_basis') <p class="error mt-2">{{ $message }}</p> @enderror
                    @error('channel') <p class="error mt-2">{{ $message }}</p> @enderror
                </section>
            </div>

            <div class="promo-wiz__panel" data-wiz-panel="5" @if ($initialStep !== 5) hidden @endif>
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-people-fill"></i>
                            </span>
                            {{ __('admin.marketing.campaigns.segment_dynamic') }}
                        </h3>
                    </div>

                    <div>
                        <label class="label_c" for="segment">
                            <i class="bi bi-people-fill"></i>
                            {{ __('admin.marketing.campaigns.segment') }}
                        </label>
                        <p>
                            <select name="segment" id="segment" data-segment-select data-initial-value="{{ $selectedSegment }}">
                                @foreach ($visibleSegmentOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedSegment === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </p>
                        @error('segment') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-megaphone-fill"></i>
                            </span>
                            {{ __('admin.marketing.campaigns.linked_promotions') }}
                        </h3>
                    </div>

                    <p class="marketing-form-preview__note" style="margin-bottom: 14px;">
                        <i class="bi bi-info-circle" style="margin-right: 6px; opacity: 0.7;"></i>{{ __('admin.marketing.campaigns.promo_optional_note') }}
                    </p>

                    <label class="label_c">
                        <i class="bi bi-megaphone-fill"></i>
                        {{ __('admin.marketing.campaigns.promotions') }}
                    </label>
                    <div class="model-type-picker" data-campaign-promotion-picker>
                        @forelse ($promotions as $promotion)
                            @php
                                $isSelectable = $promotion->isActive();
                                $promotionChecked = $isSelectable && in_array((string) $promotion->id, $selectedPromotionIds, true);
                                $statusBadgeLabel = match($promotion->status) {
                                    'draft' => __('admin.common.draft'),
                                    default => __('admin.marketing.campaigns.promo_status_inactive'),
                                };
                            @endphp
                            <label class="model-type-option {{ !$isSelectable ? 'model-type-option--disabled' : '' }}" @if(!$isSelectable) aria-disabled="true" @endif>
                                <input
                                    type="checkbox"
                                    name="promotions[]"
                                    value="{{ $promotion->id }}"
                                    data-promotion-checkbox
                                    data-promotion-label="{{ $promotion->name }}"
                                    @checked($promotionChecked)
                                    @if(!$isSelectable) disabled @endif
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
                                        <small>{{ $promotion->slug }} · {{ $formatPromotionDiscount($promotion) }}</small>
                                        @if(!$isSelectable)
                                            <small><span class="campaign-promo-status-badge">{{ $statusBadgeLabel }}</span></small>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="marketing-form-preview__note">
                                {{ __('admin.marketing.campaigns.no_promotions_available') }}
                            </div>
                        @endforelse
                    </div>
                    @error('promotions') <p class="error">{{ $message }}</p> @enderror
                    @error('promotions.*') <p class="error">{{ $message }}</p> @enderror
                </section>
            </div>
        </div>

        <aside class="marketing-form-sidebar campaign-form-sidebar">
            {{-- Nav buttons sopra la preview --}}
            <div class="cpv2-nav">
                <button type="button" class="cpv2-nav-btn" data-wiz-prev @if ($initialStep === 1) hidden @endif>
                    <i class="bi bi-chevron-left"></i>
                    <span>{{ __('admin.common.back') }}</span>
                </button>
                <span class="cpv2-nav-step" data-nav-step-indicator>{{ $initialStep }}/5</span>
                <button type="button" class="cpv2-nav-btn cpv2-nav-btn--primary" data-wiz-next @if ($initialStep === 5) hidden @endif>
                    <span>{{ __('admin.common.next') }}</span>
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>

            {{-- Preview card --}}
            <div class="cpv2-card">
                <div class="cpv2-header">
                    <div class="cpv2-header-icon">
                        <i class="bi bi-envelope-paper-fill"></i>
                    </div>
                    <div>
                        <span class="cpv2-eyebrow">{{ __('admin.marketing.campaigns.preview') }}</span>
                        <strong class="cpv2-name-display" data-preview-campaign-name>{{ old('name', $campaign->name) ?: __('admin.marketing.campaigns.name_placeholder') }}</strong>
                        <div class="cpv2-badges">
                            <span class="cpv2-badge">{{ $statusPreviewLabel }}</span>
                            <span class="cpv2-badge cpv2-badge--muted" data-preview-campaign-type>{{ $selectedCampaignTypeLabel }}</span>
                        </div>
                    </div>
                </div>

                <ul class="cpv2-rows" role="list">
                    <li class="cpv2-row {{ old('name', $campaign->name) ? 'cpv2-row--done' : 'cpv2-row--empty' }}" data-preview-row="name">
                        <span class="cpv2-row-icon"><i class="bi {{ old('name', $campaign->name) ? 'bi-check-lg' : 'bi-circle' }}" data-row-icon-name></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.campaigns.step_name') }}</span>
                        <span class="cpv2-row-val" data-preview-campaign-name-val>{{ old('name', $campaign->name) ?: __('admin.marketing.campaigns.to_define') }}</span>
                    </li>

                    <li class="cpv2-row cpv2-row--done" data-preview-row="schedule">
                        <span class="cpv2-row-icon"><i class="bi bi-check-lg"></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.campaigns.date') }}</span>
                        <span class="cpv2-row-val" data-preview-schedule-window>{{ $previewScheduleWindowLabel }}</span>
                    </li>

                    <li class="cpv2-row {{ $previewMailModel ? 'cpv2-row--done' : 'cpv2-row--empty' }}" data-preview-row="model">
                        <span class="cpv2-row-icon"><i class="bi {{ $previewMailModel ? 'bi-check-lg' : 'bi-circle' }}" data-row-icon-model></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.campaigns.step_model') }}</span>
                        <span class="cpv2-row-val" data-preview-mail-model>{{ $previewMailModel?->name ?? __('admin.marketing.campaigns.to_select') }}</span>
                    </li>

                    <li class="cpv2-row cpv2-row--done" data-preview-row="type">
                        <span class="cpv2-row-icon"><i class="bi bi-check-lg"></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.campaigns.step_type') }}</span>
                        <span class="cpv2-row-val" data-preview-campaign-type-row>{{ $selectedCampaignTypeLabel }}</span>
                    </li>

                    <li class="cpv2-row {{ $selectedSegment ? 'cpv2-row--done' : 'cpv2-row--empty' }}" data-preview-row="segment">
                        <span class="cpv2-row-icon"><i class="bi {{ $selectedSegment ? 'bi-check-lg' : 'bi-circle' }}" data-row-icon-segment></i></span>
                        <span class="cpv2-row-label">{{ __('admin.marketing.campaigns.segment') }}</span>
                        <span class="cpv2-row-val" data-preview-segment-label>{{ $segmentPreviewLabels[$selectedSegment] ?? __('admin.marketing.campaigns.to_select') }}</span>
                    </li>

                    <li class="cpv2-row cpv2-row--optional" data-preview-row="promo">
                        <span class="cpv2-row-icon"><i class="bi bi-dash-lg" data-row-icon-promo></i></span>
                        <span class="cpv2-row-label">{{ __('admin.common.promo') }}</span>
                        <span class="cpv2-row-val" data-preview-promo-summary>{{ $previewPromotions->isNotEmpty() ? $previewPromotions->first()->name : __('admin.marketing.campaigns.no_promotion_selected') }}</span>
                    </li>
                </ul>

                <div class="cpv2-audience">
                    <div class="cpv2-audience-row">
                        <span class="cpv2-audience-label">{{ __('admin.marketing.campaigns.estimated_audience') }}</span>
                        <strong class="cpv2-audience-val" data-preview-audience-count>{{ __('admin.marketing.campaigns.estimated_audience_calculating') }}</strong>
                    </div>
                    <div class="cpv2-audience-row">
                        <span class="cpv2-audience-label">{{ __('admin.marketing.campaigns.available') }}</span>
                        <strong class="cpv2-audience-val" data-preview-availability-count>{{ __('admin.common.loading_data') }}</strong>
                    </div>
                    <div class="cpv2-audience-note">
                        <span class="cpv2-audience-status" data-preview-audience-status>{{ __('admin.marketing.campaigns.dynamic_recalculation') }}</span>
                    </div>
                </div>

                {{-- Elementi nascosti che il JS continua ad aggiornare --}}
                <span style="display:none" data-preview-availability-context>{{ $selectedConsentBasisLabel }}</span>
                <span style="display:none" data-preview-consent-basis>{{ $selectedConsentBasisLabel }}</span>
                <span style="display:none" data-preview-scheduled-at data-initial-label="{{ $previewScheduledAtLabel }}">{{ $previewScheduledAtLabel }}</span>
                <span style="display:none" data-preview-promotion-count>{{ $previewPromotions->count() }}</span>
                <div style="display:none" data-preview-promotion-chips>
                    @foreach ($previewPromotions as $promotion)
                        <span>{{ $promotion->name }}</span>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>

    <div class="marketing-form-actions">
        <button
            class="order-detail__contact marketing-form-action--secondary"
            type="submit"
            name="submit_action"
            value="draft"
            data-wiz-draft
        >
            <i class="bi bi-clock-history"></i>
            <span>{{ __('admin.marketing.campaigns.complete_later') }}</span>
        </button>

        <button
            class="order-detail__contact marketing-form-action--primary"
            type="submit"
            name="submit_action"
            value="activate"
            data-wiz-submit
            @if ($initialStep !== 5) hidden @endif
        >
            <i class="bi bi-check2-circle"></i>
            <span>{{ $primaryActionLabel }}</span>
        </button>
        @error('submit_action') <p class="error">{{ $message }}</p> @enderror
    </div>
</form>

@php
    $campaignFormCopy = [
        'define'                          => __('admin.marketing.campaigns.to_define'),
        'toDefine'                        => __('admin.marketing.campaigns.not_selected'),
        'estimatedAudienceSelectSegment'  => __('admin.marketing.campaigns.estimated_audience_select_segment'),
        'estimatedAudienceCalculating'    => __('admin.marketing.campaigns.estimated_audience_calculating'),
        'estimatedAudienceUnavailable'    => __('admin.marketing.campaigns.estimated_audience_unavailable'),
        'estimatedAudienceReady'          => __('admin.marketing.campaigns.estimated_audience_ready', ['matched' => '__MATCHED__', 'available' => '__AVAILABLE__']),
        'availableCount'                  => __('admin.marketing.campaigns.available_count', ['count' => '__COUNT__']),
        'loading'                         => __('admin.common.loading_data'),
        'error'                           => __('admin.common.error'),
        'chooseSegmentStatus'             => __('admin.marketing.campaigns.choose_segment_status'),
        'dynamicRecalculation'            => __('admin.marketing.campaigns.dynamic_recalculation'),
        'audienceRetry'                   => __('admin.marketing.campaigns.audience_retry'),
        'estimateAligned'                 => __('admin.marketing.campaigns.estimate_aligned'),
        'nextAvailableWindow'             => __('admin.marketing.campaigns.next_available_window'),
        'chosenWindow'                    => __('admin.marketing.campaigns.chosen_window'),
        'noPromotionSelected'             => __('admin.marketing.campaigns.no_promotion_selected'),
        'promotion'                       => __('admin.marketing.campaigns.promotion'),
    ];
@endphp
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-campaign-form]');

        if (!form) {
            return;
        }

        const copy = @json($campaignFormCopy);
        const interpolate = (template, values) => Object.entries(values).reduce(
            (text, [key, value]) => text.split(key).join(value),
            template || ''
        );
        const segmentLabels = @json($segmentPreviewLabels ?? []);
        const baseSegmentOptions = @json($baseSegmentOptions ?? []);
        const advancedSegmentOptions = @json($advancedSegmentOptions ?? []);
        const campaignTypeLabels = @json($campaignTypeOptions ?? []);
        const consentBasisLabels = @json($consentBasisPreviewLabels ?? []);
        const consentBasisByCampaignType = {
            soft_marketing: 'soft_email_marketing',
            explicit_email_marketing: 'explicit_email_marketing',
            profiling: 'explicit_email_marketing',
        };
        const previewFallbacks = {
            name: copy.define,
            campaignType: copy.define,
            consent: copy.define,
            segment: copy.define,
            mailModel: copy.toDefine,
            schedule: copy.define,
        };
        const audiencePreviewUrl = form.dataset.audiencePreviewUrl || '';
        const campaignId = form.dataset.campaignId || '';
        const nameInput = form.querySelector('#name');
        const previewName = form.querySelector('[data-preview-campaign-name]');
        const campaignTypeInput = form.querySelector('[data-campaign-type-select]');
        const campaignTypeRadios = form.querySelectorAll('[data-campaign-type-radio]');
        const campaignTypeLabel = form.querySelector('[data-preview-campaign-type]');
        const consentInput = form.querySelector('[data-consent-basis-select]');
        const consentLabel = form.querySelector('[data-preview-consent-basis]');
        const segmentSelect = form.querySelector('[data-segment-select]');
        const segmentLabel = form.querySelector('[data-preview-segment-label]');
        const availabilityCount = form.querySelector('[data-preview-availability-count]');
        const availabilityContext = form.querySelector('[data-preview-availability-context]');
        const audienceCount = form.querySelector('[data-preview-audience-count]');
        const audienceStatus = form.querySelector('[data-preview-audience-status]');
        const mailModelSelect = form.querySelector('[data-mail-model-select]');
        const mailModelLabel = form.querySelector('[data-preview-mail-model]');
        const scheduleSelect = form.querySelector('[data-schedule-window-select]');
        const scheduleLabel = form.querySelector('[data-preview-schedule-window]');
        const scheduledAtInput = form.querySelector('[data-scheduled-at-input], input[name="scheduled_at"]');
        const scheduledAtLabel = form.querySelector('[data-preview-scheduled-at]');
        const promotionCount = form.querySelector('[data-preview-promotion-count]');
        const promotionChips = form.querySelector('[data-preview-promotion-chips]');
        const promotionInputs = form.querySelectorAll('[data-promotion-checkbox]');
        const promoSummary = form.querySelector('[data-preview-promo-summary]');
        const promoRowIcon = form.querySelector('[data-row-icon-promo]');
        const promoRow = form.querySelector('[data-preview-row="promo"]');
        const btnPrev = form.querySelectorAll('[data-wiz-prev]');
        const btnNext = form.querySelectorAll('[data-wiz-next]');
        const btnDraft = form.querySelector('[data-wiz-draft]');
        const btnSubmit = form.querySelector('[data-wiz-submit]');
        let currentStep = {{ $initialStep }};
        const totalSteps = 5;
        let audiencePreviewRequestId = 0;
        let audiencePreviewController = null;

        const selectedOption = (select) => select?.options[select.selectedIndex] || null;
        const numberFormatter = new Intl.NumberFormat('it-IT');
        const formatNumber = (value) => numberFormatter.format(Number(value || 0));
        const setHidden = (target, hidden) => {
            if (!target) return;
            if (target instanceof NodeList || Array.isArray(target)) {
                target.forEach((el) => { el.hidden = hidden; });
            } else {
                target.hidden = hidden;
            }
        };

        const formatDateTime = (value) => {
            if (!value) {
                return '';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return value;
            }

            return new Intl.DateTimeFormat('it-IT', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }).format(date);
        };

        const setRowDone = (rowAttr, done) => {
            const row = form.querySelector(`[data-preview-row="${rowAttr}"]`);
            if (!row) return;
            row.classList.toggle('cpv2-row--done', done);
            row.classList.toggle('cpv2-row--empty', !done);
        };

        const syncName = () => {
            if (!previewName || !nameInput) {
                return;
            }

            const val = nameInput.value.trim();
            previewName.textContent = val || previewFallbacks.name;

            const nameVal = form.querySelector('[data-preview-campaign-name-val]');
            if (nameVal) nameVal.textContent = val || previewFallbacks.name;

            const icon = form.querySelector('[data-row-icon-name]');
            if (icon) icon.className = val ? 'bi bi-check-lg' : 'bi bi-circle';
            setRowDone('name', Boolean(val));
        };

        const syncConsent = () => {
            if (!consentLabel || !consentInput) {
                return;
            }

            const value = consentInput.value || 'explicit_email_marketing';
            consentLabel.textContent = consentBasisLabels[value] || previewFallbacks.consent;
        };

        const syncSegment = () => {
            const value = segmentSelect?.value || '';

            if (segmentLabel) {
                segmentLabel.textContent = segmentLabels[value] || previewFallbacks.segment;
            }

            const icon = form.querySelector('[data-row-icon-segment]');
            if (icon) icon.className = value ? 'bi bi-check-lg' : 'bi bi-circle';
            setRowDone('segment', Boolean(value));
        };

        const setAudienceStatus = (message, tone = '') => {
            if (!audienceStatus) {
                return;
            }

            audienceStatus.textContent = message || '';
            audienceStatus.classList.toggle('is-warning', tone === 'warning');
            audienceStatus.classList.toggle('is-error', tone === 'error');
        };

        const selectedPromotionIds = () => Array.from(promotionInputs)
            .filter((input) => input.checked)
            .map((input) => input.value)
            .filter(Boolean);

        const setAudiencePreviewState = (state, payload = {}) => {
            if (state === 'empty') {
                if (audienceCount) {
                    audienceCount.textContent = copy.estimatedAudienceSelectSegment;
                }

                if (availabilityCount) {
                    availabilityCount.textContent = interpolate(copy.availableCount, {'__COUNT__': '0'});
                }

                if (availabilityContext) {
                    availabilityContext.textContent = '';
                }

                setAudienceStatus(copy.chooseSegmentStatus, 'warning');
                return;
            }

            if (state === 'loading') {
                if (audienceCount) {
                    audienceCount.textContent = copy.estimatedAudienceCalculating;
                }

                if (availabilityCount) {
                    availabilityCount.textContent = copy.loading;
                }

                setAudienceStatus(copy.dynamicRecalculation);
                return;
            }

            if (state === 'error') {
                if (audienceCount) {
                    audienceCount.textContent = copy.estimatedAudienceUnavailable;
                }

                if (availabilityCount) {
                    availabilityCount.textContent = copy.error;
                }

                setAudienceStatus(copy.audienceRetry, 'error');
                return;
            }

            const matched = Number(payload.matched || 0);
            const available = Number(payload.available || 0);

            if (audienceCount) {
                audienceCount.textContent = interpolate(copy.estimatedAudienceReady, {
                    '__MATCHED__': formatNumber(matched),
                    '__AVAILABLE__': formatNumber(available),
                });
            }

            if (availabilityCount) {
                availabilityCount.textContent = interpolate(copy.availableCount, {'__COUNT__': formatNumber(available)});
            }

            if (availabilityContext) {
                availabilityContext.textContent = payload.available_label || consentBasisLabels[payload.consent_basis] || '';
            }

            if (payload.message) {
                setAudienceStatus(payload.message, matched === 0 || payload.can_assign === false ? 'warning' : '');
                return;
            }

            setAudienceStatus(copy.estimateAligned);
        };

        const requestAudiencePreview = () => {
            const segment = segmentSelect?.value || '';

            if (!segment) {
                audiencePreviewRequestId++;

                if (audiencePreviewController) {
                    audiencePreviewController.abort();
                }

                setAudiencePreviewState('empty');
                return;
            }

            if (!audiencePreviewUrl) {
                setAudiencePreviewState('error');
                return;
            }

            const requestId = ++audiencePreviewRequestId;

            if (audiencePreviewController) {
                audiencePreviewController.abort();
            }

            audiencePreviewController = window.AbortController ? new AbortController() : null;
            const params = new URLSearchParams();
            params.set('segment', segment);
            params.set('campaign_type', campaignTypeInput?.value || 'explicit_email_marketing');
            params.set('consent_basis', consentInput?.value || 'explicit_email_marketing');

            if (campaignId) {
                params.set('campaign_id', campaignId);
            }

            selectedPromotionIds().forEach((promotionId) => {
                params.append('promotions[]', promotionId);
            });

            setAudiencePreviewState('loading');

            fetch(`${audiencePreviewUrl}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: audiencePreviewController?.signal,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Audience preview failed');
                    }

                    return response.json();
                })
                .then((payload) => {
                    if (requestId !== audiencePreviewRequestId) {
                        return;
                    }

                    setAudiencePreviewState('ready', payload || {});
                })
                .catch((error) => {
                    if (error.name === 'AbortError' || requestId !== audiencePreviewRequestId) {
                        return;
                    }

                    setAudiencePreviewState('error');
                });
        };

        const segmentOptionsForCampaignType = (campaignType) => (
            campaignType === 'profiling' ? advancedSegmentOptions : baseSegmentOptions
        );

        const syncSegmentOptions = () => {
            if (!segmentSelect) {
                return;
            }

            const options = segmentOptionsForCampaignType(campaignTypeInput?.value || 'explicit_email_marketing');
            const entries = Object.entries(options);
            const currentValue = segmentSelect.value || segmentSelect.dataset.initialValue || '';
            const nextValue = Object.prototype.hasOwnProperty.call(options, currentValue)
                ? currentValue
                : (entries[0]?.[0] || '');

            segmentSelect.innerHTML = '';

            entries.forEach(([value, label]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                option.selected = value === nextValue;
                segmentSelect.appendChild(option);
            });

            segmentSelect.value = nextValue;
            syncSegment();
            requestAudiencePreview();
        };

        const syncCampaignType = (campaignType) => {
            const value = campaignTypeLabels[campaignType] ? campaignType : 'explicit_email_marketing';

            if (campaignTypeInput) {
                campaignTypeInput.value = value;
            }

            campaignTypeRadios.forEach((radio) => {
                radio.checked = radio.value === value;
            });

            const typeText = campaignTypeLabels[value] || previewFallbacks.campaignType;
            if (campaignTypeLabel) {
                campaignTypeLabel.textContent = typeText;
            }
            const campaignTypeRowLabel = form.querySelector('[data-preview-campaign-type-row]');
            if (campaignTypeRowLabel) {
                campaignTypeRowLabel.textContent = typeText;
            }

            if (consentInput) {
                consentInput.value = consentBasisByCampaignType[value] || 'explicit_email_marketing';
            }

            syncConsent();
            syncSegmentOptions();
        };

        const syncMailModel = () => {
            if (!mailModelLabel || !mailModelSelect) {
                return;
            }

            const option = selectedOption(mailModelSelect);
            const label = mailModelSelect.value
                ? (option?.dataset.previewLabel || option?.textContent?.trim() || previewFallbacks.mailModel)
                : previewFallbacks.mailModel;
            mailModelLabel.textContent = label;

            const icon = form.querySelector('[data-row-icon-model]');
            if (icon) icon.className = mailModelSelect.value ? 'bi bi-check-lg' : 'bi bi-circle';
            setRowDone('model', Boolean(mailModelSelect.value));
        };

        const syncSchedule = () => {
            if (scheduleLabel && scheduleSelect) {
                const option = selectedOption(scheduleSelect);
                scheduleLabel.textContent = option?.dataset.label || option?.textContent?.trim() || previewFallbacks.schedule;
            }

            if (!scheduledAtLabel) {
                return;
            }

            if (!scheduledAtInput) {
                scheduledAtLabel.textContent = scheduleSelect?.value === scheduleSelect?.dataset.initialValue
                    ? (scheduledAtLabel.dataset.initialLabel || scheduledAtLabel.textContent)
                    : (scheduleSelect?.value === 'next_available' ? copy.nextAvailableWindow : (scheduleSelect?.value ? copy.chosenWindow : previewFallbacks.schedule));
                return;
            }

            const formattedDate = formatDateTime(scheduledAtInput?.value || '');

            if (formattedDate) {
                scheduledAtLabel.textContent = formattedDate;
                return;
            }

            scheduledAtLabel.textContent = scheduleSelect?.value === 'next_available'
                ? copy.nextAvailableWindow
                : (scheduleSelect?.value ? copy.chosenWindow : previewFallbacks.schedule);
        };

        const syncPromotions = () => {
            const selected = Array.from(promotionInputs).filter((input) => input.checked);

            if (promotionCount) {
                promotionCount.textContent = selected.length;
            }

            if (promotionChips) {
                promotionChips.innerHTML = '';
                if (selected.length === 0) {
                    const chip = document.createElement('span');
                    chip.textContent = copy.noPromotionSelected;
                    promotionChips.appendChild(chip);
                } else {
                    selected.slice(0, 1).forEach((input) => {
                        const chip = document.createElement('span');
                        chip.textContent = input.dataset.promotionLabel || copy.promotion;
                        promotionChips.appendChild(chip);
                    });
                }
            }

            if (promoSummary) {
                promoSummary.textContent = selected.length > 0
                    ? (selected[0].dataset.promotionLabel || copy.promotion)
                    : copy.noPromotionSelected;
            }

            const hasPromo = selected.length > 0;
            if (promoRowIcon) promoRowIcon.className = hasPromo ? 'bi bi-check-lg' : 'bi bi-dash-lg';
            if (promoRow) {
                promoRow.classList.toggle('cpv2-row--done', hasPromo);
                promoRow.classList.toggle('cpv2-row--optional', !hasPromo);
            }
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

        const syncCampaignPreview = () => {
            syncName();
            syncConsent();
            syncSegment();
            requestAudiencePreview();
            syncMailModel();
            syncSchedule();
            syncPromotions();
        };

        nameInput?.addEventListener('input', syncName);
        campaignTypeRadios.forEach((radio) => {
            radio.addEventListener('change', () => {
                if (radio.checked) {
                    syncCampaignType(radio.value);
                }
            });
        });
        segmentSelect?.addEventListener('change', () => {
            syncSegment();
            requestAudiencePreview();
        });
        mailModelSelect?.addEventListener('change', syncMailModel);
        scheduleSelect?.addEventListener('change', syncSchedule);
        scheduledAtInput?.addEventListener('input', syncSchedule);
        promotionInputs.forEach((input) => {
            input.addEventListener('change', () => {
                if (input.checked) {
                    promotionInputs.forEach((other) => {
                        if (other !== input && !other.disabled) {
                            other.checked = false;
                        }
                    });
                }
                syncPromotions();
                requestAudiencePreview();
            });
        });

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

        form.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            if (e.target?.tagName?.toLowerCase() === 'textarea') return;
            if (currentStep < totalSteps) {
                e.preventDefault();
                currentStep++;
                renderStepBar(currentStep);
                return;
            }
            if (e.target?.type !== 'submit') {
                e.preventDefault();
            }
        });

        syncCampaignType(campaignTypeInput?.value || 'explicit_email_marketing');
        syncCampaignPreview();
        renderStepBar(currentStep);
    });
</script>
