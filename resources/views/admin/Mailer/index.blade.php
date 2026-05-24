@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

<style>
    .mailer-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }

    .mail-card {
        display: flex;
        flex-direction: column;
        border-radius: 16px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.04);
        overflow: hidden;
        transition: border-color .15s ease, transform .15s ease;
    }

    .mail-card:hover {
        border-color: rgba(216, 221, 232, 0.24);
        transform: translateY(-1px);
    }

    .mail-card__preview-link {
        display: flex;
        flex-direction: column;
        flex: 1;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    }

    .mail-card__email {
        flex: 1;
        padding: 20px 18px 14px;
        background: #e9f0fb;
        color: #04001d;
        font-family: Arial, sans-serif;
    }

    .mail-card__logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        margin-bottom: 14px;
        border-radius: 14px;
        background: #090333;
        color: #e9f0fb;
        font-size: 18px;
        font-weight: 900;
        box-shadow: 0 2px 8px rgba(0,0,0,.2);
    }

    .mail-card__subject {
        margin: 0 0 6px;
        color: rgba(4, 0, 29, .55);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mail-card__heading {
        margin: 0 0 10px;
        color: #04001d;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.25;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .mail-card__heading--placeholder {
        color: rgba(4, 0, 29, .35);
        font-weight: 600;
        font-style: italic;
    }

    .mail-card__body {
        color: rgba(4, 0, 29, .75);
        font-size: 12px;
        line-height: 1.55;
        margin-bottom: 14px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }

    .mail-card__body--placeholder {
        color: rgba(4, 0, 29, .3);
        font-style: italic;
    }

    .mail-card__cta {
        display: inline-flex;
        margin-bottom: 12px;
        padding: 8px 14px;
        border-radius: 8px;
        background: #04001d;
        color: #e9f0fb;
        font-size: 11px;
        font-weight: 900;
    }

    .mail-card__sender-line {
        margin: 6px 0 0;
        color: #04001d;
        font-size: 11px;
        font-weight: 700;
    }

    .mail-card__email-footer {
        padding: 8px 18px;
        background: #090333;
        color: rgba(255, 255, 255, .65);
        font-size: 10px;
        text-align: center;
    }

    .mail-card__bottom {
        padding: 12px 14px;
        border-top: 1px solid rgba(216, 221, 232, 0.1);
        display: grid;
        gap: 10px;
    }

    .mail-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        min-width: 0;
    }

    .mail-card__name {
        margin: 0;
        width: 100%;
        color: var(--c3);
        font-size: var(--fs-300);
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .mail-card__usage {
        display: inline-flex;
        padding: 3px 8px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.38);
        color: rgba(216, 221, 232, 0.72);
        font-size: var(--fs-100);
        font-weight: 700;
    }

    .mail-card__actions {
        display: flex;
        gap: 7px;
        align-items: stretch;
    }

    .mail-card__actions form {
        margin: 0;
        display: flex;
    }

    .mail-card__actions .order-detail__contact {
        flex: 1;
        min-height: 36px;
        padding: 7px 10px;
        font-size: var(--fs-100);
        justify-content: center;
    }

    .mail-card__actions form .order-detail__contact {
        width: 100%;
    }

    .mail-card__danger {
        background: rgba(206, 59, 59, 0.1);
        border-color: rgba(255, 141, 141, 0.22);
    }

    .mail-card__danger:hover {
        background: rgba(206, 59, 59, 0.18);
    }

    .mailer-pager {
        display: flex;
        justify-content: center;
        margin-top: 18px;
    }

    .mail-var-token {
        background: rgba(14, 183, 146, 0.15);
        color: rgba(14, 183, 146, 0.95);
        border-radius: 3px;
        padding: 0 2px;
        font-family: 'Courier New', monospace;
        font-size: 0.82em;
        font-weight: 700;
    }
</style>

<div class="dash_page marketing-index-page">
    @include('admin.Marketing.partials.index-style')

    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => __('admin.marketing.mailer.plural')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <x-icon name="file-earmark-richtext-fill" />
                </span>
                <strong>{{ __('admin.marketing.area_links.marketing') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ __('admin.marketing.mailer.plural') }}</h1>
            <p>{{ __('admin.marketing.mailer.description') }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.customers.mail_models.create') }}" class="order-detail__contact">
                <x-icon name="plus-circle-fill" />
                <span>{{ __('admin.marketing.mailer.create_new') }}</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'models'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <x-icon name="envelope-fill" />
                </span>
                {{ __('admin.marketing.mailer.list_title') }}
            </h3>
        </div>

        @if ($models->count() > 0)
            <div class="mailer-card-grid">
                @foreach ($models as $mailModel)
                    @php
                        $status = $mailModel->status ?: 'draft';
                        $statusLabels = [
                            'draft'    => __('admin.marketing.mailer.draft'),
                            'active'   => __('admin.marketing.mailer.active'),
                            'archived' => __('admin.marketing.mailer.archived'),
                        ];
                        $bodyPreview = trim(strip_tags($mailModel->body_html ?: $mailModel->body ?: ''));
                        $usageCount  = (int) ($mailModel->campaigns_count ?? 0) + (int) ($mailModel->automations_count ?? 0);
                        $appName     = config('configurazione.APP_NAME', config('app.name', 'R'));
                        $logoLetter  = mb_strtoupper(mb_substr($appName, 0, 1));

                        $varLabels = [
                            'customer_name'       => 'Nome e cognome',
                            'customer_first_name' => 'Nome',
                            'customer_last_name'  => 'Cognome',
                            'customer_email'      => 'Email',
                            'customer_phone'      => 'Telefono',
                            'customer_age'        => 'Età',
                            'customer_gender'     => 'Sesso',
                        ];

                        $highlightVar = function (string $text) use ($varLabels): string {
                            // @var syntax
                            $text = preg_replace_callback(
                                '/@([a-zA-Z0-9_]+)/',
                                fn ($m) => '<span class="mail-var-token">' . e($varLabels[$m[1]] ?? $m[1]) . '</span>',
                                $text
                            );
                            // legacy {{ var }} syntax
                            $text = preg_replace_callback(
                                '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
                                fn ($m) => '<span class="mail-var-token">' . e($varLabels[$m[1]] ?? $m[1]) . '</span>',
                                $text
                            );
                            return $text;
                        };

                        $hasPromotion       = (bool) ($mailModel->has_promotion ?? false);
                        $ctaLabel           = trim((string) ($mailModel->cta_label ?: ''));
                        $bodyHighlighted    = $highlightVar(e(\Illuminate\Support\Str::limit($bodyPreview, 160)));
                        $headingHighlighted = $highlightVar(e($mailModel->heading ?: ''));
                        $objectHighlighted  = $highlightVar(e($mailModel->object ?: __('admin.marketing.mailer.undefined_object')));
                    @endphp

                    <article class="mail-card">
                        <a class="mail-card__preview-link" href="{{ route('admin.customers.mail_models.show', $mailModel->id) }}" title="{{ __('admin.marketing.mailer.mail_model') }}: {{ $mailModel->name }}">
                            <div class="mail-card__email">
                                <span class="mail-card__logo">{{ $logoLetter }}</span>

                                <p class="mail-card__subject">
                                    {!! $objectHighlighted !!}
                                </p>

                                @if ($mailModel->heading)
                                    <h4 class="mail-card__heading">{!! $headingHighlighted !!}</h4>
                                @else
                                    <h4 class="mail-card__heading mail-card__heading--placeholder">{{ __('admin.marketing.mailer.preview_heading') }}</h4>
                                @endif

                                @if ($bodyPreview !== '')
                                    <div class="mail-card__body">{!! $bodyHighlighted !!}</div>
                                @else
                                    <div class="mail-card__body mail-card__body--placeholder">{{ __('admin.marketing.mailer.html_body_placeholder') }}</div>
                                @endif

                                @if ($hasPromotion)
                                    <span class="mail-card__cta">{{ $ctaLabel ?: __('admin.emails.marketing.discover_promotion') }}</span>
                                @endif

                                <p class="mail-card__sender-line">{{ $mailModel->sender ?: $appName }}</p>
                            </div>
                            <div class="mail-card__email-footer">
                                {{ __('admin.marketing.mailer.preview_footer') }}
                            </div>
                        </a>

                        <div class="mail-card__bottom">
                            <div class="mail-card__meta">
                                <h5 class="mail-card__name">{{ $mailModel->name }}</h5>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $status,
                                    'label'  => $statusLabels[$status] ?? ucfirst($status),
                                ])
                                @if ($hasPromotion)
                                    <span class="mail-card__usage" style="background:rgba(14,183,146,.18);border-color:rgba(14,183,146,.3);color:rgba(14,183,146,.95);">
                                        Con promozione
                                    </span>
                                @endif
                                @if ($usageCount > 0)
                                    <span class="mail-card__usage">{{ $usageCount }} {{ __('admin.marketing.mailer.usage_count') }}</span>
                                @endif
                            </div>

                            <div class="mail-card__actions">
                                <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.edit', $mailModel->id) }}">
                                    <x-icon name="pencil-square" />
                                    <span>{{ __('admin.common.edit') }}</span>
                                </a>
                                <form action="{{ route('admin.customers.mail_models.delete', $mailModel->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="order-detail__contact mail-card__danger" type="submit">
                                        <x-icon name="trash-fill" />
                                        <span>{{ __('admin.common.delete') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mailer-pager">
                {{ $models->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <x-icon name="file-earmark-richtext-fill" />
                </span>
                <div>
                    <strong>{{ __('admin.marketing.mailer.no_models') }}</strong>
                    <p>{{ __('admin.marketing.mailer.empty_text') }}</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
