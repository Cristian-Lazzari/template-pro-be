@extends('layouts.public')

@section('title', __('admin.public.updates.eyebrow'))
@section('kicker', __('admin.public.updates.title'))
@section('headline', __('admin.public.updates.subtitle'))
@section('lead', __('admin.public.updates.lead'))

@section('hero_actions')
    <a class="public-button public-button--solid" href="#timeline">{{ __('admin.public.updates.open_timeline') }}</a>
    <a class="public-button public-button--ghost" href="{{ route('guest.documentation') }}">{{ __('admin.public.updates.open_documentation') }}</a>
@endsection

@section('contents')
    <section class="public-panel public-panel--soft">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">{{ __('admin.public.updates.how_to_use') }}</p>
            <h2>{{ __('admin.public.updates.simple_rule') }}</h2>
        </div>

        <div class="public-notes">
            <p>{{ __('admin.public.updates.release_rule_1') }}</p>
            <p>{{ __('admin.public.updates.release_rule_2') }}</p>
        </div>
    </section>

    <section class="public-timeline" id="timeline">
        @foreach ($updates as $update)
            <article class="public-timeline__item">
                <div class="public-timeline__meta">
                    <span class="public-badge">{{ $update['version'] }}</span>
                    <strong>{{ $update['date'] }}</strong>
                </div>

                <div class="public-card public-card--timeline">
                    <h2>{{ $update['title'] }}</h2>
                    <p>{{ $update['summary'] }}</p>

                    <ul class="public-list">
                        @foreach ($update['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>
        @endforeach
    </section>
@endsection
