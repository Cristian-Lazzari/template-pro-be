@extends('layouts.public')

@section('title', __('admin.public.documentation.eyebrow'))
@section('kicker', __('admin.public.documentation.title'))
@section('headline', __('admin.public.documentation.subtitle'))
@section('lead', __('admin.public.documentation.lead'))

@section('hero_actions')
    <a class="public-button public-button--solid" href="#azioni-rapide">{{ __('admin.public.documentation.open_index') }}</a>
    <a class="public-button public-button--ghost" href="{{ route('guest.updates') }}">{{ __('admin.public.documentation.view_updates') }}</a>
@endsection

@section('contents')
    <section class="public-panel public-panel--onboarding">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">{{ $onboarding['eyebrow'] }}</p>
            <h2>{{ $onboarding['title'] }}</h2>
        </div>

        <div class="public-onboarding">
            <div class="public-onboarding__intro">
                <p>{{ $onboarding['intro'] }}</p>

                <div class="public-onboarding__tips">
                    @foreach ($onboarding['tips'] as $tip)
                        <div class="public-note">{{ $tip }}</div>
                    @endforeach
                </div>
            </div>

            <div class="public-onboarding__checklist">
                <h3>{{ __('admin.public.documentation.before_starting') }}</h3>
                <ul class="public-list">
                    @foreach ($onboarding['checklist'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="public-step-grid">
            @foreach ($onboarding['steps'] as $step)
                <article class="public-step-card">
                    <div class="public-icon-badge">
                        @include('guests.partials.doc-icon', ['name' => $step['icon'], 'label' => $step['title']])
                    </div>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="public-panel public-panel--soft" id="azioni-rapide">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">{{ __('admin.public.documentation.quick_access') }}</p>
            <h2>{{ __('admin.public.documentation.quick_access_text') }}</h2>
        </div>

        <div class="doc-quick-actions">
            @foreach ($quickActions as $action)
                <article class="public-card doc-quick-action">
                    <span class="public-icon-badge public-icon-badge--soft">
                        @include('guests.partials.doc-icon', ['name' => $action['icon'], 'label' => $action['title']])
                    </span>
                    <div class="doc-quick-action__body">
                        <h3>{{ $action['title'] }}</h3>
                        <p>{{ $action['description'] }}</p>
                    </div>
                    <a class="public-button public-button--ghost" href="{{ route('guest.documentation.page', ['page' => $action['page']]) }}">
                        {{ $action['cta'] }}
                    </a>
                </article>
            @endforeach
        </div>
    </section>

    <section class="public-panel" id="pagine-documentazione">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">{{ __('admin.public.documentation.documentation_pages') }}</p>
            <h2>{{ __('admin.public.documentation.documentation_pages_text') }}</h2>
        </div>

        <div class="doc-topic-grid">
            @foreach ($docPages as $page)
                @include('guests.partials.doc-topic-card', ['page' => $page])
            @endforeach
        </div>
    </section>
@endsection
