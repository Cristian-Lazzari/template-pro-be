@extends('layouts.base')

@section('contents')
@php
    $profileSettingsExpanded = session()->hasOldInput();
    $filters = $filters ?? ['search' => '', 'type' => 'all', 'segment' => ''];
    $selectedType = $filters['type'] ?? 'all';
    $selectedSegment = $filters['segment'] ?? '';
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

    .customer-page__alerts,
    .customer-page__hero,
    .customer-page__settings,
    .customer-page__toolbar-shell,
    .customer-page__mail-models,
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
    .customer-page__mail-model-grid,
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
        grid-template-columns: minmax(0, 1.6fr) repeat(2, minmax(220px, .7fr));
        gap: 10px;
        align-items: center;
    }

    .customer-page__field,
    .question-item__field,
    .customer-page__filter {
        display: grid;
        gap: 10px;
    }

    .customer-page__toolbar-form {
        display: grid;
        gap: 0;
    }

    .customer-page__filter {
        grid-template-columns: auto minmax(0, 1fr);
        align-items: center;
        gap: 0;
        min-height: 44px;
        padding: 0 10px 0 0;
        border-radius: 14px;
        border: 1px solid rgba(216, 221, 232, 0.16);
        background: rgba(9, 3, 51, 0.56);
        overflow: hidden;
    }

    .customer-page__filter-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        color: rgba(216, 221, 232, 0.72);
    }

    .customer-page__filter-icon i {
        font-size: var(--fs-200);
        line-height: 1;
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

    .customer-page__filter input,
    .customer-page__filter select {
        width: 100%;
        min-height: 42px;
        padding: 0 12px 0 0;
        border: 0;
        background: transparent;
        color: var(--c3);
        box-shadow: none;
    }

    .customer-page__filter select {
        appearance: none;
        -webkit-appearance: none;
    }

    .customer-page__filter input:focus,
    .customer-page__filter select:focus {
        outline: none;
    }

    .customer-page__field textarea {
        min-height: 120px;
        resize: vertical;
    }

    .customer-page__field input::placeholder {
        color: rgba(216, 221, 232, 0.48);
    }

    .customer-page__filter input::placeholder {
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

    .customer-page__alerts {
        display: grid;
        gap: 12px;
    }

    .customer-page__alert {
        margin: 0;
        border-radius: 18px;
        border: 1px solid rgba(14, 183, 146, 0.28);
        background: rgba(14, 183, 146, 0.12);
        color: var(--c3);
    }

    .customer-page__mail-model-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .customer-page__mail-model {
        display: grid;
        gap: 16px;
        padding: 18px;
        border-radius: 18px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
    }

    .customer-page__mail-model-head,
    .customer-page__mail-model-copy,
    .customer-page__mail-model-body,
    .customer-page__mail-model-footer,
    .customer-page__mail-model-actions {
        display: grid;
        gap: 10px;
    }

    .customer-page__mail-model-title {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
    }

    .customer-page__mail-model-title h3,
    .customer-page__mail-model-body h4 {
        margin: 0;
        color: var(--c3);
    }

    .customer-page__mail-model-title h3 {
        font-size: var(--fs-400);
        line-height: 1.15;
    }

    .customer-page__mail-model-body h4 {
        font-size: var(--fs-500);
        line-height: 1.1;
    }

    .customer-page__mail-model-meta,
    .customer-page__mail-model-body p,
    .customer-page__mail-model-footer p {
        margin: 0;
        color: rgba(216, 221, 232, 0.78);
        line-height: 1.65;
    }

    .customer-page__mail-model-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.06);
        color: rgba(216, 221, 232, 0.88);
        font-size: var(--fs-200);
        font-weight: 700;
    }

    .customer-page__mail-model-images {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .customer-page__mail-model-image {
        width: 100%;
        min-height: 160px;
        object-fit: cover;
        border-radius: 16px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.36);
    }

    .customer-page__mail-model-actions {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .customer-page__mail-model-actions form,
    .customer-page__mail-model-actions button {
        width: 100%;
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

    .customer-card__metrics {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .customer-card__score {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.06);
        color: var(--c3);
        font-size: var(--fs-200);
        font-weight: 700;
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

    .customer-page__pagination {
        margin-top: 18px;
    }

    .customer-page__pagination nav {
        display: flex;
        justify-content: center;
    }

    .customer-page__pagination .pagination {
        margin-bottom: 0;
    }

    .customer-page__toolbar-shell.is-loading {
        opacity: .78;
        transition: opacity .18s ease;
    }

    @media (max-width: 1120px) {
        .customer-page__field-grid,
        .customer-page__toolbar-grid,
        .customer-page__mail-model-grid,
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
        .customer-page__mail-model-images,
        .question-item__fields,
        .question-item__footer {
            grid-template-columns: 1fr;
        }

        .customer-card__compact,
        .customer-page__mail-model-actions,
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
    @if (session('success'))
        <div class="alert alert-success customer-page__alert" role="status">
            {{ session('success') }}
        </div>
    @endif

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

                <a href="#customerMailModels" class="order-detail__contact">
                    <x-icon name="envelope-paper-fill" />
                    <span>Apri modelli mail</span>
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

    <section id="customerMailModels" class="order-detail customer-page__mail-models">
        <div class="order-detail__body">
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="envelope-paper-fill" />
                        </span>
                        Modelli mail
                    </h3>

                    <a class="customer-page__button" href="{{ route('admin.customers.mail_models.create') }}">
                        <x-icon name="plus-circle-fill" />
                        <span>Nuovo modello</span>
                    </a>
                </div>

                <div class="customer-page__actions" style="margin-bottom: 14px;">
                    <div class="customer-page__settings-copy">
                        <p>
                            I template restano disponibili qui dentro, insieme ai profili cliente.
                            La parte email marketing e le campagne separate non sono piu esposte nel pannello.
                        </p>
                    </div>
                </div>

                @if ($mailModels->isNotEmpty())
                    <div class="customer-page__mail-model-grid">
                        @foreach ($mailModels as $mailModel)
                            @php
                                $bodyParts = array_values(array_filter(
                                    explode('/*/', (string) $mailModel->body),
                                    fn ($part) => trim($part) !== ''
                                ));
                            @endphp

                            <article class="customer-page__mail-model">
                                <div class="customer-page__mail-model-head">
                                    <div class="customer-page__mail-model-title">
                                        <h3>{{ $mailModel->name }}</h3>
                                        <span class="customer-page__mail-model-label">{{ $mailModel->sender }}</span>
                                    </div>

                                    <p class="customer-page__mail-model-meta">
                                        <strong>Oggetto:</strong> {{ $mailModel->object }}
                                    </p>
                                </div>

                                <div class="customer-page__mail-model-body">
                                    <h4>{{ $mailModel->heading }}</h4>

                                    @foreach ($bodyParts as $part)
                                        <p>{!! nl2br(e(str_replace('\n', "\n", $part))) !!}</p>
                                    @endforeach
                                </div>

                                @if ($mailModel->img_1 || $mailModel->img_2)
                                    <div class="customer-page__mail-model-images">
                                        @if ($mailModel->img_1)
                                            <img class="customer-page__mail-model-image" src="{{ asset('public/storage/' . $mailModel->img_1) }}" alt="{{ $mailModel->name }} immagine 1">
                                        @endif

                                        @if ($mailModel->img_2)
                                            <img class="customer-page__mail-model-image" src="{{ asset('public/storage/' . $mailModel->img_2) }}" alt="{{ $mailModel->name }} immagine 2">
                                        @endif
                                    </div>
                                @endif

                                <div class="customer-page__mail-model-footer">
                                    <p>{!! nl2br(e(str_replace('\n', "\n", $mailModel->ending))) !!}</p>
                                </div>

                                <div class="customer-page__mail-model-actions">
                                    <a class="customer-page__button--ghost" href="{{ route('admin.customers.mail_models.edit', $mailModel->id) }}">
                                        <x-icon name="pencil-square" />
                                        <span>Modifica</span>
                                    </a>

                                    <button type="button" class="customer-page__button--ghost" data-bs-toggle="modal" data-bs-target="#deleteMailModel{{ $mailModel->id }}">
                                        <x-icon name="trash3-fill" />
                                        <span>Elimina</span>
                                    </button>
                                </div>
                            </article>

                            <div class="modal fade" id="deleteMailModel{{ $mailModel->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteMailModel{{ $mailModel->id }}Label" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered creation">
                                    <form action="{{ route('admin.customers.mail_models.delete', $mailModel->id) }}" class="w-100" method="post">
                                        @method('delete')
                                        @csrf
                                        <x-dashboard.action-modal
                                            title-id="deleteMailModel{{ $mailModel->id }}Label"
                                            title="Sicuro di voler eliminare il modello?"
                                            eyebrow="Template mail"
                                            tone="danger"
                                            :subject="$mailModel->name"
                                            description="Una volta eliminato, questo modello non potra piu essere recuperato."
                                        >
                                            <p class="dashboard-action-modal__hint">Controlla il nome del template prima di confermare.</p>

                                            <x-slot name="footer">
                                                <button class="my_btn_2" type="submit">
                                                    Elimina definitivamente
                                                </button>
                                            </x-slot>
                                        </x-dashboard.action-modal>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="customer-page__empty">
                        <div>
                            <span class="customer-page__empty-icon" aria-hidden="true">
                                <x-icon name="envelope-paper-fill" />
                            </span>
                            <strong>Nessun modello mail disponibile</strong>
                            <p>Crea il primo template direttamente da questa pagina clienti.</p>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </section>

    <section class="order-detail customer-page__toolbar-shell">
        <form id="customerToolbarForm" class="customer-page__toolbar-form" method="GET" action="{{ route('admin.customers.index') }}">
            <div class="customer-page__toolbar-grid">
                <label class="customer-page__filter">
                    <span class="customer-page__filter-icon" aria-hidden="true">
                        <x-icon name="search" />
                    </span>
                    <input
                        id="customerSearch"
                        name="search"
                        type="text"
                        value="{{ $filters['search'] }}"
                        aria-label="{{ __('admin.Cerca_cliente') }}"
                        placeholder="{{ __('admin.Cerca_cliente') }}"
                    >
                </label>

                <label class="customer-page__filter">
                    <span class="customer-page__filter-icon" aria-hidden="true">
                        <x-icon name="funnel-fill" />
                    </span>
                    <select id="customerType" name="type" aria-label="Vista rapida">
                        <option value="all" @selected($selectedType === 'all')>{{ __('admin.Tutti') }}</option>
                        <option value="orders" @selected($selectedType === 'orders')>{{ __('admin.Con_ordini') }}</option>
                        <option value="reservations" @selected($selectedType === 'reservations')>{{ __('admin.Con_prenotazioni') }}</option>
                        <option value="both" @selected($selectedType === 'both')>{{ __('admin.Ordini_e_prenotazioni') }}</option>
                    </select>
                </label>

                <label class="customer-page__filter">
                    <span class="customer-page__filter-icon" aria-hidden="true">
                        <x-icon name="bullseye" />
                    </span>
                    <select id="customerSegment" name="segment" aria-label="Segmento CRM">
                        <option value="">Tutti i segmenti</option>
                        @foreach ($segmentOptions as $segmentKey => $segment)
                            <option value="{{ $segmentKey }}" @selected($selectedSegment === $segmentKey)>
                                {{ $segment['label'] }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
        </form>
    </section>

    <div id="customerResults">
        @include('admin.Customers.partials.results', ['customers' => $customers])
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toolbarShell = document.querySelector('.customer-page__toolbar-shell');
        const toolbarForm = document.getElementById('customerToolbarForm');
        const searchInput = document.getElementById('customerSearch');
        const typeSelect = document.getElementById('customerType');
        const segmentSelect = document.getElementById('customerSegment');
        const resultsContainer = document.getElementById('customerResults');
        const questionList = document.getElementById('customerQuestionList');
        const addQuestionButton = document.getElementById('addCustomerQuestion');
        const profileSettingsToggle = document.getElementById('customerProfileSettingsToggle');
        const profileSettingsCollapse = document.getElementById('customerProfileSettingsContent');
        const profileSettingsAnchors = Array.from(document.querySelectorAll('[href="#customerProfileSettings"]'));
        let filterTimeout = null;
        let activeRequest = null;

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

        function buildToolbarUrl(pageUrl) {
            const url = new URL(pageUrl || toolbarForm.action, window.location.origin);
            const search = (searchInput.value || '').trim();
            const type = typeSelect.value || 'all';
            const segment = segmentSelect.value || '';

            url.searchParams.delete('page');
            search ? url.searchParams.set('search', search) : url.searchParams.delete('search');
            type !== 'all' ? url.searchParams.set('type', type) : url.searchParams.delete('type');
            segment !== '' ? url.searchParams.set('segment', segment) : url.searchParams.delete('segment');

            return url;
        }

        async function fetchCustomers(pageUrl) {
            const url = buildToolbarUrl(pageUrl);

            if (pageUrl) {
                const requestedPage = new URL(pageUrl, window.location.origin).searchParams.get('page');
                if (requestedPage) {
                    url.searchParams.set('page', requestedPage);
                }
            }

            if (activeRequest) {
                activeRequest.abort();
            }

            activeRequest = new AbortController();
            toolbarShell?.classList.add('is-loading');

            try {
                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: activeRequest.signal,
                });

                if (!response.ok) {
                    throw new Error('Unable to load customers');
                }

                resultsContainer.innerHTML = await response.text();
                history.replaceState({}, '', url.toString());
            } catch (error) {
                if (error.name !== 'AbortError') {
                    window.location.assign(url.toString());
                }
            } finally {
                toolbarShell?.classList.remove('is-loading');
            }
        }

        function queueCustomerFetch() {
            window.clearTimeout(filterTimeout);
            filterTimeout = window.setTimeout(function () {
                fetchCustomers();
            }, 180);
        }

        toolbarForm?.addEventListener('submit', function (event) {
            event.preventDefault();
            fetchCustomers();
        });

        searchInput?.addEventListener('input', queueCustomerFetch);
        typeSelect?.addEventListener('change', function () {
            fetchCustomers();
        });
        segmentSelect?.addEventListener('change', function () {
            fetchCustomers();
        });

        resultsContainer?.addEventListener('click', function (event) {
            const paginationLink = event.target.closest('.pagination a');
            if (!paginationLink) {
                return;
            }

            event.preventDefault();
            fetchCustomers(paginationLink.href);
        });
    });
</script>
@endsection
