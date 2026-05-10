@php
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
    $selectedConsentBasis = \App\Models\Campaign::normalizeConsentBasis(old('consent_basis', $campaign->consent_basis));
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

    .campaign-preview-panel {
        gap: 12px;
        padding: 14px;
        border-radius: 8px;
    }

    .campaign-preview-list {
        display: grid;
        gap: 12px;
    }

    .campaign-preview-item {
        display: grid;
        gap: 4px;
        min-width: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(216, 221, 232, 0.1);
    }

    .campaign-preview-item:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .campaign-preview-item > span,
    .campaign-preview-item small {
        color: rgba(216, 221, 232, 0.66);
        font-size: var(--fs-100);
        font-weight: 800;
        line-height: 1.35;
        text-transform: uppercase;
        overflow-wrap: anywhere;
    }

    .campaign-preview-item strong {
        color: var(--c3);
        font-size: var(--fs-300);
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .campaign-preview-badge {
        display: inline-flex;
        width: fit-content;
        max-width: 100%;
        padding: 6px 9px;
        border-radius: 999px;
        border: 1px solid rgba(14, 183, 146, 0.28);
        background: rgba(14, 183, 146, 0.12);
        color: rgba(142, 246, 219, 0.96) !important;
    }

    .campaign-preview-badge--muted {
        border-color: rgba(98, 166, 255, 0.24);
        background: rgba(98, 166, 255, 0.1);
        color: rgba(216, 221, 232, 0.92) !important;
    }

    .campaign-preview-warning {
        color: #ffb4a8 !important;
    }

    .campaign-preview-warning.is-hidden {
        display: none;
    }

    .campaign-preview-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 4px;
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

    <div class="marketing-form-grid">
        <div class="marketing-form-main">
    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-card-text"></i>
                </span>
                Informazioni campagna
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

        <div class="mt-3">
            <label class="label_c" for="consent_basis">
                <i class="bi bi-shield-check"></i>
                Tipo invio
            </label>
            <p>
                <select
                    name="consent_basis"
                    id="consent_basis"
                    data-consent-basis-select
                    data-initial-value="{{ $selectedConsentBasis }}"
                >
                    @foreach (($consentBasisOptions ?? \App\Models\Campaign::consentBasisOptions()) as $value => $label)
                        <option value="{{ $value }}" data-preview-label="{{ $consentBasisPreviewLabels[$value] ?? $label }}" @selected($selectedConsentBasis === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </p>
            @error('consent_basis') <p class="error">{{ $message }}</p> @enderror
            @error('channel') <p class="error">{{ $message }}</p> @enderror
            @if ($selectedConsentBasis === \App\Models\Campaign::CONSENT_BASIS_WHATSAPP_MARKETING)
                <p class="menu-dashboard__copy mt-2">Canale predisposto, invio non ancora attivo.</p>
            @endif
        </div>
    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-people-fill"></i>
                </span>
                Segmento e modello mail
            </h3>
        </div>

        <div class="split">
            <div>
                <label class="label_c" for="segment">
                    <i class="bi bi-people-fill"></i>
                    Segmento
                </label>
                <p>
                    <select name="segment" id="segment" data-segment-select data-initial-value="{{ $selectedSegment }}">
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
        </div>

    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-calendar-plus"></i>
                </span>
                Programmazione
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
        </div>
        <p class="menu-dashboard__copy mt-3">Le email partiranno automaticamente nella finestra programmata tramite il runner marketing.</p>
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
                    <div class="marketing-form-preview__head">
                        <span class="marketing-form-preview__icon">
                            <i class="bi bi-envelope-paper-fill"></i>
                        </span>
                        <div>
                            <strong data-preview-campaign-name>{{ old('name', $campaign->name) ?: 'Nome campagna' }}</strong>
                        </div>
                    </div>

                    <div class="campaign-preview-list">
                        <div class="campaign-preview-item">
                            <span>Stato</span>
                            <strong class="campaign-preview-badge">{{ $statusPreviewLabel }}</strong>
                        </div>

                        <div class="campaign-preview-item">
                            <span>Tipo invio</span>
                            <strong class="campaign-preview-badge campaign-preview-badge--muted" data-preview-consent-basis>{{ $selectedConsentBasisLabel }}</strong>
                        </div>

                        <div class="campaign-preview-item">
                            <span>Segmento</span>
                            <strong data-preview-segment-label>{{ $segments[$selectedSegment] ?? 'Da selezionare' }}</strong>
                        </div>

                        <div class="campaign-preview-item">
                            <span>Contatti disponibili</span>
                            <strong data-preview-availability-count>
                                Disponibili: {{ $formatAudienceNumber($selectedAudienceAvailability['eligible'] ?? 0) }} / {{ $formatAudienceNumber($selectedAudienceAvailability['total'] ?? 0) }}
                            </strong>
                            <small data-preview-availability-context>{{ $selectedAudienceAvailability['total_label'] ?? '' }}</small>
                        </div>

                        <div class="campaign-preview-item">
                            <span>Audience stimata</span>
                            <strong data-preview-audience-count>
                                Assegnabili stimati: {{ $formatAudienceNumber($selectedAudienceEstimate) }}
                            </strong>
                            <small
                                class="campaign-preview-warning{{ $selectedAudienceEstimate === 0 ? '' : ' is-hidden' }}"
                                data-preview-audience-warning
                            >Nessun contatto disponibile con questa combinazione.</small>
                        </div>

                        <div class="campaign-preview-item">
                            <span>Modello mail</span>
                            <strong data-preview-mail-model>{{ $previewMailModel?->name ?? 'Da selezionare' }}</strong>
                        </div>

                        <div class="campaign-preview-item">
                            <span>Promozioni collegate</span>
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

                        <div class="campaign-preview-item">
                            <span>Programmazione</span>
                            <strong data-preview-schedule-window>{{ $previewScheduleWindowLabel }}</strong>
                            <small data-preview-scheduled-at data-initial-label="{{ $previewScheduledAtLabel }}">{{ $previewScheduledAtLabel }}</small>
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
        const form = document.querySelector('[data-campaign-form]');

        if (!form) {
            return;
        }

        const segmentLabels = @json($segments ?? []);
        const audienceAvailability = @json($audienceAvailability ?? []);
        const audienceMatrix = @json($audienceMatrix ?? []);
        const nameInput = form.querySelector('#name');
        const previewName = form.querySelector('[data-preview-campaign-name]');
        const consentSelect = form.querySelector('[data-consent-basis-select]');
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

        const selectedOption = (select) => select?.options[select.selectedIndex] || null;
        const numberFormatter = new Intl.NumberFormat('it-IT');
        const formatNumber = (value) => numberFormatter.format(Number(value || 0));

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

            previewName.textContent = nameInput.value.trim() || 'Nome campagna';
        };

        const syncConsent = () => {
            if (!consentLabel || !consentSelect) {
                return;
            }

            const option = selectedOption(consentSelect);
            consentLabel.textContent = option?.dataset.previewLabel || option?.textContent?.trim() || 'Email marketing con consenso esplicito';
        };

        const syncSegment = () => {
            const value = segmentSelect?.value || 'all';

            if (segmentLabel) {
                segmentLabel.textContent = segmentLabels[value] || 'Da selezionare';
            }
        };

        const syncAudienceState = () => {
            const consentBasis = consentSelect?.value || 'explicit_email_marketing';
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
                availabilityCount.textContent = `Disponibili: ${formatNumber(availability.eligible)} / ${formatNumber(availability.total)}`;
            }

            if (availabilityContext) {
                availabilityContext.textContent = availability.total_label || '';
            }

            if (!audienceCount) {
                return;
            }

            audienceCount.textContent = `Assegnabili stimati: ${formatNumber(estimated)}`;

            if (audienceWarning) {
                audienceWarning.classList.toggle('is-hidden', estimated !== 0);
            }
        };

        const syncMailModel = () => {
            if (!mailModelLabel || !mailModelSelect) {
                return;
            }

            const option = selectedOption(mailModelSelect);
            mailModelLabel.textContent = option?.dataset.previewLabel || option?.textContent?.trim() || 'Da selezionare';
        };

        const syncSchedule = () => {
            if (scheduleLabel && scheduleSelect) {
                const option = selectedOption(scheduleSelect);
                scheduleLabel.textContent = option?.dataset.label || option?.textContent?.trim() || 'Prima finestra disponibile';
            }

            if (!scheduledAtLabel) {
                return;
            }

            if (!scheduledAtInput) {
                scheduledAtLabel.textContent = scheduleSelect?.value === scheduleSelect?.dataset.initialValue
                    ? (scheduledAtLabel.dataset.initialLabel || scheduledAtLabel.textContent)
                    : (scheduleSelect?.value === 'next_available' ? 'Prima finestra disponibile' : 'Definita dalla finestra scelta');
                return;
            }

            const formattedDate = formatDateTime(scheduledAtInput?.value || '');

            if (formattedDate) {
                scheduledAtLabel.textContent = formattedDate;
                return;
            }

            scheduledAtLabel.textContent = scheduleSelect?.value === 'next_available'
                ? 'Prima finestra disponibile'
                : 'Definita dalla finestra scelta';
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

        nameInput?.addEventListener('input', syncName);
        consentSelect?.addEventListener('change', () => {
            syncConsent();
            syncAudienceState();
        });
        segmentSelect?.addEventListener('change', () => {
            syncSegment();
            syncAudienceState();
        });
        mailModelSelect?.addEventListener('change', syncMailModel);
        scheduleSelect?.addEventListener('change', syncSchedule);
        scheduledAtInput?.addEventListener('input', syncSchedule);
        promotionInputs.forEach((input) => input.addEventListener('change', syncPromotions));

        syncName();
        syncConsent();
        syncSegment();
        syncAudienceState();
        syncMailModel();
        syncSchedule();
        syncPromotions();
    });
</script>
