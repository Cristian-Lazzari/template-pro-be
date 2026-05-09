@php
    $reusableValue = old('metadata.reusable', data_get($promotion->metadata, 'reusable', false));
    $previewPermanent = filter_var(old('permanent', $promotion->permanent), FILTER_VALIDATE_BOOLEAN);
    $scheduleValue = $previewPermanent ? '' : old('schedule_at', $promotion->schedule_at?->format('Y-m-d\TH:i'));
    $expiringValue = $previewPermanent ? '' : old('expiring_at', $promotion->expiring_at?->format('Y-m-d\TH:i'));
    $targetConfigs = [
        'product' => [
            'field' => 'product_ids',
            'label' => 'Scegli prodotti',
            'empty' => 'Nessun prodotto disponibile',
            'card_title' => 'Prodotti',
            'card_copy' => 'La regola vale sui prodotti selezionati.',
            'icon' => 'bi-tags-fill',
            'singular' => '1 prodotto',
            'plural' => 'prodotti',
        ],
        'menu' => [
            'field' => 'menu_ids',
            'label' => 'Scegli menu',
            'empty' => 'Nessun menu disponibile',
            'card_title' => 'Menu',
            'card_copy' => 'La regola vale sui menu selezionati.',
            'icon' => 'bi-menu-button-wide-fill',
            'singular' => '1 menu',
            'plural' => 'menu',
        ],
        'category' => [
            'field' => 'category_ids',
            'label' => 'Scegli categorie',
            'empty' => 'Nessuna categoria disponibile',
            'card_title' => 'Categorie',
            'card_copy' => 'La regola vale sulle categorie selezionate.',
            'icon' => 'bi-folder-fill',
            'singular' => '1 categoria',
            'plural' => 'categorie',
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
                ->map(fn ($targetId) => (string) $targetId)
                ->unique()
                ->values()
                ->all()
            : [];
    }

    $existingSpecificTargetType = collect($existingTargetIds)
        ->search(fn ($targetIds) => count($targetIds) > 0);
    $targetType = old('target_type', $existingSpecificTargetType ?: 'generic');
    $targetType = in_array($targetType, $validTargetTypes, true) ? $targetType : 'generic';
    $selectedTargetIds = [];

    foreach ($targetConfigs as $type => $config) {
        $selectedTargetIds[$type] = collect((array) old($config['field'], $existingTargetIds[$type]))
            ->map(fn ($targetId) => (string) $targetId)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    $targetOptionsByType = collect($targetConfigs)
        ->mapWithKeys(fn ($config, $type) => [$type => $specificTargetOptions[$type] ?? []])
        ->all();

    $primaryActionLabel = $method === 'POST' ? 'Crea e attiva' : 'Salva e attiva';
    $cancelUrl = $promotion->exists ? route('admin.promotions.show', $promotion) : route('admin.promotions.index');
    $rawPreviewDiscountType = old('type_discount', $promotion->type_discount);
    $previewDiscountType = $targetType === 'category' && $rawPreviewDiscountType === 'gift'
        ? 'fixed'
        : $rawPreviewDiscountType;
    $previewCaseUse = old('case_use', $promotion->case_use);
    $targetOptionLabels = collect($targetOptionsByType[$targetType] ?? [])
        ->mapWithKeys(function ($option) {
            $targetId = explode(':', $option['key'], 2)[1] ?? '';

            return [$targetId => $option['label']];
        })
        ->all();
    $previewTargetLabels = collect($targetType === 'generic' ? [] : ($selectedTargetIds[$targetType] ?? []))
        ->map(fn ($targetId) => $targetOptionLabels[$targetId] ?? ($targetConfigs[$targetType]['card_title'] ?? 'Target') . ' #' . $targetId)
        ->unique()
        ->values();
    $targetCount = $previewTargetLabels->count();
    $targetSummary = $targetType === 'generic'
        ? 'Promo generale'
        : ($targetCount === 1 ? $targetConfigs[$targetType]['singular'] : $targetCount . ' ' . $targetConfigs[$targetType]['plural']);
@endphp

@include('admin.Marketing.partials.form-style')

<style>
    .promotion-form-ui {
        display: grid;
        gap: 18px;
    }

    .promotion-choice-grid,
    .promotion-toggle-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 12px;
    }

    .promotion-choice-option,
    .promotion-toggle-option {
        position: relative;
        min-width: 0;
    }

    .promotion-choice-input,
    .promotion-toggle-input {
        position: absolute;
        inline-size: 1px;
        block-size: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .promotion-choice-card,
    .promotion-toggle-card {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 12px;
        min-height: 100%;
        padding: 16px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
        color: var(--c3);
        cursor: pointer;
        transition: border-color .16s ease, background .16s ease, transform .16s ease;
    }

    .promotion-choice-card:hover,
    .promotion-toggle-card:hover {
        transform: translateY(-1px);
        border-color: rgba(14, 183, 146, 0.26);
        background: rgba(14, 183, 146, 0.08);
    }

    .promotion-choice-input:focus-visible + .promotion-choice-card,
    .promotion-toggle-input:focus-visible + .promotion-toggle-card {
        outline: 2px solid rgba(142, 246, 219, 0.7);
        outline-offset: 3px;
    }

    .promotion-choice-input:checked + .promotion-choice-card,
    .promotion-toggle-input:checked + .promotion-toggle-card {
        border-color: rgba(14, 183, 146, 0.42);
        background:
            linear-gradient(135deg, rgba(14, 183, 146, 0.18), rgba(216, 221, 232, 0.05));
    }

    .promotion-choice-icon,
    .promotion-toggle-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.42);
        color: rgba(142, 246, 219, 0.92);
        flex: 0 0 auto;
    }

    .promotion-choice-card strong,
    .promotion-toggle-card strong {
        display: block;
        color: var(--c3);
        font-size: var(--fs-300);
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .promotion-choice-card small,
    .promotion-toggle-card small {
        display: block;
        margin-top: 5px;
        color: rgba(216, 221, 232, 0.74);
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .promotion-form-panel[hidden] {
        display: none !important;
    }

    .promotion-target-panel {
        display: grid;
        gap: 10px;
        padding: 14px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.04);
    }

    .promotion-target-list {
        display: grid;
        gap: 8px;
        max-height: 320px;
        overflow: auto;
    }

    .promotion-target-check {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.28);
        color: var(--c3);
        cursor: pointer;
    }

    .promotion-target-check:hover {
        border-color: rgba(14, 183, 146, 0.28);
        background: rgba(14, 183, 146, 0.08);
    }

    .promotion-target-check input {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }

    .promotion-target-check span {
        min-width: 0;
        overflow-wrap: anywhere;
        line-height: 1.35;
    }

    .promotion-target-empty,
    .promotion-form-note {
        margin: 0;
        color: rgba(216, 221, 232, 0.74);
        line-height: 1.45;
    }

    .promotion-form-note[hidden] {
        display: none !important;
    }
</style>

@if ($errors->any())
    <div class="alert alert-danger">
        Controlla i campi evidenziati prima di salvare.
    </div>
@endif

<form class="creation marketing-form-shell promotion-form-ui mt-4" action="{{ $action }}" method="POST" data-promotion-form>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="marketing-form-grid">
        <div class="marketing-form-main">
    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-card-text"></i>
                </span>
                Informazioni principali
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="name">
                    <i class="bi bi-type"></i>
                    Nome
                </label>
                <p>
                    <input value="{{ old('name', $promotion->name) }}" type="text" name="name" id="name" placeholder="Nome promozione">
                </p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="case_use">
                    <i class="bi bi-bullseye"></i>
                    Caso d'uso
                </label>
                <p>
                    <select name="case_use" id="case_use">
                        <option value="">Nessuno</option>
                        @foreach ($caseUses as $value => $label)
                            <option value="{{ $value }}" @selected(old('case_use', $promotion->case_use) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                @error('case_use') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <p class="menu-dashboard__copy mt-3">Lo slug viene generato automaticamente.</p>
    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-percent"></i>
                </span>
                Regola promozione
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="type_discount">
                    <i class="bi bi-percent"></i>
                    Tipo sconto
                </label>
                <p>
                    <select name="type_discount" id="type_discount" data-type-discount>
                        <option value="">Nessuno</option>
                        @foreach ($discountTypes as $value => $label)
                            <option value="{{ $value }}" @selected($previewDiscountType === $value) @disabled($targetType === 'category' && $value === 'gift')>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                <p class="promotion-form-note" data-category-gift-copy @if ($targetType !== 'category') hidden @endif>L'omaggio non è disponibile per promozioni su categorie.</p>
                @error('type_discount') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="discount">
                    <i class="bi bi-cash-coin"></i>
                    Sconto
                </label>
                <p>
                    <input
                        value="{{ $previewDiscountType === 'gift' ? '' : old('discount', $promotion->discount) }}"
                        type="number"
                        step="0.01"
                        min="0"
                        name="discount"
                        id="discount"
                        data-discount-field
                        @disabled($previewDiscountType === 'gift')
                    >
                </p>
                @error('discount') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="split mt-3">
            <div>
                <label class="label_c" for="minimum_pretest">
                    <i class="bi bi-bag-check"></i>
                    Minimo
                </label>
                <p>
                    <input value="{{ old('minimum_pretest', $promotion->minimum_pretest) }}" type="number" step="0.01" min="0" name="minimum_pretest" id="minimum_pretest">
                </p>
                @error('minimum_pretest') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="cta">
                    <i class="bi bi-cursor-fill"></i>
                    CTA
                </label>
                <p>
                    <input value="{{ old('cta', $promotion->cta) }}" type="text" name="cta" id="cta" placeholder="Testo call to action">
                </p>
                @error('cta') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-bullseye"></i>
                </span>
                Target
            </h3>
        </div>

        <div class="promotion-choice-grid">
            <div class="promotion-choice-option">
                <input class="promotion-choice-input" type="radio" name="target_type" id="target_type_generic" value="generic" @checked($targetType === 'generic')>
                <label class="promotion-choice-card" for="target_type_generic">
                    <span class="promotion-choice-icon">
                        <i class="bi bi-cart-check-fill"></i>
                    </span>
                    <span>
                        <strong>Promo generale</strong>
                        <small>La regola vale come promozione generale.</small>
                    </span>
                </label>
            </div>

            @foreach ($targetConfigs as $type => $config)
                <div class="promotion-choice-option">
                    <input class="promotion-choice-input" type="radio" name="target_type" id="target_type_{{ $type }}" value="{{ $type }}" @checked($targetType === $type)>
                    <label class="promotion-choice-card" for="target_type_{{ $type }}">
                        <span class="promotion-choice-icon">
                            <i class="bi {{ $config['icon'] }}"></i>
                        </span>
                        <span>
                            <strong>{{ $config['card_title'] }}</strong>
                            <small>{{ $config['card_copy'] }}</small>
                        </span>
                    </label>
                </div>
            @endforeach
        </div>
        @error('target_type') <p class="error">{{ $message }}</p> @enderror

        @foreach ($targetConfigs as $type => $config)
            @php
                $options = $targetOptionsByType[$type] ?? [];
            @endphp

            <div class="promotion-form-panel promotion-target-panel mt-3" data-target-type-panel="{{ $type }}" @if ($targetType !== $type) hidden @endif>
                <div class="label_c">
                    <i class="bi {{ $config['icon'] }}"></i>
                    {{ $config['label'] }}
                </div>

                @if (count($options) === 0)
                    <p class="promotion-target-empty">{{ $config['empty'] }}</p>
                @else
                    <div class="promotion-target-list">
                        @foreach ($options as $option)
                            @php
                                $targetId = explode(':', $option['key'], 2)[1] ?? '';
                                $inputId = 'target_' . $type . '_' . $targetId;
                            @endphp

                            <label class="promotion-target-check" for="{{ $inputId }}">
                                <input
                                    type="checkbox"
                                    name="{{ $config['field'] }}[]"
                                    id="{{ $inputId }}"
                                    value="{{ $targetId }}"
                                    @checked(in_array((string) $targetId, $selectedTargetIds[$type] ?? [], true))
                                    @disabled($targetType !== $type)
                                >
                                <span>{{ $option['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif

                @error($config['field']) <p class="error">{{ $message }}</p> @enderror
                @error($config['field'] . '.*') <p class="error">{{ $message }}</p> @enderror
            </div>
        @endforeach
    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-calendar2-week-fill"></i>
                </span>
                Validità
            </h3>
        </div>

        <div class="split" data-permanent-dates @if ($previewPermanent) hidden @endif>
            <div>
                <label class="label_c" for="schedule_at">
                    <i class="bi bi-calendar-plus"></i>
                    Programmazione
                </label>
                <p>
                    <input value="{{ $scheduleValue }}" type="datetime-local" name="schedule_at" id="schedule_at" @disabled($previewPermanent)>
                </p>
                @error('schedule_at') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="expiring_at">
                    <i class="bi bi-calendar-x"></i>
                    Scadenza
                </label>
                <p>
                    <input value="{{ $expiringValue }}" type="datetime-local" name="expiring_at" id="expiring_at" @disabled($previewPermanent)>
                </p>
                @error('expiring_at') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="promotion-toggle-grid">
            <div class="promotion-toggle-option">
                <input type="hidden" name="permanent" value="0">
                <input class="promotion-toggle-input" type="checkbox" name="permanent" id="permanent" value="1" @checked(old('permanent', $promotion->permanent))>
                <label class="promotion-toggle-card" for="permanent">
                    <span class="promotion-toggle-icon">
                        <i class="bi bi-infinity"></i>
                    </span>
                    <span>
                        <strong>Permanente</strong>
                        <small>Ignora date di programmazione e scadenza.</small>
                    </span>
                </label>
                @error('permanent') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="promotion-toggle-option">
                <input type="hidden" name="metadata[reusable]" value="0">
                <input class="promotion-toggle-input" type="checkbox" name="metadata[reusable]" id="metadata_reusable" value="1" @checked(filter_var($reusableValue, FILTER_VALIDATE_BOOLEAN))>
                <label class="promotion-toggle-card" for="metadata_reusable">
                    <span class="promotion-toggle-icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </span>
                    <span>
                        <strong>Riusabile</strong>
                        <small>Permette piu utilizzi per lo stesso cliente.</small>
                    </span>
                </label>
                @error('metadata.reusable') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>
        </div>

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
                            <strong>{{ old('name', $promotion->name) ?: 'Nome promozione' }}</strong>
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
                            <strong>{{ $caseUses[$previewCaseUse] ?? ($previewCaseUse ?: '-') }}</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Tipo sconto</span>
                            <strong>{{ $discountTypes[$previewDiscountType] ?? ($previewDiscountType ?: '-') }}</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Target</span>
                            <strong>{{ $targetSummary }}</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Permanente</span>
                            <strong>{{ $previewPermanent ? 'Sì' : 'No' }}</strong>
                        </div>
                    </div>

                    @if ($targetType !== 'generic')
                        <div class="marketing-form-preview__chips">
                            @forelse ($previewTargetLabels->take(5) as $targetLabel)
                                <span>{{ $targetLabel }}</span>
                            @empty
                                <span>{{ $targetConfigs[$targetType]['empty'] ?? 'Nessun target selezionato' }}</span>
                            @endforelse
                            @if ($previewTargetLabels->count() > 5)
                                <span>+{{ $previewTargetLabels->count() - 5 }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
        </aside>
    </div>

    <div class="marketing-form-actions">
        <a class="order-detail__contact marketing-form-action--cancel" href="{{ $cancelUrl }}">
            <i class="bi bi-x-lg"></i>
            <span>Annulla</span>
        </a>
        <button class="order-detail__contact marketing-form-action--secondary" type="submit" name="submit_action" value="draft">
            <i class="bi bi-clock-history"></i>
            <span>Completa più tardi</span>
        </button>
        <button class="order-detail__contact marketing-form-action--primary" type="submit" name="submit_action" value="activate">
            <i class="bi bi-check2-circle"></i>
            <span>{{ $primaryActionLabel }}</span>
        </button>
        @error('submit_action') <p class="error">{{ $message }}</p> @enderror
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-promotion-form]');

        if (!form) {
            return;
        }

        const targetTypeInputs = form.querySelectorAll('input[name="target_type"]');
        const targetPanels = form.querySelectorAll('[data-target-type-panel]');
        const typeDiscount = form.querySelector('[data-type-discount]');
        const discountField = form.querySelector('[data-discount-field]');
        const categoryGiftCopy = form.querySelector('[data-category-gift-copy]');
        const permanentInput = form.querySelector('#permanent');
        const datePanel = form.querySelector('[data-permanent-dates]');

        const setPanelState = (panel, active) => {
            if (!panel) {
                return;
            }

            panel.hidden = !active;
            panel.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = !active;
            });
        };

        const syncTargetPanel = () => {
            const selected = form.querySelector('input[name="target_type"]:checked')?.value || 'generic';

            targetPanels.forEach((panel) => {
                setPanelState(panel, panel.dataset.targetTypePanel === selected);
            });

            syncDiscount();
        };

        const syncDiscount = () => {
            if (!typeDiscount || !discountField) {
                return;
            }

            const selectedTarget = form.querySelector('input[name="target_type"]:checked')?.value || 'generic';
            const giftOption = typeDiscount.querySelector('option[value="gift"]');
            const isCategory = selectedTarget === 'category';

            if (giftOption) {
                giftOption.disabled = isCategory;
            }

            if (categoryGiftCopy) {
                categoryGiftCopy.hidden = !isCategory;
            }

            if (isCategory && typeDiscount.value === 'gift') {
                typeDiscount.value = 'fixed';
            }

            const isGift = typeDiscount.value === 'gift';

            if (isGift) {
                discountField.value = '';
            }

            discountField.disabled = isGift;
        };

        const syncDates = () => {
            if (!permanentInput || !datePanel) {
                return;
            }

            const isPermanent = permanentInput.checked;

            datePanel.hidden = isPermanent;
            datePanel.querySelectorAll('input').forEach((field) => {
                if (isPermanent) {
                    field.value = '';
                }

                field.disabled = isPermanent;
            });
        };

        targetTypeInputs.forEach((input) => input.addEventListener('change', syncTargetPanel));
        typeDiscount?.addEventListener('change', syncDiscount);
        permanentInput?.addEventListener('change', syncDates);
        syncTargetPanel();
        syncDiscount();
        syncDates();
    });
</script>
