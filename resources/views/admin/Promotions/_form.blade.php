@php
    $reusableValue = old('metadata.reusable', data_get($promotion->metadata, 'reusable', false));
    $previewPermanent = filter_var(old('permanent', $promotion->permanent), FILTER_VALIDATE_BOOLEAN);
    $scheduleValue = $previewPermanent ? '' : old('schedule_at', $promotion->schedule_at?->format('Y-m-d\TH:i'));
    $expiringValue = $previewPermanent ? '' : old('expiring_at', $promotion->expiring_at?->format('Y-m-d\TH:i'));

    $targetConfigs = [
        'product' => [
            'field'    => 'product_ids',
            'empty'    => 'Nessun prodotto disponibile',
            'icon'     => 'bi-tags-fill',
            'singular' => '1 prodotto',
            'plural'   => 'prodotti',
        ],
        'menu' => [
            'field'    => 'menu_ids',
            'empty'    => 'Nessun menu disponibile',
            'icon'     => 'bi-menu-button-wide-fill',
            'singular' => '1 menu',
            'plural'   => 'menu',
        ],
        'category' => [
            'field'    => 'category_ids',
            'empty'    => 'Nessuna categoria disponibile',
            'icon'     => 'bi-folder-fill',
            'singular' => '1 categoria',
            'plural'   => 'categorie',
        ],
    ];

    $validTargetTypes = array_merge(['generic'], array_keys($targetConfigs));

    $existingTargetIds = [];
    foreach ($targetConfigs as $type => $config) {
        $existingTargetIds[$type] = $promotion->exists
            ? $promotion->targets
                ->where('target_type', $type)
                ->pluck('target_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->unique()
                ->values()
                ->all()
            : [];
    }

    $existingSpecificTargetType = collect($existingTargetIds)->search(fn ($ids) => count($ids) > 0);
    $targetType = old('target_type', $existingSpecificTargetType ?: 'generic');
    $targetType = in_array($targetType, $validTargetTypes, true) ? $targetType : 'generic';

    $selectedTargetIds = [];
    foreach ($targetConfigs as $type => $config) {
        $selectedTargetIds[$type] = collect((array) old($config['field'], $existingTargetIds[$type]))
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    $targetOptionsByType = collect($targetConfigs)
        ->mapWithKeys(fn ($config, $type) => [$type => $specificTargetOptions[$type] ?? []])
        ->all();

    $previewCaseUse = old('case_use', $promotion->case_use);
    $rawPreviewDiscountType = old('type_discount', $promotion->type_discount);
    $previewDiscountType = ($targetType === 'category' && $rawPreviewDiscountType === 'gift')
        ? 'fixed'
        : $rawPreviewDiscountType;

    $primaryActionLabel = $method === 'POST' ? 'Crea e attiva' : 'Salva e attiva';
    $cancelUrl = $promotion->exists ? route('admin.promotions.show', $promotion) : route('admin.promotions.index');

    $isTableCase = $previewCaseUse === 'table';
    $isOrderCase = in_array($previewCaseUse, ['generic', 'take_away', 'delivery'], true);

    // Wizard step detection for server-side error recovery
    $hasErrors = $errors->any();
    if ($hasErrors && $errors->hasAny(['name', 'description', 'cta', 'permanent', 'schedule_at', 'expiring_at', 'metadata.reusable'])) {
        $initialStep = 1;
    } elseif ($hasErrors && $errors->has('case_use')) {
        $initialStep = 2;
    } elseif ($hasErrors) {
        $initialStep = 3;
    } elseif ($promotion->exists) {
        $initialStep = 3;
    } else {
        $initialStep = 1;
    }

    $caseUseLabels = [
        'table'    => 'Prenotazioni',
        'generic'  => 'Ordini',
        'take_away'=> 'Solo asporto',
        'delivery' => 'Solo delivery',
    ];
    $discountTypeLabels = ['percentage' => 'Percentuale', 'fixed' => 'Fisso', 'gift' => 'Omaggio'];

    $targetOptionLabels = collect($targetOptionsByType[$targetType] ?? [])
        ->mapWithKeys(function ($option) {
            $id = explode(':', $option['key'], 2)[1] ?? '';
            return [$id => $option['label']];
        })
        ->all();

    $previewTargetLabels = collect($targetType === 'generic' ? [] : ($selectedTargetIds[$targetType] ?? []))
        ->map(fn ($id) => $targetOptionLabels[$id] ?? $targetType . ' #' . $id)
        ->unique()
        ->values();

    $targetCount = $previewTargetLabels->count();
    $targetSummary = match (true) {
        $targetType === 'generic'  => 'Carrello',
        $targetCount === 1         => $targetConfigs[$targetType]['singular'],
        default                    => $targetCount . ' ' . ($targetConfigs[$targetType]['plural'] ?? ''),
    };
@endphp

@include('admin.Marketing.partials.form-style')

<style>
    /* ── Wizard bar ────────────────────────────────── */
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
        font-size: 11px;
        font-weight: 600;
        color: rgba(216, 221, 232, 0.45);
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
        transition: color .2s;
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
        background: rgba(216, 221, 232, 0.1);
        margin: 0 10px;
        margin-bottom: 22px;
        transition: background .2s;
    }

    .promo-wiz__line.is-done {
        background: rgba(14, 183, 146, 0.3);
    }

    /* ── Panel visibility ────────────────────────── */
    .promo-wiz__panel {
        animation: panelIn .22s ease;
    }

    .promo-wiz__panel[hidden] {
        display: none !important;
    }

    @keyframes panelIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Card grid (case_use / target / discount-type) */
    .promo-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
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
        padding: 16px 12px;
        border-radius: 10px;
        border: 1.5px solid rgba(216, 221, 232, 0.1);
        background: rgba(216, 221, 232, 0.04);
        color: var(--c3);
        text-align: center;
        transition: border-color .15s, background .15s, transform .15s;
        height: 100%;
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
        font-size: var(--fs-200);
        color: var(--c3);
        line-height: 1.25;
    }

    /* ── Toggle toggle (permanente / riusabile) ──── */
    .promo-toggles {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
    }

    .promo-toggle {
        position: relative;
    }

    .promo-toggle__input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 1px;
        height: 1px;
    }

    .promo-toggle__card {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 12px;
        align-items: center;
        padding: 14px 16px;
        border-radius: 10px;
        border: 1.5px solid rgba(216, 221, 232, 0.1);
        background: rgba(216, 221, 232, 0.04);
        color: var(--c3);
        cursor: pointer;
        transition: border-color .15s, background .15s;
    }

    .promo-toggle__card:hover {
        border-color: rgba(14, 183, 146, 0.26);
        background: rgba(14, 183, 146, 0.06);
    }

    .promo-toggle__input:checked + .promo-toggle__card {
        border-color: rgba(14, 183, 146, 0.46);
        background: linear-gradient(135deg, rgba(14, 183, 146, 0.16), rgba(216, 221, 232, 0.04));
    }

    .promo-toggle__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.42);
        color: rgba(142, 246, 219, 0.9);
        flex: 0 0 auto;
    }

    .promo-toggle__card strong {
        display: block;
        font-size: var(--fs-300);
        color: var(--c3);
        line-height: 1.2;
    }

    .promo-toggle__card small {
        display: block;
        margin-top: 3px;
        color: rgba(216, 221, 232, 0.66);
        line-height: 1.4;
    }

    /* ── Target item list ─────────────────────────── */
    .promo-items {
        display: grid;
        gap: 5px;
        max-height: 300px;
        overflow-y: auto;
        padding: 2px;
    }

    .promo-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        border-radius: 9px;
        border: 1.5px solid rgba(216, 221, 232, 0.08);
        background: rgba(9, 3, 51, 0.22);
        cursor: pointer;
        transition: border-color .14s, background .14s;
        position: relative;
    }

    .promo-item input[type="radio"],
    .promo-item input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
        pointer-events: none;
    }

    .promo-item__check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 5px;
        border: 1.5px solid rgba(216, 221, 232, 0.2);
        background: rgba(9, 3, 51, 0.4);
        flex: 0 0 auto;
        color: transparent;
        font-size: 12px;
        transition: border-color .14s, background .14s, color .14s;
    }

    .promo-item__label {
        font-size: var(--fs-300);
        color: var(--c3);
        line-height: 1.35;
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .promo-item:hover {
        border-color: rgba(14, 183, 146, 0.25);
        background: rgba(14, 183, 146, 0.05);
    }

    .promo-item:hover .promo-item__check {
        border-color: rgba(14, 183, 146, 0.4);
    }

    .promo-item:has(input:checked) {
        border-color: rgba(14, 183, 146, 0.5);
        background: linear-gradient(135deg, rgba(14, 183, 146, 0.13), rgba(9, 3, 51, 0.22));
    }

    .promo-item:has(input:checked) .promo-item__check {
        border-color: rgba(14, 183, 146, 0.7);
        background: rgba(14, 183, 146, 0.85);
        color: rgba(9, 3, 51, 0.9);
    }

    .promo-items-empty {
        color: rgba(216, 221, 232, 0.55);
        padding: 10px 0;
    }

    /* ── Reveal animation for sub-sections ─────── */
    .promo-reveal[hidden] {
        display: none !important;
    }

    .promo-reveal {
        animation: panelIn .18s ease;
    }

    /* ── Section spacing ─────────────────────────── */
    .promo-wiz__panel .order-detail__section + .order-detail__section {
        margin-top: 16px;
    }

    .promo-wiz__panel [data-order-td-wrap],
    .promo-wiz__panel [data-order-sconto-wrap],
    .promo-wiz__panel [data-items] {
        margin-top: 16px;
    }
</style>

@if ($errors->any())
    <div class="alert alert-danger mb-3">
        Controlla i campi evidenziati prima di salvare.
    </div>
@endif

<form
    class="creation marketing-form-shell promotion-form-ui mt-4"
    action="{{ $action }}"
    method="POST"
    data-promo-form
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    {{-- Hidden canonical fields (always submitted) --}}
    <input type="hidden" name="case_use"         id="h_case_use"          value="{{ $previewCaseUse ?? '' }}">
    <input type="hidden" name="target_type"       id="h_target_type"       value="{{ $targetType }}">
    <input type="hidden" name="type_discount"     id="h_type_discount"     value="{{ $previewDiscountType ?? '' }}">
    <input type="hidden" name="minimum_pretest"   id="h_minimum_pretest"   value="{{ old('minimum_pretest', $promotion->minimum_pretest) }}">
    <input type="hidden" name="discount"          id="h_discount"          value="{{ $previewDiscountType !== 'gift' ? old('discount', $promotion->discount) : '' }}">

    {{-- Step indicator --}}
    <div class="promo-wiz__bar">
        <div class="promo-wiz__dot {{ $initialStep === 1 ? 'is-active' : 'is-done' }}" data-step-dot="1">
            <span class="promo-wiz__dot-num">
                @if ($initialStep > 1) <i class="bi bi-check-lg"></i> @else 1 @endif
            </span>
            <span class="promo-wiz__dot-lbl">Identità</span>
        </div>
        <div class="promo-wiz__line {{ $initialStep > 1 ? 'is-done' : '' }}" data-step-line="1"></div>
        <div class="promo-wiz__dot {{ $initialStep === 2 ? 'is-active' : ($initialStep > 2 ? 'is-done' : '') }}" data-step-dot="2">
            <span class="promo-wiz__dot-num">
                @if ($initialStep > 2) <i class="bi bi-check-lg"></i> @else 2 @endif
            </span>
            <span class="promo-wiz__dot-lbl">Contesto</span>
        </div>
        <div class="promo-wiz__line {{ $initialStep > 2 ? 'is-done' : '' }}" data-step-line="2"></div>
        <div class="promo-wiz__dot {{ $initialStep === 3 ? 'is-active' : '' }}" data-step-dot="3">
            <span class="promo-wiz__dot-num">3</span>
            <span class="promo-wiz__dot-lbl">Regola</span>
        </div>
    </div>

    <div class="marketing-form-grid">
        <div class="marketing-form-main">

            {{-- ═══════════════════════════════════════════ --}}
            {{-- STEP 1 · Identità                          --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div class="promo-wiz__panel" data-wiz-panel="1" @if ($initialStep !== 1) hidden @endif>

                <section class="order-detail__section">
                    <div class="split">
                        <div>
                            <label class="label_c" for="name">
                                <i class="bi bi-type"></i>
                                Nome
                            </label>
                            <p>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name', $promotion->name) }}"
                                    placeholder="Nome promozione"
                                    autocomplete="off"
                                >
                            </p>
                            @error('name') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label_c" for="cta">
                                <i class="bi bi-cursor-fill"></i>
                                CTA
                            </label>
                            <p>
                                <input
                                    type="text"
                                    name="cta"
                                    id="cta"
                                    value="{{ old('cta', $promotion->cta) }}"
                                    placeholder="Testo call to action"
                                    autocomplete="off"
                                >
                            </p>
                            @error('cta') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="label_c" for="description">
                            <i class="bi bi-text-paragraph"></i>
                            Descrizione
                        </label>
                        <p>
                            <textarea
                                name="description"
                                id="description"
                                rows="3"
                                placeholder="Descrizione interna della promozione (opzionale)"
                                autocomplete="off"
                            >{{ old('description', $promotion->description) }}</textarea>
                        </p>
                        @error('description') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="promo-toggles">
                        {{-- Permanente --}}
                        <div class="promo-toggle">
                            <input type="hidden" name="permanent" value="0">
                            <input
                                class="promo-toggle__input"
                                type="checkbox"
                                name="permanent"
                                id="permanent"
                                value="1"
                                data-permanent
                                @checked(old('permanent', $promotion->permanent))
                            >
                            <label class="promo-toggle__card" for="permanent">
                                <span class="promo-toggle__icon"><i class="bi bi-infinity"></i></span>
                                <span>
                                    <strong>Permanente</strong>
                                    <small>Senza scadenza</small>
                                </span>
                            </label>
                            @error('permanent') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        {{-- Riutilizzabile --}}
                        <div class="promo-toggle">
                            <input type="hidden" name="metadata[reusable]" value="0">
                            <input
                                class="promo-toggle__input"
                                type="checkbox"
                                name="metadata[reusable]"
                                id="metadata_reusable"
                                value="1"
                                @checked(filter_var($reusableValue, FILTER_VALIDATE_BOOLEAN))
                            >
                            <label class="promo-toggle__card" for="metadata_reusable">
                                <span class="promo-toggle__icon"><i class="bi bi-arrow-repeat"></i></span>
                                <span>
                                    <strong>Riutilizzabile</strong>
                                    <small>Più utilizzi per cliente</small>
                                </span>
                            </label>
                            @error('metadata.reusable') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Date range --}}
                    <div class="split mt-3 promo-reveal" data-dates-panel @if ($previewPermanent) hidden @endif>
                        <div>
                            <label class="label_c" for="schedule_at">
                                <i class="bi bi-calendar-plus"></i>
                                Data inizio
                            </label>
                            <p>
                                <input
                                    type="datetime-local"
                                    name="schedule_at"
                                    id="schedule_at"
                                    value="{{ $scheduleValue }}"
                                    @disabled($previewPermanent)
                                >
                            </p>
                            @error('schedule_at') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label_c" for="expiring_at">
                                <i class="bi bi-calendar-x"></i>
                                Data fine
                            </label>
                            <p>
                                <input
                                    type="datetime-local"
                                    name="expiring_at"
                                    id="expiring_at"
                                    value="{{ $expiringValue }}"
                                    @disabled($previewPermanent)
                                >
                            </p>
                            @error('expiring_at') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- STEP 2 · Contesto                          --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div class="promo-wiz__panel" data-wiz-panel="2" @if ($initialStep !== 2) hidden @endif>

                <section class="order-detail__section">
                    <div class="promo-cards" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr))">
                        <label class="promo-card">
                            <input class="promo-card__radio" type="radio" name="_case_use" value="table"     @checked($previewCaseUse === 'table')     data-sync="h_case_use">
                            <span class="promo-card__face">
                                <span class="promo-card__icon"><i class="bi bi-calendar2-check-fill"></i></span>
                                <strong>Prenotazioni</strong>
                            </span>
                        </label>
                        <label class="promo-card">
                            <input class="promo-card__radio" type="radio" name="_case_use" value="generic"   @checked($previewCaseUse === 'generic')   data-sync="h_case_use">
                            <span class="promo-card__face">
                                <span class="promo-card__icon"><i class="bi bi-bag-fill"></i></span>
                                <strong>Ordini</strong>
                            </span>
                        </label>
                        <label class="promo-card">
                            <input class="promo-card__radio" type="radio" name="_case_use" value="take_away" @checked($previewCaseUse === 'take_away') data-sync="h_case_use">
                            <span class="promo-card__face">
                                <span class="promo-card__icon"><i class="bi bi-box2-fill"></i></span>
                                <strong>Solo asporto</strong>
                            </span>
                        </label>
                        <label class="promo-card">
                            <input class="promo-card__radio" type="radio" name="_case_use" value="delivery"  @checked($previewCaseUse === 'delivery')  data-sync="h_case_use">
                            <span class="promo-card__face">
                                <span class="promo-card__icon"><i class="bi bi-bicycle"></i></span>
                                <strong>Solo delivery</strong>
                            </span>
                        </label>
                    </div>
                    @error('case_use') <p class="error mt-2">{{ $message }}</p> @enderror
                </section>

            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- STEP 3 · Regola                            --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div class="promo-wiz__panel" data-wiz-panel="3" @if ($initialStep !== 3) hidden @endif>

                {{-- ── PRENOTAZIONI ── --}}
                <div data-regola="table" @if (!$isTableCase) hidden @endif>

                    <section class="order-detail__section">
                        <div class="split">
                            <div>
                                <label class="label_c" for="dsp_min_table">
                                    <i class="bi bi-people-fill"></i>
                                    Minimo persone
                                </label>
                                <p>
                                    <input
                                        type="number"
                                        step="1"
                                        min="1"
                                        id="dsp_min_table"
                                        name="_min_table"
                                        value="{{ old('minimum_pretest', $promotion->minimum_pretest) }}"
                                        data-sync="h_minimum_pretest"
                                        placeholder="es. 4"
                                    >
                                </p>
                                @error('minimum_pretest') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label_c" for="dsp_discount_table">
                                    <i class="bi bi-cash-coin"></i>
                                    Sconto
                                </label>
                                <p>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="dsp_discount_table"
                                        name="_discount_table"
                                        value="{{ old('discount', $promotion->discount) }}"
                                        data-sync="h_discount"
                                        placeholder="es. 10"
                                    >
                                </p>
                                @error('discount') <p class="error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="order-detail__section">
                        <div class="label_c mb-2">
                            <i class="bi bi-percent"></i>
                            Tipo sconto
                        </div>
                        <div class="promo-cards" style="grid-template-columns: repeat(2, 1fr); max-width: 340px">
                            <label class="promo-card">
                                <input
                                    class="promo-card__radio"
                                    type="radio"
                                    name="_td_table"
                                    value="percentage"
                                    @checked($isTableCase && $previewDiscountType === 'percentage')
                                    data-sync="h_type_discount"
                                >
                                <span class="promo-card__face">
                                    <span class="promo-card__icon">%</span>
                                    <strong>Percentuale</strong>
                                </span>
                            </label>
                            <label class="promo-card">
                                <input
                                    class="promo-card__radio"
                                    type="radio"
                                    name="_td_table"
                                    value="fixed"
                                    @checked($isTableCase && $previewDiscountType === 'fixed')
                                    data-sync="h_type_discount"
                                >
                                <span class="promo-card__face">
                                    <span class="promo-card__icon"><i class="bi bi-currency-euro"></i></span>
                                    <strong>Fisso</strong>
                                </span>
                            </label>
                        </div>
                        @error('type_discount') <p class="error mt-2">{{ $message }}</p> @enderror
                    </section>

                </div>

                {{-- ── ORDINI / ASPORTO / DELIVERY ── --}}
                <div data-regola="order" @if (!$isOrderCase) hidden @endif>

                    {{-- Minimo carrello --}}
                    <section class="order-detail__section">
                        <div style="max-width: 320px">
                            <label class="label_c" for="dsp_min_order">
                                <i class="bi bi-bag-check"></i>
                                Minimo carrello
                            </label>
                            <p>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    id="dsp_min_order"
                                    name="_min_order"
                                    value="{{ old('minimum_pretest', $promotion->minimum_pretest) }}"
                                    data-sync="h_minimum_pretest"
                                    placeholder="es. 15.00"
                                >
                            </p>
                            @error('minimum_pretest') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    {{-- Target --}}
                    <section class="order-detail__section">
                        <div class="promo-cards" data-target-cards>
                            <label class="promo-card">
                                <input class="promo-card__radio" type="radio" name="_target_type" value="product"  @checked($isOrderCase && $targetType === 'product')  data-sync="h_target_type" data-target-radio>
                                <span class="promo-card__face">
                                    <span class="promo-card__icon"><i class="bi bi-tags-fill"></i></span>
                                    <strong>Prodotti</strong>
                                </span>
                            </label>
                            <label class="promo-card">
                                <input class="promo-card__radio" type="radio" name="_target_type" value="menu"     @checked($isOrderCase && $targetType === 'menu')     data-sync="h_target_type" data-target-radio>
                                <span class="promo-card__face">
                                    <span class="promo-card__icon"><i class="bi bi-menu-button-wide-fill"></i></span>
                                    <strong>Menu</strong>
                                </span>
                            </label>
                            <label class="promo-card">
                                <input class="promo-card__radio" type="radio" name="_target_type" value="category" @checked($isOrderCase && $targetType === 'category') data-sync="h_target_type" data-target-radio>
                                <span class="promo-card__face">
                                    <span class="promo-card__icon"><i class="bi bi-folder-fill"></i></span>
                                    <strong>Categorie</strong>
                                </span>
                            </label>
                            <label class="promo-card">
                                <input class="promo-card__radio" type="radio" name="_target_type" value="generic"  @checked($isOrderCase && $targetType === 'generic')  data-sync="h_target_type" data-target-radio>
                                <span class="promo-card__face">
                                    <span class="promo-card__icon"><i class="bi bi-cart-fill"></i></span>
                                    <strong>Carrello</strong>
                                </span>
                            </label>
                        </div>
                        @error('target_type') <p class="error mt-2">{{ $message }}</p> @enderror
                    </section>

                    {{-- Tipo di sconto (appare dopo target) --}}
                    <div class="promo-reveal" data-order-td-wrap
                         @if (!$isOrderCase || !$targetType) hidden @endif>
                        <section class="order-detail__section">
                            <div class="label_c mb-2">
                                <i class="bi bi-percent"></i>
                                Tipo di sconto
                            </div>
                            <div class="promo-cards" style="grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); max-width: 420px" data-td-order-cards>
                                <label class="promo-card">
                                    <input class="promo-card__radio" type="radio" name="_td_order" value="percentage" @checked($isOrderCase && $previewDiscountType === 'percentage') data-sync="h_type_discount" data-td-radio>
                                    <span class="promo-card__face">
                                        <span class="promo-card__icon">%</span>
                                        <strong>Percentuale</strong>
                                    </span>
                                </label>
                                <label class="promo-card">
                                    <input class="promo-card__radio" type="radio" name="_td_order" value="fixed" @checked($isOrderCase && $previewDiscountType === 'fixed') data-sync="h_type_discount" data-td-radio>
                                    <span class="promo-card__face">
                                        <span class="promo-card__icon"><i class="bi bi-currency-euro"></i></span>
                                        <strong>Fisso</strong>
                                    </span>
                                </label>
                                {{-- Omaggio: only for product / menu --}}
                                <label class="promo-card promo-reveal" data-gift-card
                                       @if (!$isOrderCase || in_array($targetType, ['category', 'generic'], true)) hidden @endif>
                                    <input class="promo-card__radio" type="radio" name="_td_order" value="gift" @checked($isOrderCase && $previewDiscountType === 'gift') data-sync="h_type_discount" data-td-radio>
                                    <span class="promo-card__face">
                                        <span class="promo-card__icon"><i class="bi bi-gift-fill"></i></span>
                                        <strong>Omaggio</strong>
                                    </span>
                                </label>
                            </div>
                            @error('type_discount') <p class="error mt-2">{{ $message }}</p> @enderror
                        </section>
                    </div>

                    {{-- Sconto (shown for % / €, hidden for omaggio) --}}
                    <div class="promo-reveal" data-order-sconto-wrap
                         @if (!$isOrderCase || !$previewDiscountType || $previewDiscountType === 'gift') hidden @endif>
                        <section class="order-detail__section">
                            <div style="max-width: 320px">
                                <label class="label_c" for="dsp_discount_order">
                                    <i class="bi bi-cash-coin"></i>
                                    Sconto
                                </label>
                                <p>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="dsp_discount_order"
                                        name="_discount_order"
                                        value="{{ $previewDiscountType !== 'gift' ? old('discount', $promotion->discount) : '' }}"
                                        data-sync="h_discount"
                                        placeholder="es. 10"
                                    >
                                </p>
                                @error('discount') <p class="error">{{ $message }}</p> @enderror
                            </div>
                        </section>
                    </div>

                    {{-- Item selector (radio, selezione singola) --}}
                    @foreach ($targetConfigs as $type => $config)
                        @php
                            $options       = $targetOptionsByType[$type] ?? [];
                            $selectedFirst = $selectedTargetIds[$type][0] ?? null;
                        @endphp
                        <div class="promo-reveal" data-items="{{ $type }}"
                             @if (!$isOrderCase || $targetType !== $type) hidden @endif>
                            <section class="order-detail__section">
                                @if (count($options) === 0)
                                    <p class="promo-items-empty">{{ $config['empty'] }}</p>
                                @else
                                    <div class="promo-items">
                                        @foreach ($options as $option)
                                            @php
                                                $itemId  = explode(':', $option['key'], 2)[1] ?? '';
                                                $inputId = 'item_' . $type . '_' . $itemId;
                                            @endphp
                                            <label class="promo-item" for="{{ $inputId }}">
                                                <input
                                                    type="radio"
                                                    name="{{ $config['field'] }}[]"
                                                    id="{{ $inputId }}"
                                                    value="{{ $itemId }}"
                                                    @checked((string) $itemId === (string) $selectedFirst)
                                                >
                                                <span class="promo-item__check"><i class="bi bi-check-lg"></i></span>
                                                <span class="promo-item__label">{{ $option['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                                @error($config['field'])     <p class="error mt-2">{{ $message }}</p> @enderror
                                @error($config['field'].'.*') <p class="error mt-2">{{ $message }}</p> @enderror
                            </section>
                        </div>
                    @endforeach

                </div>{{-- /data-regola=order --}}

            </div>{{-- /wiz-panel 3 --}}

        </div>{{-- /marketing-form-main --}}

        {{-- ─────────────────────────────────────── --}}
        {{-- Sidebar preview                         --}}
        {{-- ─────────────────────────────────────── --}}
        <aside class="marketing-form-sidebar">
            <section class="order-detail__section marketing-form-preview">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <i class="bi bi-eye-fill"></i>
                        </span>
                        Riepilogo
                    </h3>
                </div>

                <div class="marketing-form-preview__panel">
                    <div class="marketing-form-preview__head">
                        <span class="marketing-form-preview__icon">
                            <i class="bi bi-megaphone-fill"></i>
                        </span>
                        <div>
                            <strong data-preview-name>
                                {{ old('name', $promotion->name) ?: 'Nome promozione' }}
                            </strong>
                        </div>
                    </div>

                    <div class="marketing-form-preview__facts">
                        @if ($promotion->exists)
                            <div class="marketing-form-preview__fact">
                                <span>Stato</span>
                                <strong>{{ $statuses[$promotion->status] ?? $promotion->status }}</strong>
                            </div>
                        @endif
                        <div class="marketing-form-preview__fact">
                            <span>Caso d'uso</span>
                            <strong data-preview-case>
                                {{ $caseUseLabels[$previewCaseUse] ?? ($previewCaseUse ? ucfirst($previewCaseUse) : '—') }}
                            </strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Tipo sconto</span>
                            <strong data-preview-td>
                                {{ $discountTypeLabels[$previewDiscountType] ?? ($previewDiscountType ?: '—') }}
                            </strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Target</span>
                            <strong data-preview-target>{{ $targetSummary }}</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Permanente</span>
                            <strong data-preview-perm>{{ $previewPermanent ? 'Sì' : 'No' }}</strong>
                        </div>
                    </div>

                    @if ($targetType !== 'generic' && $previewTargetLabels->isNotEmpty())
                        <div class="marketing-form-preview__chips" data-preview-chips>
                            @foreach ($previewTargetLabels->take(5) as $lbl)
                                <span>{{ $lbl }}</span>
                            @endforeach
                            @if ($previewTargetLabels->count() > 5)
                                <span>+{{ $previewTargetLabels->count() - 5 }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
        </aside>

    </div>{{-- /marketing-form-grid --}}

    {{-- Navigation --}}
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
            @if ($initialStep === 3) hidden @endif
        >
            <span>Avanti</span>
            <i class="bi bi-chevron-right"></i>
        </button>

        <button
            type="submit"
            name="submit_action"
            value="draft"
            class="order-detail__contact marketing-form-action--secondary"
            data-wiz-draft
            @if ($initialStep !== 3) hidden @endif
        >
            <i class="bi bi-clock-history"></i>
            <span>Completa più tardi</span>
        </button>

        <button
            type="submit"
            name="submit_action"
            value="activate"
            class="order-detail__contact marketing-form-action--primary"
            data-wiz-submit
            @if ($initialStep !== 3) hidden @endif
        >
            <i class="bi bi-check2-circle"></i>
            <span>{{ $primaryActionLabel }}</span>
        </button>

        @error('submit_action') <p class="error">{{ $message }}</p> @enderror
    </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-promo-form]');
    if (!form) return;

    let currentStep = {{ $initialStep }};
    const TOTAL = 3;

    const h = (id) => document.getElementById(id);
    const cu  = () => h('h_case_use')?.value ?? '';
    const tt  = () => h('h_target_type')?.value ?? '';
    const td  = () => h('h_type_discount')?.value ?? '';

    const group = () => cu() === 'table' ? 'table' : (cu() ? 'order' : null);

    const CU_LABELS = { table: 'Prenotazioni', generic: 'Ordini', take_away: 'Solo asporto', delivery: 'Solo delivery' };
    const TD_LABELS = { percentage: 'Percentuale', fixed: 'Fisso', gift: 'Omaggio' };
    const TT_LABELS = { generic: 'Carrello', product: 'Prodotti', menu: 'Menu', category: 'Categorie' };

    const btnPrev   = form.querySelector('[data-wiz-prev]');
    const btnNext   = form.querySelector('[data-wiz-next]');
    const btnDraft  = form.querySelector('[data-wiz-draft]');
    const btnSubmit = form.querySelector('[data-wiz-submit]');

    const setHidden = (el, v) => { if (el) el.hidden = v; };

    // Disable/enable submitting inputs inside a container (prevents double-submit from hidden lists)
    const setInputsDisabled = (el, disabled) => {
        el?.querySelectorAll('input[name]:not([name^="_"]), select[name], textarea[name]')
          .forEach(f => { f.disabled = disabled; });
    };

    // Disable item list inputs that are hidden to prevent double-submission
    const syncItemDisabled = () => {
        ['product', 'menu', 'category'].forEach(type => {
            const el = form.querySelector(`[data-items="${type}"]`);
            setInputsDisabled(el, !el || el.hidden);
        });
    };

    // Radio data-sync: sync checked value to hidden input
    form.querySelectorAll('[data-sync]').forEach(radio => {
        if (radio.type !== 'radio') return;
        radio.addEventListener('change', () => {
            if (!radio.checked) return;
            const hidden = h(radio.getAttribute('data-sync'));
            if (hidden) hidden.value = radio.value;
            if (currentStep === 3) renderStep3();
            updatePreview();
        });
    });

    /* ── Permanent toggle ──────────────────────────── */
    const permanentInput = form.querySelector('[data-permanent]');
    const datesPanel     = form.querySelector('[data-dates-panel]');

    const syncPermanent = () => {
        const on = permanentInput?.checked ?? false;
        if (datesPanel) {
            datesPanel.hidden = on;
            datesPanel.querySelectorAll('input').forEach(f => { f.disabled = on; if (on) f.value = ''; });
        }
        const el = form.querySelector('[data-preview-perm]');
        if (el) el.textContent = on ? 'Sì' : 'No';
    };

    permanentInput?.addEventListener('change', syncPermanent);

    /* ── Preview update ───────────────────────────── */
    const updatePreview = () => {
        const nameEl = form.querySelector('#name');
        const pName  = form.querySelector('[data-preview-name]');
        if (pName) pName.textContent = nameEl?.value.trim() || 'Nome promozione';

        const pCase = form.querySelector('[data-preview-case]');
        if (pCase) pCase.textContent = CU_LABELS[cu()] || '—';

        const pTd = form.querySelector('[data-preview-td]');
        if (pTd) pTd.textContent = TD_LABELS[td()] || '—';

        const pTarget = form.querySelector('[data-preview-target]');
        if (pTarget) pTarget.textContent = cu() === 'table' ? '—' : (TT_LABELS[tt()] || '—');
    };

    form.querySelector('#name')?.addEventListener('input', updatePreview);

    /* ── Step bar rendering ───────────────────────── */
    const renderStepBar = (step) => {
        form.querySelectorAll('[data-wiz-panel]').forEach(p => {
            p.hidden = +p.dataset.wizPanel !== step;
        });

        form.querySelectorAll('[data-step-dot]').forEach(dot => {
            const n = +dot.dataset.stepDot;
            dot.classList.toggle('is-active', n === step);
            dot.classList.toggle('is-done',   n < step);
            const numEl = dot.querySelector('.promo-wiz__dot-num');
            if (numEl) numEl.innerHTML = n < step ? '<i class="bi bi-check-lg"></i>' : String(n);
        });

        form.querySelectorAll('[data-step-line]').forEach(line => {
            line.classList.toggle('is-done', +line.dataset.stepLine < step);
        });

        setHidden(btnPrev,   step === 1);
        setHidden(btnNext,   step === TOTAL);
        setHidden(btnDraft,  step !== TOTAL);
        setHidden(btnSubmit, step !== TOTAL);

        // When not on step 3, disable item selector inputs to prevent unintended submission
        if (step !== 3) {
            ['product', 'menu', 'category'].forEach(type => {
                setInputsDisabled(form.querySelector(`[data-items="${type}"]`), true);
            });
        }
    };

    /* ── Step 3 conditional rendering ─────────────── */
    const renderStep3 = () => {
        const g  = group();
        const tv = tt();
        const dv = td();

        setHidden(form.querySelector('[data-regola="table"]'), g !== 'table');
        setHidden(form.querySelector('[data-regola="order"]'), g !== 'order');

        if (g === 'order') {
            setHidden(form.querySelector('[data-order-td-wrap]'), !tv);

            const giftCard   = form.querySelector('[data-gift-card]');
            const giftAllowed = tv === 'product' || tv === 'menu';
            setHidden(giftCard, !giftAllowed);

            if (!giftAllowed && dv === 'gift') {
                h('h_type_discount').value = '';
                form.querySelectorAll('[name="_td_order"]').forEach(r => r.checked = false);
            }

            setHidden(form.querySelector('[data-order-sconto-wrap]'), !dv || dv === 'gift');

            ['product', 'menu', 'category'].forEach(type => {
                setHidden(form.querySelector(`[data-items="${type}"]`), tv !== type);
            });
        }

        syncItemDisabled();
        updatePreview();
    };

    /* ── Navigation ───────────────────────────────── */
    btnNext?.addEventListener('click', () => {
        if (currentStep >= TOTAL) return;

        if (currentStep === 2) {
            // Reset derived state when advancing to step 3 (in case case_use changed)
            h('h_type_discount').value = '';
            h('h_discount').value      = '';
            form.querySelectorAll('[name="_td_table"],[name="_td_order"]').forEach(r => r.checked = false);
            const dspDT = form.querySelector('#dsp_discount_table');
            const dspDO = form.querySelector('#dsp_discount_order');
            if (dspDT) dspDT.value = '';
            if (dspDO) dspDO.value = '';
        }

        currentStep++;
        renderStepBar(currentStep);
        if (currentStep === 3) renderStep3();
    });

    btnPrev?.addEventListener('click', () => {
        if (currentStep <= 1) return;
        currentStep--;
        renderStepBar(currentStep);
    });

    /* ── Target card: reset td/discount on change ─── */
    form.querySelectorAll('[data-target-radio]').forEach(radio => {
        radio.addEventListener('change', () => {
            h('h_type_discount').value = '';
            h('h_discount').value      = '';
            form.querySelectorAll('[name="_td_order"]').forEach(r => r.checked = false);
            const dspDO = form.querySelector('#dsp_discount_order');
            if (dspDO) dspDO.value = '';
            renderStep3();
        });
    });

    /* ── Tipo sconto ordini: clear discount on gift ── */
    form.querySelectorAll('[data-td-radio]').forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'gift') {
                const dspDO = form.querySelector('#dsp_discount_order');
                if (dspDO) dspDO.value = '';
                h('h_discount').value = '';
            }
            renderStep3();
        });
    });

    /* ── Display number inputs → sync to hidden ─── */
    [
        ['#dsp_min_table',      'h_minimum_pretest'],
        ['#dsp_min_order',      'h_minimum_pretest'],
        ['#dsp_discount_table', 'h_discount'],
        ['#dsp_discount_order', 'h_discount'],
    ].forEach(([sel, hid]) => {
        const el = form.querySelector(sel);
        el?.addEventListener('input', () => {
            if (!el.closest('[hidden]')) {
                const hidden = h(hid);
                if (hidden) hidden.value = el.value;
            }
        });
    });

    /* ── Initial render ───────────────────────────── */
    syncPermanent();
    renderStepBar(currentStep);
    if (currentStep === 3) renderStep3();
    updatePreview();
});
</script>
