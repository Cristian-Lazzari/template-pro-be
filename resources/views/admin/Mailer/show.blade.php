@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

@php
    $status = $model->status ?: 'draft';
    $statusLabels = [
        'draft'    => __('admin.marketing.mailer.draft'),
        'active'   => __('admin.marketing.mailer.active'),
        'archived' => __('admin.marketing.mailer.archived'),
    ];
    $bodyPreview = trim(strip_tags($model->body_html ?: $model->body ?: ''));
    $usageCount  = (int) ($model->campaigns_count ?? 0) + (int) ($model->automations_count ?? 0);
    $appName     = config('configurazione.APP_NAME', config('app.name', 'R'));
    $logoLetter  = mb_strtoupper(mb_substr($appName, 0, 1));

    $bodyHtml = trim((string) ($model->body_html ?: $model->body ?: ''));
    $ending   = trim((string) ($model->ending ?: ''));
    $heading  = trim((string) ($model->heading ?: ''));
    $object   = trim((string) ($model->object ?: ''));
    $sender   = trim((string) ($model->sender ?: $appName));
@endphp

<style>
    .mailer-show-page {
        display: grid;
        gap: 18px;
    }

    .mailer-show-page * {
        min-width: 0;
    }

    .mailer-show-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(300px, .85fr);
        gap: 18px;
        align-items: start;
    }

    .mailer-show-details {
        display: grid;
        gap: 14px;
    }

    .mailer-show-fact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
    }

    .mailer-show-fact {
        display: grid;
        gap: 6px;
        padding: 14px;
        border-radius: 14px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
    }

    .mailer-show-fact > span {
        color: rgba(216, 221, 232, 0.62);
        font-size: var(--fs-100);
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .mailer-show-fact > strong {
        color: var(--c3);
        font-size: var(--fs-300);
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .mailer-show-fact > small {
        color: rgba(216, 221, 232, 0.68);
        font-size: var(--fs-100);
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .mailer-show-body {
        padding: 16px;
        border-radius: 14px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
        color: rgba(216, 221, 232, 0.84);
        font-size: var(--fs-200);
        line-height: 1.6;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .mailer-show-body p {
        margin: 0 0 .75em;
    }

    .mailer-show-body p:last-child {
        margin-bottom: 0;
    }

    .mailer-show-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: stretch;
    }

    .mailer-show-actions form {
        margin: 0;
        display: flex;
    }

    .mailer-show-actions .order-detail__contact {
        height: 100%;
        justify-content: center;
    }

    .mailer-show-danger {
        background: rgba(206, 59, 59, 0.1);
        border-color: rgba(255, 141, 141, 0.22);
    }

    /* Email preview panel */
    .mail-preview-panel {
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(216, 221, 232, 0.12);
    }

    .mail-preview-panel__label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        color: rgba(216, 221, 232, 0.72);
        font-size: var(--fs-200);
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom: 1px solid rgba(216, 221, 232, 0.08);
        background: rgba(216, 221, 232, 0.04);
    }

    .mail-preview-email {
        background: #e9f0fb;
        color: #04001d;
        font-family: Arial, sans-serif;
        padding: 28px 24px 0;
        text-align: center;
    }

    .mail-preview-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin-bottom: 20px;
        border-radius: 20px;
        background: #090333;
        color: #e9f0fb;
        font-size: 24px;
        font-weight: 900;
        box-shadow: 0 4px 14px rgba(0,0,0,.22);
    }

    .mail-preview-subject {
        margin: 0 0 10px;
        color: rgba(4, 0, 29, .52);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .mail-preview-heading {
        margin: 0 0 18px;
        color: #04001d;
        font-size: 22px;
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .mail-preview-heading--placeholder {
        color: rgba(4, 0, 29, .35);
        font-style: italic;
        font-weight: 600;
    }

    .mail-preview-body {
        margin: 0 0 20px;
        color: #04001d;
        font-size: 15px;
        line-height: 1.6;
        text-align: left;
        overflow-wrap: anywhere;
    }

    .mail-preview-body--placeholder {
        color: rgba(4, 0, 29, .3);
        font-style: italic;
    }

    .mail-preview-cta {
        display: inline-flex;
        margin: 0 auto 22px;
        padding: 12px 22px;
        border-radius: 10px;
        background: #04001d;
        color: #e9f0fb;
        font-size: 15px;
        font-weight: 900;
        text-decoration: none;
    }

    .mail-preview-ending {
        margin: 0 0 22px;
        color: #04001d;
        font-size: 16px;
        line-height: 1.5;
        text-align: center;
        overflow-wrap: anywhere;
    }

    .mail-preview-sender {
        margin: 0 0 24px;
        text-align: left;
    }

    .mail-preview-sender strong {
        display: block;
        color: #04001d;
        font-size: 15px;
        font-weight: 900;
    }

    .mail-preview-sender small {
        color: rgba(4, 0, 29, .55);
        font-size: 12px;
        font-style: italic;
    }

    .mail-preview-footer {
        margin: 0 -24px;
        padding: 14px 24px;
        background: #090333;
        color: rgba(255, 255, 255, .65);
        font-size: 11px;
        text-align: center;
        line-height: 1.5;
    }

    @media (max-width: 1050px) {
        .mailer-show-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dash_page mailer-show-page">
    @include('admin.Marketing.partials.index-style')

    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => __('admin.marketing.mailer.plural'), 'url' => route('admin.customers.mail_models.index')],
            ['label' => $model->name],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <x-icon name="file-earmark-richtext-fill" />
                </span>
                <strong>{{ __('admin.marketing.mailer.mail_model') }}</strong>
                @include('admin.Marketing.partials.status-pill', [
                    'status' => $status,
                    'label'  => $statusLabels[$status] ?? ucfirst($status),
                ])
            </div>

            <h1 class="menu-dashboard__title">{{ $model->name }}</h1>
            @if ($object !== '')
                <p>{{ $object }}</p>
            @endif
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <div class="mailer-show-actions">
                <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.edit', $model->id) }}">
                    <x-icon name="pencil-square" />
                    <span>{{ __('admin.common.edit') }}</span>
                </a>
                <form action="{{ route('admin.customers.mail_models.delete', $model->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="order-detail__contact mailer-show-danger" type="submit">
                        <x-icon name="trash-fill" />
                        <span>{{ __('admin.common.delete') }}</span>
                    </button>
                </form>
                <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.index') }}">
                    <x-icon name="arrow-left-circle-fill" />
                    <span>{{ __('admin.common.back') }}</span>
                </a>
            </div>
        </div>
    </header>

    <div class="mailer-show-grid">
        {{-- Colonna sinistra: dati del modello --}}
        <div class="mailer-show-details">
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="card-text" />
                        </span>
                        {{ __('admin.marketing.mailer.model_info') }}
                    </h3>
                </div>

                <div class="mailer-show-fact-grid">
                    <div class="mailer-show-fact">
                        <span>{{ __('admin.marketing.mailer.status') }}</span>
                        <strong>{{ $statusLabels[$status] ?? ucfirst($status) }}</strong>
                    </div>

                    <div class="mailer-show-fact">
                        <span>{{ __('admin.marketing.mailer.sender') }}</span>
                        <strong>{{ $sender ?: '-' }}</strong>
                    </div>

                    <div class="mailer-show-fact">
                        <span>{{ __('admin.marketing.mailer.usage_count') }}</span>
                        <strong>{{ $usageCount }}</strong>
                        <small>
                            {{ __('admin.marketing.mailer.campaigns_count', ['count' => $model->campaigns_count ?? 0]) }}
                            &nbsp;·&nbsp;
                            {{ __('admin.marketing.mailer.automations_count', ['count' => $model->automations_count ?? 0]) }}
                        </small>
                    </div>

                    <div class="mailer-show-fact">
                        <span>{{ __('admin.marketing.mailer.updated_at', ['date' => '']) }}</span>
                        <strong>{{ $model->updated_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                    </div>
                </div>
            </section>

            @if ($bodyHtml !== '')
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="body-text" />
                            </span>
                            {{ __('admin.marketing.mailer.html_body') }}
                        </h3>
                    </div>
                    <div class="mailer-show-body">
                        {!! $bodyHtml !!}
                    </div>
                </section>
            @endif

            @if ($ending !== '')
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="chat-quote-fill" />
                            </span>
                            {{ __('admin.marketing.mailer.ending') }}
                        </h3>
                    </div>
                    <p class="menu-dashboard__copy">{{ $ending }}</p>
                </section>
            @endif

            @if ($model->img_1 || $model->img_2)
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="image-fill" />
                            </span>
                            {{ __('admin.marketing.mailer.top_image') }} / {{ __('admin.marketing.mailer.bottom_image') }}
                        </h3>
                    </div>
                    <div class="mailer-show-fact-grid">
                        @if ($model->img_1)
                            <div class="mailer-show-fact">
                                <span>{{ __('admin.marketing.mailer.top_image') }}</span>
                                <strong>{{ basename($model->img_1) }}</strong>
                            </div>
                        @endif
                        @if ($model->img_2)
                            <div class="mailer-show-fact">
                                <span>{{ __('admin.marketing.mailer.bottom_image') }}</span>
                                <strong>{{ basename($model->img_2) }}</strong>
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        </div>

        {{-- Colonna destra: anteprima email --}}
        <aside>
            <div class="mail-preview-panel">
                <div class="mail-preview-panel__label">
                    <x-icon name="eye-fill" />
                    {{ __('admin.marketing.mailer.structure_preview') }}
                </div>

                <div class="mail-preview-email">
                    <span class="mail-preview-logo">{{ $logoLetter }}</span>

                    @if ($object !== '')
                        <p class="mail-preview-subject">{{ $object }}</p>
                    @endif

                    @if ($heading !== '')
                        <h2 class="mail-preview-heading">{{ $heading }}</h2>
                    @else
                        <h2 class="mail-preview-heading mail-preview-heading--placeholder">{{ __('admin.marketing.mailer.preview_heading') }}</h2>
                    @endif

                    @if ($bodyHtml !== '')
                        <div class="mail-preview-body">{!! $bodyHtml !!}</div>
                    @else
                        <div class="mail-preview-body mail-preview-body--placeholder">{{ __('admin.marketing.mailer.preview_body') }}</div>
                    @endif

                    <span class="mail-preview-cta">{{ __('admin.emails.marketing.discover_promotion') }}</span>

                    @if ($ending !== '')
                        <p class="mail-preview-ending">{{ $ending }}</p>
                    @endif

                    <div class="mail-preview-sender">
                        <strong>{{ $sender }}</strong>
                        <small>{{ now()->translatedFormat('l j F Y') }}</small>
                    </div>

                    <div class="mail-preview-footer">
                        {{ __('admin.marketing.mailer.preview_footer') }}
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

@endsection
