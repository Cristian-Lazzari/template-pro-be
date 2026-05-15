@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $selectedPromotionIds = collect(old('promotions', $campaign->exists ? $campaign->promotions->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $primaryActionLabel = $method === 'POST' ? 'Crea e programma' : 'Salva e programma';
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
        \App\Models\Campaign::CAMPAIGN_TYPE_SOFT_MARKETING => 'Comunicazione email soft opt-in.',
        \App\Models\Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING => 'Invio email con consenso esplicito.',
        \App\Models\Campaign::CAMPAIGN_TYPE_PROFILING => 'Audience CRM profilata e segmenti avanzati.',
    ];
    $selectedCampaignTypeLabel = $campaignTypeOptions[$selectedCampaignType] ?? 'Email marketing con consenso esplicito';
    $baseSegmentOptions = [
        'reservations' => 'Prenotazioni',
        'orders' => 'Ordini',
        'both' => 'Prenotazioni e ordini',
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
        \App\Models\Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING => 'Email marketing con consenso esplicito',
        \App\Models\Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING => 'Email soft opt-in',
        \App\Models\Campaign::CONSENT_BASIS_WHATSAPP_MARKETING => 'WhatsApp marketing, non ancora attivo',
    ];
    $selectedConsentBasisLabel = $consentBasisPreviewLabels[$selectedConsentBasis] ?? 'Email marketing con consenso esplicito';
    $audienceAvailability = $audienceAvailability ?? [];
    $audienceMatrix = $audienceMatrix ?? [];
    $defaultAudienceAvailability = [
        'eligible' => 0,
        'total' => 0,
        'label' => $consentBasisPreviewLabels[$selectedConsentBasis] ?? 'Email marketing esplicito',
        'total_label' => $selectedConsentBasis === \App\Models\Campaign::CONSENT_BASIS_WHATSAPP_MARKETING
            ? 'Totale: clienti con telefono'
            : 'Totale: clienti con email valida',
    ];
    $selectedAudienceAvailability = array_merge(
        $defaultAudienceAvailability,
        $audienceAvailability[$selectedConsentBasis] ?? []
    );
    $selectedAudienceEstimate = (int) data_get($audienceMatrix, $selectedConsentBasis . '.' . $selectedSegment, 0);
    $formatAudienceNumber = fn ($value) => number_format((int) $value, 0, ',', '.');
    $selectedScheduleWindow = array_key_exists($selectedScheduleWindow, $scheduleWindows)
        ? $selectedScheduleWindow
        : 'next_available';
    $previewMailModel = collect($mailModels)->first(fn ($mailModel) => (string) $mailModel->id === $selectedMailModelId) ?: $campaign->model;
    $previewPromotions = collect($promotions)
        ->filter(fn ($promotion) => in_array((string) $promotion->id, $selectedPromotionIds, true))
        ->values();
    $previewScheduleWindowLabel = $scheduleWindows[$selectedScheduleWindow] ?? 'Prima finestra disponibile';
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
        ?: ($selectedScheduleWindow === 'next_available' ? 'Prima finestra disponibile' : 'Definita dalla finestra scelta');
    $statusPreviewLabel = $campaign->exists
        ? ($statuses[$campaign->status] ?? $campaign->status)
        : 'Nuova campagna';
    $formatPromotionDiscount = function ($promotion) {
        if ($promotion->type_discount === 'gift') {
            return 'Regalo';
        }

        if ($promotion->discount === null) {
            return 'Sconto da definire';
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

    .promo-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
    }

    .promo-card {
        position: relative;
        min-width: 0;
        cursor: pointer;
    }

    .promo-card__radio {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 1px;
        height: 1px;
    }

    .promo-card__face {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        height: 100%;
        padding: 16px 12px;
        border-radius: 10px;
        border: 1.5px solid rgba(216, 221, 232, 0.1);
        background: rgba(216, 221, 232, 0.04);
        color: var(--c3);
        text-align: center;
        transition: border-color .15s, background .15s, transform .15s;
    }

    .promo-card__face:hover {
        border-color: rgba(14, 183, 146, 0.3);
        background: rgba(14, 183, 146, 0.07);
        transform: translateY(-1px);
    }

    .promo-card__radio:checked + .promo-card__face {
        border-color: rgba(14, 183, 146, 0.5);
        background: linear-gradient(135deg, rgba(14, 183, 146, 0.18), rgba(216, 221, 232, 0.04));
    }

    .promo-card__radio:focus-visible + .promo-card__face {
        outline: 2px solid rgba(142, 246, 219, 0.7);
        outline-offset: 3px;
    }

    .promo-card__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.45);
        color: rgba(142, 246, 219, 0.88);
        font-size: 17px;
        font-weight: 700;
        flex: 0 0 auto;
    }

    .promo-card__face strong {
        display: block;
        color: var(--c3);
        font-size: var(--fs-300);
        line-height: 1.25;
    }

    .promo-card__face small {
        color: rgba(216, 221, 232, 0.66);
        font-size: var(--fs-100);
        font-weight: 700;
        line-height: 1.35;
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

        .campaign-preview-metrics {
            grid-template-columns: 1fr;
        }
    }

    .campaign-preview-panel {
        display: grid;
        gap: 14px;
        padding: 14px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background:
            linear-gradient(180deg, rgba(216, 221, 232, 0.07), rgba(9, 3, 51, 0.16)),
            rgba(9, 3, 51, 0.28);
        box-shadow: inset 0 1px 0 rgba(216, 221, 232, 0.08);
    }

    .campaign-preview-hero {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 12px;
        align-items: start;
        padding: 14px;
        border-radius: 8px;
        border: 1px solid rgba(14, 183, 146, 0.2);
        background:
            linear-gradient(135deg, rgba(14, 183, 146, 0.16), rgba(98, 166, 255, 0.08)),
            rgba(216, 221, 232, 0.04);
    }

    .campaign-preview-hero__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        border: 1px solid rgba(142, 246, 219, 0.22);
        background: rgba(9, 3, 51, 0.36);
        color: rgba(142, 246, 219, 0.94);
        font-size: 18px;
        flex: 0 0 auto;
    }

    .campaign-preview-eyebrow,
    .campaign-preview-label {
        color: rgba(216, 221, 232, 0.58);
        font-size: var(--fs-100);
        font-weight: 800;
        line-height: 1.3;
        text-transform: uppercase;
        overflow-wrap: anywhere;
    }

    .campaign-preview-title {
        display: block;
        margin-top: 3px;
        color: var(--c3);
        font-size: clamp(1.05rem, 1.8vw, 1.35rem);
        font-weight: 900;
        line-height: 1.18;
        overflow-wrap: anywhere;
    }

    .campaign-preview-status-row {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        align-items: center;
        margin-top: 10px;
    }

    .campaign-preview-badge {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        max-width: 100%;
        padding: 6px 9px;
        border-radius: 999px;
        border: 1px solid rgba(14, 183, 146, 0.28);
        background: rgba(14, 183, 146, 0.12);
        color: rgba(142, 246, 219, 0.96) !important;
        font-size: var(--fs-100);
        font-weight: 900;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .campaign-preview-badge--muted {
        border-color: rgba(98, 166, 255, 0.24);
        background: rgba(98, 166, 255, 0.1);
        color: rgba(216, 221, 232, 0.92) !important;
    }

    .campaign-preview-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .campaign-preview-metric {
        display: grid;
        gap: 4px;
        min-width: 0;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.1);
        background: rgba(216, 221, 232, 0.045);
    }

    .campaign-preview-metric strong {
        color: var(--c3);
        font-size: var(--fs-500);
        font-weight: 900;
        line-height: 1.1;
        overflow-wrap: anywhere;
    }

    .campaign-preview-metric small {
        color: rgba(216, 221, 232, 0.62);
        font-size: var(--fs-100);
        font-weight: 700;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .campaign-preview-grid {
        display: grid;
        gap: 10px;
    }

    .campaign-preview-card {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 10px;
        align-items: start;
        min-width: 0;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.1);
        background: rgba(9, 3, 51, 0.26);
    }

    .campaign-preview-card__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.055);
        color: rgba(142, 246, 219, 0.86);
        flex: 0 0 auto;
    }

    .campaign-preview-card strong {
        display: block;
        margin-top: 3px;
        color: var(--c3);
        font-size: var(--fs-300);
        font-weight: 850;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .campaign-preview-card small {
        display: block;
        margin-top: 3px;
        color: rgba(216, 221, 232, 0.6);
        font-size: var(--fs-100);
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .campaign-preview-warning {
        color: #ffb4a8 !important;
        font-weight: 800;
    }

    .campaign-preview-warning.is-hidden {
        display: none;
    }

    .campaign-preview-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 8px;
    }

    .campaign-preview-chips span {
        display: inline-flex;
        max-width: 100%;
        padding: 6px 8px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.38);
        color: rgba(216, 221, 232, 0.84);
        font-size: var(--fs-100);
        font-weight: 800;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }
</style>

@if ($errors->any())
    <div class="alert alert-danger">
        Controlla i campi evidenziati prima di salvare.
    </div>
@endif

@if (in_array($campaign->status, ['completed', 'sent'], true))
    <div class="alert alert-warning">
        Questa campagna risulta completata: puoi solo archiviarla.
    </div>
@endif

<form class="creation marketing-form-shell campaign-form-shell mt-4" action="{{ $action }}" method="POST" data-campaign-form>
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
            <span class="promo-wiz__dot-lbl">Nome</span>
        </div>
        <div class="promo-wiz__line {{ $initialStep > 1 ? 'is-done' : '' }}" data-step-line="1"></div>
        <div class="promo-wiz__dot {{ $initialStep === 2 ? 'is-active' : ($initialStep > 2 ? 'is-done' : '') }}" data-step-dot="2">
            <span class="promo-wiz__dot-num">
                @if ($initialStep > 2) <i class="bi bi-check-lg"></i> @else 2 @endif
            </span>
            <span class="promo-wiz__dot-lbl">Data</span>
        </div>
        <div class="promo-wiz__line {{ $initialStep > 2 ? 'is-done' : '' }}" data-step-line="2"></div>
        <div class="promo-wiz__dot {{ $initialStep === 3 ? 'is-active' : ($initialStep > 3 ? 'is-done' : '') }}" data-step-dot="3">
            <span class="promo-wiz__dot-num">
                @if ($initialStep > 3) <i class="bi bi-check-lg"></i> @else 3 @endif
            </span>
            <span class="promo-wiz__dot-lbl">Modello</span>
        </div>
        <div class="promo-wiz__line {{ $initialStep > 3 ? 'is-done' : '' }}" data-step-line="3"></div>
        <div class="promo-wiz__dot {{ $initialStep === 4 ? 'is-active' : ($initialStep > 4 ? 'is-done' : '') }}" data-step-dot="4">
            <span class="promo-wiz__dot-num">
                @if ($initialStep > 4) <i class="bi bi-check-lg"></i> @else 4 @endif
            </span>
            <span class="promo-wiz__dot-lbl">Tipo</span>
        </div>
        <div class="promo-wiz__line {{ $initialStep > 4 ? 'is-done' : '' }}" data-step-line="4"></div>
        <div class="promo-wiz__dot {{ $initialStep === 5 ? 'is-active' : '' }}" data-step-dot="5">
            <span class="promo-wiz__dot-num">5</span>
            <span class="promo-wiz__dot-lbl">Segmento</span>
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
                            Nome campagna
                        </h3>
                    </div>

                    <div>
                        <label class="label_c" for="name">
                            <i class="bi bi-type"></i>
                            Nome
                        </label>
                        <p>
                            <input value="{{ old('name', $campaign->name) }}" type="text" name="name" id="name" placeholder="Nome campagna" autocomplete="off">
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
                            Data programmazione
                        </h3>
                    </div>

                    <div>
                        <label class="label_c" for="schedule_window">
                            <i class="bi bi-calendar-plus"></i>
                            Finestra di invio
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
                    <p class="menu-dashboard__copy mt-3">Le email partiranno automaticamente nella finestra programmata tramite il runner marketing.</p>
                </section>
            </div>

            <div class="promo-wiz__panel" data-wiz-panel="3" @if ($initialStep !== 3) hidden @endif>
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </span>
                            Modello email
                        </h3>
                    </div>

                    <div>
                        <label class="label_c" for="model_id">
                            <i class="bi bi-envelope-fill"></i>
                            Modello mail
                        </label>
                        <p>
                            <select name="model_id" id="model_id" data-mail-model-select>
                                <option value="" data-preview-label="Da selezionare">Nessun modello</option>
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
                            Tipo campagna
                        </h3>
                    </div>

                    <div class="promo-cards">
                        @foreach ($campaignTypeOptions as $value => $label)
                            <label class="promo-card">
                                <input
                                    class="promo-card__radio"
                                    type="radio"
                                    name="_campaign_type"
                                    value="{{ $value }}"
                                    data-campaign-type-radio
                                    @checked($selectedCampaignType === $value)
                                >
                                <span class="promo-card__face">
                                    <span class="promo-card__icon">
                                        <i class="bi {{ $campaignTypeIcons[$value] ?? 'bi-envelope-fill' }}"></i>
                                    </span>
                                    <strong>{{ $label }}</strong>
                                    <small>{{ $campaignTypeDescriptions[$value] ?? '' }}</small>
                                </span>
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
                            Segmento dinamico
                        </h3>
                    </div>

                    <div>
                        <label class="label_c" for="segment">
                            <i class="bi bi-people-fill"></i>
                            Segmento
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
                            Promozioni collegate
                        </h3>
                    </div>

                    <label class="label_c">
                        <i class="bi bi-megaphone-fill"></i>
                        Promozioni
                    </label>
                    <div class="campaign-promotion-picker" data-campaign-promotion-picker>
                        @forelse ($promotions as $promotion)
                            @php
                                $promotionChecked = in_array((string) $promotion->id, $selectedPromotionIds, true);
                            @endphp
                            <label class="campaign-promotion-option">
                                <input
                                    class="campaign-promotion-option__input"
                                    type="checkbox"
                                    name="promotions[]"
                                    value="{{ $promotion->id }}"
                                    data-promotion-checkbox
                                    data-promotion-label="{{ $promotion->name }}"
                                    @checked($promotionChecked)
                                >
                                <span class="campaign-promotion-option__card">
                                    <span class="campaign-promotion-option__icon">
                                        <i class="bi bi-megaphone-fill"></i>
                                    </span>
                                    <span>
                                        <strong>{{ $promotion->name }}</strong>
                                        <small>{{ $promotion->slug }} · {{ $formatPromotionDiscount($promotion) }}</small>
                                    </span>
                                </span>
                            </label>
                        @empty
                            <div class="marketing-form-preview__note">
                                Non ci sono promozioni disponibili da collegare.
                            </div>
                        @endforelse
                    </div>
                    @error('promotions') <p class="error">{{ $message }}</p> @enderror
                    @error('promotions.*') <p class="error">{{ $message }}</p> @enderror
                </section>
            </div>
        </div>

        <aside class="marketing-form-sidebar campaign-form-sidebar">
            <section class="order-detail__section marketing-form-preview">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <i class="bi bi-eye-fill"></i>
                        </span>
                        Riepilogo
                    </h3>
                </div>

                <div class="marketing-form-preview__panel campaign-preview-panel">
                    <div class="campaign-preview-hero">
                        <span class="campaign-preview-hero__icon">
                            <i class="bi bi-envelope-paper-fill"></i>
                        </span>
                        <div>
                            <span class="campaign-preview-eyebrow">Anteprima campagna</span>
                            <strong class="campaign-preview-title" data-preview-campaign-name>{{ old('name', $campaign->name) ?: 'Nome campagna' }}</strong>
                            <div class="campaign-preview-status-row">
                                <strong class="campaign-preview-badge">{{ $statusPreviewLabel }}</strong>
                                <strong class="campaign-preview-badge campaign-preview-badge--muted" data-preview-campaign-type>{{ $selectedCampaignTypeLabel }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="campaign-preview-metrics">
                        <div class="campaign-preview-metric">
                            <span class="campaign-preview-label">Audience</span>
                            <strong data-preview-audience-count>{{ $formatAudienceNumber($selectedAudienceEstimate) }}</strong>
                            <small
                                class="campaign-preview-warning{{ $selectedAudienceEstimate === 0 ? '' : ' is-hidden' }}"
                                data-preview-audience-warning
                            >Nessun contatto disponibile</small>
                        </div>
                        <div class="campaign-preview-metric">
                            <span class="campaign-preview-label">Disponibili</span>
                            <strong data-preview-availability-count>
                                {{ $formatAudienceNumber($selectedAudienceAvailability['eligible'] ?? 0) }} / {{ $formatAudienceNumber($selectedAudienceAvailability['total'] ?? 0) }}
                            </strong>
                            <small data-preview-availability-context>{{ $selectedAudienceAvailability['total_label'] ?? '' }}</small>
                        </div>
                    </div>

                    <div class="campaign-preview-grid">
                        <div class="campaign-preview-card">
                            <span class="campaign-preview-card__icon"><i class="bi bi-calendar2-week-fill"></i></span>
                            <div>
                                <span class="campaign-preview-label">Programmazione</span>
                                <strong data-preview-schedule-window>{{ $previewScheduleWindowLabel }}</strong>
                                <small data-preview-scheduled-at data-initial-label="{{ $previewScheduledAtLabel }}">{{ $previewScheduledAtLabel }}</small>
                            </div>
                        </div>

                        <div class="campaign-preview-card">
                            <span class="campaign-preview-card__icon"><i class="bi bi-envelope-fill"></i></span>
                            <div>
                                <span class="campaign-preview-label">Modello mail</span>
                                <strong data-preview-mail-model>{{ $previewMailModel?->name ?? 'Da selezionare' }}</strong>
                            </div>
                        </div>

                        <div class="campaign-preview-card">
                            <span class="campaign-preview-card__icon"><i class="bi bi-people-fill"></i></span>
                            <div>
                                <span class="campaign-preview-label">Segmento</span>
                                <strong data-preview-segment-label>{{ $segmentPreviewLabels[$selectedSegment] ?? 'Da selezionare' }}</strong>
                                <small data-preview-consent-basis>{{ $selectedConsentBasisLabel }}</small>
                            </div>
                        </div>

                        <div class="campaign-preview-card">
                            <span class="campaign-preview-card__icon"><i class="bi bi-megaphone-fill"></i></span>
                            <div>
                                <span class="campaign-preview-label">Promozioni collegate</span>
                                <strong><span data-preview-promotion-count>{{ $previewPromotions->count() }}</span> selezionate</strong>
                                <div class="campaign-preview-chips" data-preview-promotion-chips>
                                    @forelse ($previewPromotions->take(5) as $promotion)
                                        <span>{{ $promotion->name }}</span>
                                    @empty
                                        <span>Nessuna promozione selezionata</span>
                                    @endforelse
                                    @if ($previewPromotions->count() > 5)
                                        <span>+{{ $previewPromotions->count() - 5 }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <div class="marketing-form-actions">
        <a class="order-detail__contact marketing-form-action--cancel" href="{{ $cancelUrl }}">
            <i class="bi bi-x-lg"></i>
            <span>Annulla</span>
        </a>

        <button
            type="button"
            class="order-detail__contact"
            data-wiz-prev
            @if ($initialStep === 1) hidden @endif
        >
            <i class="bi bi-chevron-left"></i>
            <span>Indietro</span>
        </button>

        <button
            type="button"
            class="order-detail__contact marketing-form-action--primary"
            data-wiz-next
            @if ($initialStep === 5) hidden @endif
        >
            <span>Avanti</span>
            <i class="bi bi-chevron-right"></i>
        </button>

        <button
            class="order-detail__contact marketing-form-action--secondary"
            type="submit"
            name="submit_action"
            value="draft"
            data-wiz-draft
            @if ($initialStep !== 5) hidden @endif
        >
            <i class="bi bi-clock-history"></i>
            <span>Completa più tardi</span>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-campaign-form]');

        if (!form) {
            return;
        }

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
            name: 'Da definire',
            campaignType: 'Da definire',
            consent: 'Da definire',
            segment: 'Da definire',
            mailModel: 'Non selezionato',
            schedule: 'Da definire',
        };
        const audienceAvailability = @json($audienceAvailability ?? []);
        const audienceMatrix = @json($audienceMatrix ?? []);
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
        const audienceWarning = form.querySelector('[data-preview-audience-warning]');
        const mailModelSelect = form.querySelector('[data-mail-model-select]');
        const mailModelLabel = form.querySelector('[data-preview-mail-model]');
        const scheduleSelect = form.querySelector('[data-schedule-window-select]');
        const scheduleLabel = form.querySelector('[data-preview-schedule-window]');
        const scheduledAtInput = form.querySelector('[data-scheduled-at-input], input[name="scheduled_at"]');
        const scheduledAtLabel = form.querySelector('[data-preview-scheduled-at]');
        const promotionCount = form.querySelector('[data-preview-promotion-count]');
        const promotionChips = form.querySelector('[data-preview-promotion-chips]');
        const promotionInputs = form.querySelectorAll('[data-promotion-checkbox]');
        const btnPrev = form.querySelector('[data-wiz-prev]');
        const btnNext = form.querySelector('[data-wiz-next]');
        const btnDraft = form.querySelector('[data-wiz-draft]');
        const btnSubmit = form.querySelector('[data-wiz-submit]');
        let currentStep = {{ $initialStep }};
        const totalSteps = 5;

        const selectedOption = (select) => select?.options[select.selectedIndex] || null;
        const numberFormatter = new Intl.NumberFormat('it-IT');
        const formatNumber = (value) => numberFormatter.format(Number(value || 0));
        const setHidden = (element, hidden) => {
            if (element) {
                element.hidden = hidden;
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

        const syncName = () => {
            if (!previewName || !nameInput) {
                return;
            }

            previewName.textContent = nameInput.value.trim() || previewFallbacks.name;
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
        };

        const syncAudienceState = () => {
            const consentBasis = consentInput?.value || 'explicit_email_marketing';
            const segment = segmentSelect?.value || 'all';
            const availability = audienceAvailability[consentBasis] || {
                eligible: 0,
                total: 0,
                total_label: consentBasis === 'whatsapp_marketing'
                    ? 'Totale: clienti con telefono'
                    : 'Totale: clienti con email valida',
            };
            const estimated = Number(audienceMatrix?.[consentBasis]?.[segment] || 0);

            if (availabilityCount) {
                availabilityCount.textContent = `${formatNumber(availability.eligible)} / ${formatNumber(availability.total)}`;
            }

            if (availabilityContext) {
                availabilityContext.textContent = availability.total_label || '';
            }

            if (!audienceCount) {
                return;
            }

            audienceCount.textContent = formatNumber(estimated);

            if (audienceWarning) {
                audienceWarning.classList.toggle('is-hidden', estimated !== 0);
            }
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
            syncAudienceState();
        };

        const syncCampaignType = (campaignType) => {
            const value = campaignTypeLabels[campaignType] ? campaignType : 'explicit_email_marketing';

            if (campaignTypeInput) {
                campaignTypeInput.value = value;
            }

            campaignTypeRadios.forEach((radio) => {
                radio.checked = radio.value === value;
            });

            if (campaignTypeLabel) {
                campaignTypeLabel.textContent = campaignTypeLabels[value] || previewFallbacks.campaignType;
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
            mailModelLabel.textContent = mailModelSelect.value
                ? (option?.dataset.previewLabel || option?.textContent?.trim() || previewFallbacks.mailModel)
                : previewFallbacks.mailModel;
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
                    : (scheduleSelect?.value === 'next_available' ? 'Prima finestra disponibile' : (scheduleSelect?.value ? 'Definita dalla finestra scelta' : previewFallbacks.schedule));
                return;
            }

            const formattedDate = formatDateTime(scheduledAtInput?.value || '');

            if (formattedDate) {
                scheduledAtLabel.textContent = formattedDate;
                return;
            }

            scheduledAtLabel.textContent = scheduleSelect?.value === 'next_available'
                ? 'Prima finestra disponibile'
                : (scheduleSelect?.value ? 'Definita dalla finestra scelta' : previewFallbacks.schedule);
        };

        const syncPromotions = () => {
            const selected = Array.from(promotionInputs).filter((input) => input.checked);

            if (promotionCount) {
                promotionCount.textContent = selected.length;
            }

            if (!promotionChips) {
                return;
            }

            promotionChips.innerHTML = '';

            if (selected.length === 0) {
                const chip = document.createElement('span');
                chip.textContent = 'Nessuna promozione selezionata';
                promotionChips.appendChild(chip);
                return;
            }

            selected.slice(0, 6).forEach((input) => {
                const chip = document.createElement('span');
                chip.textContent = input.dataset.promotionLabel || 'Promozione';
                promotionChips.appendChild(chip);
            });

            if (selected.length > 6) {
                const chip = document.createElement('span');
                chip.textContent = `+${selected.length - 6}`;
                promotionChips.appendChild(chip);
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

            setHidden(btnPrev, step === 1);
            setHidden(btnNext, step === totalSteps);
            setHidden(btnDraft, step !== totalSteps);
            setHidden(btnSubmit, step !== totalSteps);
        };

        const syncCampaignPreview = () => {
            syncName();
            syncConsent();
            syncSegment();
            syncAudienceState();
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
            syncAudienceState();
        });
        mailModelSelect?.addEventListener('change', syncMailModel);
        scheduleSelect?.addEventListener('change', syncSchedule);
        scheduledAtInput?.addEventListener('input', syncSchedule);
        promotionInputs.forEach((input) => input.addEventListener('change', syncPromotions));
        btnNext?.addEventListener('click', () => {
            if (currentStep >= totalSteps) {
                return;
            }

            currentStep++;
            renderStepBar(currentStep);
        });
        btnPrev?.addEventListener('click', () => {
            if (currentStep <= 1) {
                return;
            }

            currentStep--;
            renderStepBar(currentStep);
        });

        syncCampaignType(campaignTypeInput?.value || 'explicit_email_marketing');
        syncCampaignPreview();
        renderStepBar(currentStep);
    });
</script>
