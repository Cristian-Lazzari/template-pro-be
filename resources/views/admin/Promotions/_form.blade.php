@php
    $reusableValue = old('metadata.reusable', data_get($promotion->metadata, 'reusable', false));
    $scheduleValue = old('schedule_at', $promotion->schedule_at?->format('Y-m-d\TH:i'));
    $expiringValue = old('expiring_at', $promotion->expiring_at?->format('Y-m-d\TH:i'));
    $existingTargetRows = $promotion->exists
        ? $promotion->targets->filter(fn ($target) => ! $target->isGenericTarget())->map(fn ($target) => [
            'target_key' => $target->target_type . ':' . ($target->target_id ?? ''),
            'discount' => $target->discount,
            'type_discount' => $target->type_discount,
        ])->values()->all()
        : [];
    $hasSpecificTargets = count($existingTargetRows) > 0;
    $targetScope = old('target_scope', $hasSpecificTargets ? 'specific' : 'generic');
    $targetRows = array_values(old('targets', $existingTargetRows));
    $minimumTargetRows = max(4, count($targetRows) + 1);

    while (count($targetRows) < $minimumTargetRows) {
        $targetRows[] = [
            'target_key' => '',
            'discount' => '',
            'type_discount' => '',
        ];
    }
@endphp

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
        border-radius: 18px;
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
        border-radius: 12px;
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
</style>

@if ($errors->any())
    <div class="alert alert-danger">
        Controlla i campi evidenziati prima di salvare.
    </div>
@endif

<form class="creation promotion-form-ui mt-4" action="{{ $action }}" method="POST" data-promotion-form>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-card-text"></i>
                </span>
                Dati principali
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
                <label class="label_c" for="slug">
                    <i class="bi bi-link-45deg"></i>
                    Slug
                </label>
                <p>
                    <input value="{{ old('slug', $promotion->slug) }}" type="text" name="slug" id="slug" placeholder="Generato dal nome se vuoto">
                </p>
                @error('slug') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="status">
                    <i class="bi bi-toggle-on"></i>
                    Status
                </label>
                <p>
                    <select name="status" id="status">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $promotion->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                @error('status') <p class="error">{{ $message }}</p> @enderror
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
    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-bullseye"></i>
                </span>
                Ambito sconto
            </h3>
        </div>

        <div class="promotion-choice-grid">
            <div class="promotion-choice-option">
                <input class="promotion-choice-input" type="radio" name="target_scope" id="target_scope_generic" value="generic" @checked($targetScope !== 'specific')>
                <label class="promotion-choice-card" for="target_scope_generic">
                    <span class="promotion-choice-icon">
                        <i class="bi bi-cart-check-fill"></i>
                    </span>
                    <span>
                        <strong>Sconto generico sul carrello</strong>
                        <small>La regola vale sull'intero carrello o sulla prenotazione.</small>
                    </span>
                </label>
            </div>

            <div class="promotion-choice-option">
                <input class="promotion-choice-input" type="radio" name="target_scope" id="target_scope_specific" value="specific" @checked($targetScope === 'specific')>
                <label class="promotion-choice-card" for="target_scope_specific">
                    <span class="promotion-choice-icon">
                        <i class="bi bi-tags-fill"></i>
                    </span>
                    <span>
                        <strong>Sconto su prodotto, categoria o menu</strong>
                        <small>La regola vale solo sugli elementi selezionati.</small>
                    </span>
                </label>
            </div>
        </div>
        @error('target_scope') <p class="error">{{ $message }}</p> @enderror
    </section>

    <section class="order-detail__section promotion-form-panel" data-target-scope-panel="generic" @if ($targetScope === 'specific') hidden @endif>
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-percent"></i>
                </span>
                Sconto carrello
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="type_discount">
                    <i class="bi bi-percent"></i>
                    Tipo sconto
                </label>
                <p>
                    <select name="type_discount" id="type_discount">
                        <option value="">Nessuno</option>
                        @foreach ($discountTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('type_discount', $promotion->type_discount) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                @error('type_discount') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="discount">
                    <i class="bi bi-cash-coin"></i>
                    Sconto
                </label>
                <p>
                    <input value="{{ old('discount', $promotion->discount) }}" type="number" step="0.01" min="0" name="discount" id="discount">
                </p>
                @error('discount') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="order-detail__section promotion-form-panel" data-target-scope-panel="specific" @if ($targetScope !== 'specific') hidden @endif>
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-tags-fill"></i>
                </span>
                Sconti target
            </h3>
        </div>

        <div class="order-detail__items">
            @foreach ($targetRows as $index => $targetRow)
                @php
                    $selectedTargetKey = (string) ($targetRow['target_key'] ?? '');
                    $targetDiscount = $targetRow['discount'] ?? '';
                    $targetDiscountType = $targetRow['type_discount'] ?? '';
                @endphp

                <article class="order-detail__item">
                    <div class="split">
                        <div>
                            <label class="label_c" for="targets_{{ $index }}_target_key">
                                <i class="bi bi-bullseye"></i>
                                Target {{ $index + 1 }}
                            </label>
                            <p>
                                <select name="targets[{{ $index }}][target_key]" id="targets_{{ $index }}_target_key">
                                    <option value="">Nessuna selezione</option>
                                    @foreach ($specificTargetOptions as $group => $options)
                                        @if (count($options) > 0)
                                            <optgroup label="{{ $formTargetTypes[$group] ?? $group }}">
                                                @foreach ($options as $option)
                                                    <option value="{{ $option['key'] }}" @selected($selectedTargetKey === $option['key'])>
                                                        {{ $option['label'] }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                            </p>
                            @error("targets.$index.target_key") <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label_c" for="targets_{{ $index }}_type_discount">
                                <i class="bi bi-percent"></i>
                                Tipo sconto
                            </label>
                            <p>
                                <select name="targets[{{ $index }}][type_discount]" id="targets_{{ $index }}_type_discount">
                                    <option value="">Nessuno</option>
                                    @foreach ($discountTypes as $value => $label)
                                        <option value="{{ $value }}" @selected((string) $targetDiscountType === (string) $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </p>
                            @error("targets.$index.type_discount") <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="split">
                        <div>
                            <label class="label_c" for="targets_{{ $index }}_discount">
                                <i class="bi bi-cash-coin"></i>
                                Sconto
                            </label>
                            <p>
                                <input value="{{ $targetDiscount }}" type="number" step="0.01" min="0" name="targets[{{ $index }}][discount]" id="targets_{{ $index }}_discount" placeholder="Valore sconto">
                            </p>
                            @error("targets.$index.discount") <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div></div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-bag-check"></i>
                </span>
                Minimo e CTA
            </h3>
        </div>

        <div class="split">
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
                    <i class="bi bi-calendar2-week-fill"></i>
                </span>
                Validita e opzioni
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="schedule_at">
                    <i class="bi bi-calendar-plus"></i>
                    Programmazione
                </label>
                <p>
                    <input value="{{ $scheduleValue }}" type="datetime-local" name="schedule_at" id="schedule_at">
                </p>
                @error('schedule_at') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="expiring_at">
                    <i class="bi bi-calendar-x"></i>
                    Scadenza
                </label>
                <p>
                    <input value="{{ $expiringValue }}" type="datetime-local" name="expiring_at" id="expiring_at">
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

    <div class="d-flex justify-content-end mb-5">
        <button class="my_btn_2 w-auto" type="submit">
            <i class="bi bi-check2-circle"></i>
            {{ $submitLabel }}
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-promotion-form]');

        if (!form) {
            return;
        }

        const scopeInputs = form.querySelectorAll('input[name="target_scope"]');
        const genericPanel = form.querySelector('[data-target-scope-panel="generic"]');
        const specificPanel = form.querySelector('[data-target-scope-panel="specific"]');

        const setPanelState = (panel, active) => {
            if (!panel) {
                return;
            }

            panel.hidden = !active;
            panel.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = !active;
            });
        };

        const syncPanels = () => {
            const selected = form.querySelector('input[name="target_scope"]:checked')?.value || 'generic';

            setPanelState(genericPanel, selected === 'generic');
            setPanelState(specificPanel, selected === 'specific');
        };

        scopeInputs.forEach((input) => input.addEventListener('change', syncPanels));
        syncPanels();
    });
</script>
