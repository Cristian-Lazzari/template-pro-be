@extends('layouts.base')

@section('contents')
@php
    $accountLabels = [
        'guest' => 'Ospite',
        'registered' => 'Registrato',
    ];

    $marketingLabels = [
        'no_marketing' => 'No marketing',
        'soft_marketing' => 'Soft marketing',
        'full' => 'Full marketing',
    ];

    $profileSettingsExpanded = session()->hasOldInput();
@endphp

<style>
    .customer-page.customer-page--detail {
        display: grid;
        gap: 22px;
        width: 100%;
    }
    .customer-page__toolbar-shell{
        background:
        radial-gradient(circle at top left, rgba(14, 183, 146, 0.2), transparent 22%),
        radial-gradient(circle at 85% 25%, rgba(216, 221, 232, 0.12), transparent 24%),
        linear-gradient(145deg, rgba(216, 221, 232, 0.1), rgba(216, 221, 232, 0.03)),
        rgba(9, 3, 51, 0.84);
        position: sticky;
        top: 10px;
    }

    .customer-page__hero,
    .customer-page__settings,
    .customer-page__toolbar-shell,
    .customer-page__empty,
    .customer-card {
        width: 100%;
    }

    .customer-page__hero-copy,
    .customer-page__hero-title,
    .customer-page__summary-side,
    .customer-page__toolbar-badges,
    .customer-page__settings-copy,
    .customer-page__actions,
    .customer-card__identity,
    .customer-card__title {
        display: grid;
        gap: 12px;
    }

    .customer-page__hero-title h1,
    .customer-card__title h2 {
        margin: 0;
        color: var(--c3);
    }

    .customer-page__hero-title h1 {
        font-size: var(--fs-700);
        line-height: 1.02;
    }

    .customer-card__title h2 {
        font-size: var(--fs-400);
        line-height: 1.08;
    }

    .customer-page__hero-title p,
    .customer-page__summary-card small,
    .customer-page__field > span,
    .customer-page__field p,
    .question-item__field > span,
    .customer-page__actions p,
    .customer-card__title p,
    .customer-page__empty p {
        margin: 0;
        color: rgba(216, 221, 232, 0.78);
        line-height: 1.65;
    }

    .customer-page__summary-grid,
    .customer-page__field-grid,
    .customer-page__toolbar-grid,
    .customer-page__list,
    .question-list {
        display: grid;
        gap: 14px;
    }

    .customer-page__summary-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .customer-page__summary-card,
    .customer-page__mini-card {
        border-radius: 18px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
    }

    .customer-page__summary-card,
    .customer-page__mini-card {
        padding: 16px;
    }

    .customer-page__summary-card span,
    .customer-page__mini-card span,
    .customer-page__field > span,
    .question-item__field > span {
        display: block;
        font-size: var(--fs-200);
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(216, 221, 232, 0.64);
    }

    .customer-page__summary-card strong,
    .customer-page__mini-card strong {
        display: block;
        margin-top: 10px;
        color: var(--c3);
        font-size: var(--fs-400);
        line-height: 1.05;
    }

    .customer-page__summary-side,
    .customer-page__toolbar-badges {
        align-content: start;
        justify-items: end;
    }

    .customer-page__button,
    .customer-page__button--ghost {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 46px;
        padding: 0 16px;
        border-radius: 16px;
        border: 1px solid rgba(216, 221, 232, 0.14);
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: transform .18s ease, border-color .18s ease, background .18s ease;
    }

    .customer-page__button:hover,
    .customer-page__button--ghost:hover {
        transform: translateY(-1px);
    }

    .customer-page__button {
        background: linear-gradient(135deg, rgba(14, 183, 146, 0.18), rgba(216, 221, 232, 0.08));
        border-color: rgba(14, 183, 146, 0.28);
        color: var(--c3);
    }

    .customer-page__button--ghost {
        background: rgba(216, 221, 232, 0.06);
        color: rgba(216, 221, 232, 0.94);
    }

    .customer-page__button i,
    .customer-page__button--ghost i {
        font-size: var(--fs-200);
        line-height: 1;
    }

    .customer-page__settings-toggle-shell,
    .customer-page__settings-body {
        display: grid;
        gap: 12px;
    }

    .customer-page__settings-toggle-shell {
        align-content: start;
        justify-items: end;
    }

    .customer-page__settings-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 46px;
        padding: 0 16px;
        border-radius: 16px;
        border: 1px solid rgba(216, 221, 232, 0.14);
        background: rgba(216, 221, 232, 0.06);
        color: rgba(216, 221, 232, 0.94);
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: transform .18s ease, border-color .18s ease, background .18s ease;
    }

    .customer-page__settings-toggle:hover {
        transform: translateY(-1px);
        border-color: rgba(14, 183, 146, 0.28);
        background: rgba(14, 183, 146, 0.12);
    }

    .customer-page__settings-toggle:focus-visible {
        outline: 2px solid rgba(142, 246, 219, 0.42);
        outline-offset: 3px;
    }

    .customer-page__settings-toggle i {
        font-size: var(--fs-100);
        line-height: 1;
        transition: transform .18s ease;
    }

    .customer-page__settings-toggle[aria-expanded="true"] {
        border-color: rgba(14, 183, 146, 0.28);
        background: rgba(14, 183, 146, 0.12);
    }

    .customer-page__settings-toggle[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    .customer-page__settings-toggle-text--open {
        display: none;
    }

    .customer-page__settings-toggle[aria-expanded="true"] .customer-page__settings-toggle-text--closed {
        display: none;
    }

    .customer-page__settings-toggle[aria-expanded="true"] .customer-page__settings-toggle-text--open {
        display: inline;
    }

    .customer-page__settings-body {
        gap: 24px;
    }

    .customer-page__field-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .customer-page__toolbar-grid {
        grid-template-columns: minmax(0, 1.6fr) minmax(240px, .7fr);
    }

    .customer-page__field,
    .question-item__field {
        display: grid;
        gap: 10px;
    }

    .customer-page__field input,
    .customer-page__field select,
    .customer-page__field textarea,
    [data-question-item] input[type="text"] {
        width: 100%;
        min-height: 48px;
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid rgba(216, 221, 232, 0.16);
        background: rgba(9, 3, 51, 0.56);
        color: var(--c3);
    }

    .customer-page__field textarea {
        min-height: 120px;
        resize: vertical;
    }

    .customer-page__field input::placeholder {
        color: rgba(216, 221, 232, 0.48);
    }

    .question-item__fields {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
        gap: 12px;
    }

    .question-item__footer {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
    }

    .customer-page__actions {
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
    }


    .customer-card.order-detail {
        gap: 12px;
        padding: 16px;
    }

    .customer-card .order-detail__header {
        gap: 12px;
    }

    .customer-card .order-detail__status {
        padding: 8px 12px;
    }

    .customer-card__pill-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-start;
    }

    .customer-card__compact {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px 12px;
        align-items: center;
    }

    .customer-card__details {
        display: grid;
        gap: 10px;
        min-width: 0;
    }

    .customer-card__meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .customer-card__summary {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: rgba(216, 221, 232, 0.74);
        font-size: var(--fs-200);
        line-height: 1.5;
    }

    .customer-card__summary strong {
        color: var(--c3);
        font-size: var(--fs-200);
        font-family: monospace;
    }

    .customer-card__action {
        justify-self: end;
    }

    .customer-card__action .customer-page__button {
        min-width: 112px;
        min-height: 40px;
        padding: 0 12px;
        border-radius: 14px;
    }

    [data-question-item] label.question-item__switch {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 58px;
        padding: 10px 12px;
        border-radius: 16px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.44);
        cursor: pointer;
    }

    .question-item__switch input {
        position: absolute;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        overflow: hidden;
        clip: rect(0 0 0 0);
        clip-path: inset(50%);
        border: 0;
        white-space: nowrap;
        appearance: none;
        -webkit-appearance: none;
    }

    [data-question-item] .question-item__switch-ui {
        display: inline-flex;
        flex: 0 0 auto;
        width: 48px;
        height: 28px;
        border-radius: 999px;
        background: rgba(216, 221, 232, 0.18);
        border: 1px solid rgba(216, 221, 232, 0.14);
        transition: background .2s ease, border-color .2s ease;
        position: relative;
    }

    [data-question-item] .question-item__switch-ui::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--c3);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.24);
        transition: transform .2s ease, background .2s ease;
    }

    .question-item__switch-copy {
        display: grid;
        gap: 2px;
    }

    .question-item__switch-copy strong {
        color: var(--c3);
        font-size: var(--fs-200);
        line-height: 1.2;
    }

    .question-item__switch-copy small {
        color: rgba(216, 221, 232, 0.72);
        line-height: 1.4;
    }

    .question-item__switch input:checked + .question-item__switch-ui {
        background: rgba(14, 183, 146, 0.28);
        border-color: rgba(14, 183, 146, 0.34);
    }

    .question-item__switch input:checked + .question-item__switch-ui::after {
        transform: translateX(20px);
        background: #8ef6db;
    }

    .question-item__switch input:focus-visible + .question-item__switch-ui {
        outline: 2px solid rgba(142, 246, 219, 0.42);
        outline-offset: 3px;
    }

    .customer-page__empty {
        display: grid;
        place-items: center;
        min-height: 220px;
        text-align: center;
    }

    .customer-page__empty strong {
        display: block;
        margin-bottom: 8px;
        color: var(--c3);
        font-size: var(--fs-400);
    }

    .customer-page__empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin-bottom: 16px;
        border-radius: 18px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.06);
        color: var(--c3);
    }

    .customer-page__empty-icon svg {
        width: 24px;
        height: 24px;
    }

    .customer-page__empty-icon i {
        font-size: var(--fs-500);
        line-height: 1;
    }

    @media (max-width: 1120px) {
        .customer-page__field-grid,
        .customer-page__toolbar-grid,
        .customer-page__actions {
            grid-template-columns: 1fr;
        }

        .customer-page__summary-side,
        .customer-page__toolbar-badges,
        .customer-page__settings-toggle-shell {
            justify-items: start;
        }
    }

    @media (max-width: 720px) {
        .customer-page__summary-grid,
        .customer-page__list,
        .question-item__fields,
        .question-item__footer {
            grid-template-columns: 1fr;
        }

        .customer-card__compact,
        .customer-page__actions {
            grid-template-columns: 1fr;
        }

        .customer-page__button,
        .customer-page__button--ghost,
        .customer-page__settings-toggle,
        .customer-card__meta-row .order-detail__contact {
            width: 100%;
        }

        .customer-card__action {
            justify-self: stretch;
        }

        .customer-card__summary {
            gap: 6px;
        }
    }
</style>

<div class="dash_page customer-page customer-page--detail">
    <section class="order-detail order-detail--active customer-page__hero">
        <header class="order-detail__header">
            <div class="customer-page__hero-copy">
                <div class="order-detail__status">
                    <span class="order-detail__status-icon order-detail__status-icon--active">
                        <x-icon name="people-fill" />
                    </span>
                    <strong>{{ __('admin.Clienti') }}</strong>
                </div>

                <div class="customer-page__hero-title">
                    <h1>{{ __('admin.Clienti') }}</h1>
                    <p>
                        Qui vedi subito chi torna spesso, chi ha gia ordinato o prenotato
                        e chi merita un attenzione in piu dopo il servizio.
                    </p>
                </div>
            </div>

            <div class="order-detail__contacts">
                <a href="#customerProfileSettings" class="order-detail__contact">
                    <x-icon name="sliders" />
                    <span>Configura profilo cliente</span>
                </a>

                <a href="#customerList" class="order-detail__contact">
                    <x-icon name="person-vcard-fill" />
                    <span>Vai ai profili</span>
                </a>
            </div>
        </header>

        <div class="customer-page__summary-grid">
            <article class="customer-page__summary-card">
                <span>{{ __('admin.Clienti') }}</span>
                <strong>{{ $stats['total'] }}</strong>
            </article>

            <article class="customer-page__summary-card">
                <span>{{ __('admin.Con_ordini') }}</span>
                <strong>{{ $stats['with_orders'] }}</strong>
            </article>

            <article class="customer-page__summary-card">
                <span>{{ __('admin.Con_prenotazioni') }}</span>
                <strong>{{ $stats['with_reservations'] }}</strong>
            </article>

            <article class="customer-page__summary-card">
                <span>{{ __('admin.Ordini_e_prenotazioni') }}</span>
                <strong>{{ $stats['with_both'] }}</strong>
            </article>
        </div>
    </section>

    <form id="customerProfileSettings" class="" method="POST" action="{{ route('admin.customers.profile_settings') }}">
        @csrf

        <div class="order-detail__summary ">
            <div class="order-detail__meta">
                <p class="order-detail__code">Profilazione cliente</p>
                <p class="order-detail__time">Consensi e questionario</p>
                <p class="order-detail__date">
                    Qui imposti i messaggi che il cliente legge quando lascia i suoi dati
                    e scegli poche domande che ti aiutano davvero a lavorare meglio, per esempio preferenze,
                    ricorrenze o esigenze da ricordare.
                </p>
            </div>

            <div class="customer-page__settings-toggle-shell">
                <button
                    type="button"
                    class="customer-page__settings-toggle"
                    id="customerProfileSettingsToggle"
                    data-bs-toggle="collapse"
                    data-bs-target="#customerProfileSettingsContent"
                    aria-expanded="{{ $profileSettingsExpanded ? 'true' : 'false' }}"
                    aria-controls="customerProfileSettingsContent"
                >
                    <span class="customer-page__settings-toggle-text customer-page__settings-toggle-text--closed">Espandi</span>
                    <span class="customer-page__settings-toggle-text customer-page__settings-toggle-text--open">Chiudi</span>
                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div id="customerProfileSettingsContent" class="mt-4 collapse{{ $profileSettingsExpanded ? ' show' : '' }}">
            <div class="customer-page__settings-body">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="chat-square-text-fill" />
                            </span>
                            Testi dei consensi
                        </h3>
                    </div>

                    <div class="customer-page__field-grid">
                        <label class="customer-page__field">
                            <span>Testo consenso marketing</span>
                            <textarea name="marketing_consent_text">{{ old('marketing_consent_text', $profileSettings['marketing_consent_text'] ?? '') }}</textarea>
                        </label>

                        <label class="customer-page__field">
                            <span>Testo consenso profilazione</span>
                            <textarea name="profiling_consent_text">{{ old('profiling_consent_text', $profileSettings['profiling_consent_text'] ?? '') }}</textarea>
                        </label>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="patch-question-fill" />
                            </span>
                            Domande personalizzate
                        </h3>
                    </div>

                    <div class="customer-page__actions" style="margin-bottom: 14px;">
                        <div class="customer-page__settings-copy">
                            
                        </div>

                        <button type="button" class="customer-page__button--ghost" id="addCustomerQuestion">Aggiungi domanda</button>
                    </div>

                    <div id="customerQuestionList" class="question-list">
                        @foreach (($profileSettings['questions'] ?? []) as $index => $question)
                            <div class="dashboard-action-modal__detail" data-question-item>
                                <input type="hidden" name="questions[{{ $index }}][key]" value="{{ $question['key'] ?? '' }}">
                                <div class="question-item__fields">
                                    <label class="question-item__field">
                                        <span>Domanda</span>
                                        <input type="text" name="questions[{{ $index }}][label]" value="{{ $question['label'] ?? '' }}">
                                    </label>

                                    <label class="question-item__field">
                                        <span>Placeholder</span>
                                        <input type="text" name="questions[{{ $index }}][placeholder]" value="{{ $question['placeholder'] ?? '' }}">
                                    </label>
                                </div>

                                <div class="question-item__footer">
                                    <label class="question-item__switch">
                                        <input type="checkbox" name="questions[{{ $index }}][required]" value="1" @checked(($question['required'] ?? false))>
                                        <span class="question-item__switch-ui" aria-hidden="true"></span>
                                        <div class="question-item__switch-copy">
                                            <strong>Da compilare sempre</strong>
                                            <small>Attivala solo se la risposta ti serve davvero.</small>
                                        </div>
                                    </label>

                                    <button type="button" class="customer-page__button--ghost" data-remove-question>Rimuovi</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <div class="customer-page__actions">
                    <button type="submit" class="customer-page__button">Salva configurazione</button>
                </div>
            </div>
        </div>
    </form>

    <section class="order-detail customer-page__toolbar-shell">
            <div class="customer-page__toolbar-grid">
                <label class="customer-page__field">
                    <span>{{ __('admin.Cerca_cliente') }}</span>
                    <input id="customerSearch" type="text" placeholder="{{ __('admin.Cerca_cliente') }}">
                </label>

                <label class="customer-page__field">
                    <span>Vista rapida</span>
                    <select id="customerType">
                        <option value="all">{{ __('admin.Tutti') }}</option>
                        <option value="orders">{{ __('admin.Con_ordini') }}</option>
                        <option value="reservations">{{ __('admin.Con_prenotazioni') }}</option>
                        <option value="both">{{ __('admin.Ordini_e_prenotazioni') }}</option>
                    </select>
                </label>
            </div>
        </section>
    </section>

    <div id="customerList" class="customer-page__list">
        @foreach ($customers as $customer)
            @php
                $displayName = trim(($customer->name ?? '') . ' ' . ($customer->surname ?? '')) ?: $customer->email;
                $accountTone = $customer->account_state === 'registered' ? 'active' : 'warning';
                $marketingTone = match ($customer->marketing_state) {
                    'full' => 'active',
                    'soft_marketing' => 'warning',
                    default => 'off',
                };
                $marketingLabel = $marketingLabels[$customer->marketing_state] ?? ucfirst((string) $customer->marketing_state);
            @endphp

            <article
                class="order-detail order-detail--{{ $accountTone }} customer-card"
                data-customer-card
                data-search="{{ $customer->search_text }}"
                data-has-orders="{{ $customer->orders_count > 0 ? 1 : 0 }}"
                data-has-reservations="{{ $customer->reservations_count > 0 ? 1 : 0 }}"
            >
                <header class="order-detail__header">
                    <div class="customer-card__identity">
                        <div class="order-detail__status">
                            <span class="order-detail__status-icon order-detail__status-icon--{{ $accountTone }}">
                                @if ($customer->account_state === 'registered')
                                    <x-icon name="person-check-fill" />
                                @else
                                    <x-icon name="person-fill" />
                                @endif
                            </span>
                            <strong>{{ $accountLabels[$customer->account_state] ?? ucfirst((string) $customer->account_state) }}</strong>
                        </div>

                        <div class="customer-card__title">
                            <h2>{{ $displayName }}</h2>
                            <p>
                                Ultimo movimento:
                                {{ $customer->last_activity_at ? $customer->last_activity_at->format('d/m/Y H:i') : '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="customer-card__pill-row">
                        <x-dashboard.state-pill :tone="$marketingTone">{{ $marketingLabel }}</x-dashboard.state-pill>
                    </div>
                </header>

                <div class="customer-card__compact">
                    <div class="customer-card__details">
                        <div class="customer-card__meta-row">
                            <a href="mailto:{{ $customer->email }}" class="order-detail__contact">
                                <x-icon name="envelope-arrow-up-fill" />
                                <span>{{ $customer->email }}</span>
                            </a>

                            @if ($customer->phone)
                                <a href="tel:{{ $customer->phone }}" class="order-detail__contact">
                                    <x-icon name="telephone-outbound-fill" />
                                    <span>{{ $customer->phone }}</span>
                                </a>
                            @endif
                        </div>

                        <p class="customer-card__summary">
                            <strong>{{ $customer->orders_count }}</strong> ordini
                            <span aria-hidden="true">•</span>
                            <strong>{{ $customer->reservations_count }}</strong> prenotazioni
                            <span aria-hidden="true">•</span>
                            <strong>{{ $customer->interactions_count }}</strong> interazioni
                        </p>
                    </div>

                    @if ($customer->detail_url)
                        <div class="customer-card__action">
                            <a class="customer-page__button" href="{{ $customer->detail_url }}">
                                <x-icon name="arrow-up-right-circle-fill" />
                                <span>Apri</span>
                            </a>
                        </div>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    <div id="customerEmpty" class="order-detail customer-page__empty" @if($customers->isNotEmpty()) hidden @endif>
        <div>
            <span class="customer-page__empty-icon" aria-hidden="true">
                <x-icon name="search" />
            </span>
            <strong>{{ __('admin.Nessun_cliente_trovato') }}</strong>
            <p>Prova con un altro nome oppure allarga il filtro per vedere piu clienti.</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('customerSearch');
        const typeSelect = document.getElementById('customerType');
        const cards = Array.from(document.querySelectorAll('[data-customer-card]'));
        const emptyState = document.getElementById('customerEmpty');
        const questionList = document.getElementById('customerQuestionList');
        const addQuestionButton = document.getElementById('addCustomerQuestion');
        const profileSettingsToggle = document.getElementById('customerProfileSettingsToggle');
        const profileSettingsCollapse = document.getElementById('customerProfileSettingsContent');
        const profileSettingsAnchors = Array.from(document.querySelectorAll('[href="#customerProfileSettings"]'));

        function questionIndex() {
            return questionList.querySelectorAll('[data-question-item]').length;
        }

        function createQuestionItem(index) {
            const wrapper = document.createElement('div');
            wrapper.className = 'dashboard-action-modal__detail';
            wrapper.dataset.questionItem = '1';
            wrapper.innerHTML = `
                <input type="hidden" name="questions[${index}][key]" value="">
                <div class="question-item__fields">
                    <label class="question-item__field">
                        <span>Domanda</span>
                        <input type="text" name="questions[${index}][label]" value="">
                    </label>
                    <label class="question-item__field">
                        <span>Placeholder</span>
                        <input type="text" name="questions[${index}][placeholder]" value="">
                    </label>
                </div>
                <div class="question-item__footer">
                    <label class="question-item__switch">
                        <input type="checkbox" name="questions[${index}][required]" value="1">
                        <span class="question-item__switch-ui" aria-hidden="true"></span>
                        <div class="question-item__switch-copy">
                            <strong>Da compilare sempre</strong>
                            <small>Attivala solo se la risposta ti serve davvero.</small>
                        </div>
                    </label>
                    <button type="button" class="customer-page__button--ghost" data-remove-question>Rimuovi</button>
                </div>
            `;

            return wrapper;
        }

        addQuestionButton?.addEventListener('click', function () {
            questionList.appendChild(createQuestionItem(questionIndex()));
        });

        questionList?.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-question]');
            if (!removeButton) {
                return;
            }

            const item = removeButton.closest('[data-question-item]');
            item?.remove();
        });

        function openProfileSettings() {
            if (!profileSettingsToggle || !profileSettingsCollapse) {
                return;
            }

            if (profileSettingsCollapse.classList.contains('show') || profileSettingsCollapse.classList.contains('collapsing')) {
                return;
            }

            profileSettingsToggle.click();
        }

        profileSettingsAnchors.forEach(function (anchor) {
            anchor.addEventListener('click', function () {
                window.requestAnimationFrame(openProfileSettings);
            });
        });

        if (window.location.hash === '#customerProfileSettings') {
            openProfileSettings();
        }

        function renderCustomers() {
            const search = (searchInput.value || '').trim().toLowerCase();
            const type = typeSelect.value;
            let visibleCount = 0;

            cards.forEach((card) => {
                const text = card.dataset.search || '';
                const hasOrders = card.dataset.hasOrders === '1';
                const hasReservations = card.dataset.hasReservations === '1';

                let typeMatch = true;
                if (type === 'orders') {
                    typeMatch = hasOrders;
                } else if (type === 'reservations') {
                    typeMatch = hasReservations;
                } else if (type === 'both') {
                    typeMatch = hasOrders && hasReservations;
                }

                const searchMatch = search === '' || text.includes(search);
                const visible = typeMatch && searchMatch;

                card.hidden = !visible;
                if (visible) {
                    visibleCount++;
                }
            });

            emptyState.hidden = visibleCount > 0;
        }

        searchInput.addEventListener('input', renderCustomers);
        typeSelect.addEventListener('change', renderCustomers);
        renderCustomers();
    });
</script>
@endsection
