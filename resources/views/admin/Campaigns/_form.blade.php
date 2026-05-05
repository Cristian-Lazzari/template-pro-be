@php
    $selectedPromotionIds = collect(old('promotions', $campaign->exists ? $campaign->promotions->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $primaryActionLabel = $method === 'POST' ? 'Crea e programma' : 'Salva e programma';
    $selectedScheduleWindow = old('schedule_window', data_get($campaign->metadata, 'schedule_window', 'next_available'));
    $legacySegmentMap = [
        'inactive_customers' => 'at_risk_customers',
        'high_spending_customers' => 'high_value_customers',
    ];
    $selectedSegment = old('segment', $campaign->segment ?: 'all');
    $selectedSegment = $legacySegmentMap[$selectedSegment] ?? $selectedSegment;
    $selectedMailModelId = (string) old('model_id', $campaign->model_id);
    $selectedScheduleWindow = array_key_exists($selectedScheduleWindow, $scheduleWindows)
        ? $selectedScheduleWindow
        : 'next_available';
    $previewMailModel = collect($mailModels)->first(fn ($mailModel) => (string) $mailModel->id === $selectedMailModelId) ?: $campaign->model;
    $previewPromotions = collect($promotions)
        ->filter(fn ($promotion) => in_array((string) $promotion->id, $selectedPromotionIds, true))
        ->values();
    $previewScheduleWindowLabel = $scheduleWindows[$selectedScheduleWindow] ?? 'Prima finestra disponibile';
    $selectedAudienceCount = $audienceCounts[$selectedSegment] ?? 0;
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
@endphp

@include('admin.Marketing.partials.form-style')

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

<form class="creation marketing-form-shell mt-4" action="{{ $action }}" method="POST" data-campaign-form>
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
                Dati campagna
            </h3>
        </div>

        <div>
            <label class="label_c" for="name">
                <i class="bi bi-type"></i>
                Nome
            </label>
            <p>
                <input value="{{ old('name', $campaign->name) }}" type="text" name="name" id="name" placeholder="Nome campagna">
            </p>
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>
    </section>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-people-fill"></i>
                </span>
                Audience e modello
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="segment">
                    <i class="bi bi-people-fill"></i>
                    Segmento
                </label>
                <p>
                    <select name="segment" id="segment" data-segment-select>
                        @foreach ($segments as $value => $label)
                            <option value="{{ $value }}" @selected($selectedSegment === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </p>
                @error('segment') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="model_id">
                    <i class="bi bi-envelope-fill"></i>
                    Modello mail
                </label>
                <p>
                    <select name="model_id" id="model_id">
                        <option value="">Nessun modello</option>
                        @foreach ($mailModels as $mailModel)
                            <option value="{{ $mailModel->id }}" @selected((string) old('model_id', $campaign->model_id) === (string) $mailModel->id)>
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
        </div>

        <div>
            <label class="label_c" for="schedule_window">
                <i class="bi bi-calendar-plus"></i>
                Finestra di invio
            </label>
            <p>
                <select name="schedule_window" id="schedule_window" data-schedule-window-select>
                    @foreach ($scheduleWindows as $value => $label)
                        <option value="{{ $value }}" data-label="{{ $label }}" @selected($selectedScheduleWindow === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </p>
            @error('schedule_window') <p class="error">{{ $message }}</p> @enderror
        </div>
        <p class="menu-dashboard__copy mt-3">
            Scegli una finestra: il sistema definisce l’orario reale evitando sovrapposizioni con altre campagne.
        </p>
    </section>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                            Promozione collegata
                        </h3>
                    </div>

        <label class="label_c">
            <i class="bi bi-megaphone-fill"></i>
            Selezione promozione
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

        <aside class="marketing-form-sidebar">
            <section class="order-detail__section marketing-form-preview">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <i class="bi bi-eye-fill"></i>
                        </span>
                        Anteprima campagna
                    </h3>
                </div>

                <div class="marketing-form-preview__panel">
                    <div class="marketing-form-preview__head">
                        <span class="marketing-form-preview__icon">
                            <i class="bi bi-envelope-paper-fill"></i>
                        </span>
                        <div>
                            <strong>{{ old('name', $campaign->name) ?: 'Nome campagna' }}</strong>
                            <small>{{ $segments[$selectedSegment] ?? 'Tutti i clienti con consenso marketing' }}</small>
                        </div>
                    </div>

                    <div class="marketing-form-preview__facts">
                        <div class="marketing-form-preview__fact">
                            <span>Finestra</span>
                            <strong data-preview-schedule-window>{{ $previewScheduleWindowLabel }}</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Orario reale</span>
                            <strong>Ottimizzato</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Modello</span>
                            <strong>{{ $previewMailModel?->name ?? 'Da scegliere' }}</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Promozione</span>
                            <strong data-preview-promotion-count>{{ $previewPromotions->count() }}</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Audience</span>
                            <strong data-audience-count>{{ $selectedAudienceCount }}</strong>
                        </div>
                        <div class="marketing-form-preview__fact">
                            <span>Segmento</span>
                            <strong data-audience-label>{{ $segments[$selectedSegment] ?? 'Tutti i clienti con consenso marketing' }}</strong>
                        </div>
                    </div>

                    <div class="marketing-form-preview__chips" data-preview-promotion-chips>
                        @forelse ($previewPromotions->take(6) as $previewPromotion)
                            <span>{{ $previewPromotion->name }}</span>
                        @empty
                            <span data-empty-chip>Nessuna promozione selezionata</span>
                        @endforelse
                        @if ($previewPromotions->count() > 6)
                            <span>+{{ $previewPromotions->count() - 6 }}</span>
                        @endif
                    </div>
                </div>

                <div class="marketing-form-preview__steps">
                    <div class="marketing-form-preview__step">
                        <i class="bi bi-people-fill"></i>
                        <div>
                            <strong>Preview audience</strong>
                            <small>Il conteggio usa solo clienti con consenso marketing valido.</small>
                        </div>
                    </div>
                    <div class="marketing-form-preview__step">
                        <i class="bi bi-person-check-fill"></i>
                        <div>
                            <strong>Programmazione</strong>
                            <small>Crea e programma prepara subito le assegnazioni, senza inviare email.</small>
                        </div>
                    </div>
                    <div class="marketing-form-preview__step">
                        <i class="bi bi-clock-history"></i>
                        <div>
                            <strong>Invio</strong>
                            <small>Le email partiranno dal runner/scheduler quando arriva l'orario reale.</small>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <section class="order-detail__section mt-4">
        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <button class="order-detail__contact" type="submit" name="submit_action" value="activate">
                <i class="bi bi-check2-circle"></i>
                <span>{{ $primaryActionLabel }}</span>
            </button>
            <button class="order-detail__contact" type="submit" name="submit_action" value="draft">
                <i class="bi bi-clock-history"></i>
                <span>Completa più tardi</span>
            </button>
        </div>
        <p class="menu-dashboard__copy mt-3">
            Crea e programma prepara subito i destinatari. Le email partiranno automaticamente all’orario programmato tramite scheduler.
        </p>
        @error('submit_action') <p class="error">{{ $message }}</p> @enderror
    </section>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-campaign-form]');

        if (!form) {
            return;
        }

        const audienceCounts = @json($audienceCounts ?? []);
        const segmentLabels = @json($segments ?? []);
        const segmentSelect = form.querySelector('[data-segment-select]');
        const audienceCount = form.querySelector('[data-audience-count]');
        const audienceLabel = form.querySelector('[data-audience-label]');
        const scheduleSelect = form.querySelector('[data-schedule-window-select]');
        const scheduleLabel = form.querySelector('[data-preview-schedule-window]');
        const promotionCount = form.querySelector('[data-preview-promotion-count]');
        const promotionChips = form.querySelector('[data-preview-promotion-chips]');
        const promotionInputs = form.querySelectorAll('[data-promotion-checkbox]');

        const syncAudience = () => {
            const value = segmentSelect?.value || 'all';

            if (audienceCount) {
                audienceCount.textContent = audienceCounts[value] ?? 0;
            }

            if (audienceLabel) {
                audienceLabel.textContent = segmentLabels[value] || 'Tutti i clienti con consenso marketing';
            }
        };

        const syncSchedule = () => {
            if (!scheduleLabel || !scheduleSelect) {
                return;
            }

            const selected = scheduleSelect.options[scheduleSelect.selectedIndex];
            scheduleLabel.textContent = selected?.dataset.label || selected?.textContent || 'Prima finestra disponibile';
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

        segmentSelect?.addEventListener('change', syncAudience);
        scheduleSelect?.addEventListener('change', syncSchedule);
        promotionInputs.forEach((input) => input.addEventListener('change', syncPromotions));

        syncAudience();
        syncSchedule();
        syncPromotions();
    });
</script>
