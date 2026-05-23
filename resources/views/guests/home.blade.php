@extends('layouts.public')

@section('title', __('admin.public.home.eyebrow'))
@section('kicker', __('admin.public.home.title'))
@section('headline', __('admin.public.home.subtitle'))
@section('lead', __('admin.public.home.lead'))

@section('hero_actions')
    <a class="public-button public-button--solid" href="{{ route('login') }}">{{ __('admin.Accedi') }}</a>
    <a class="public-button public-button--ghost" href="{{ route('guest.documentation') }}">{{ __('admin.public.home.open_documentation') }}</a>
@endsection

@section('contents')
    <section class="public-grid public-grid--home">
        <article class="public-card">
            <p class="public-card__eyebrow">{{ __('admin.public.home.documentation_card_title') }}</p>
            <h2>{{ __('admin.public.home.documentation_card_subtitle') }}</h2>
            <p>{{ __('admin.public.home.documentation_card_text') }}</p>
            <a class="public-inline-link" href="{{ route('guest.documentation') }}">{{ __('admin.public.home.documentation_card_cta') }}</a>
        </article>

        <article class="public-card">
            <p class="public-card__eyebrow">{{ __('admin.public.home.updates_card_title') }}</p>
            <h2>{{ __('admin.public.home.updates_card_subtitle') }}</h2>
            <p>{{ __('admin.public.home.updates_card_text') }}</p>
            <a class="public-inline-link" href="{{ route('guest.updates') }}">{{ __('admin.public.home.updates_card_cta') }}</a>
        </article>

        <article class="public-card">
            <p class="public-card__eyebrow">{{ __('admin.public.home.access_card_title') }}</p>
            <h2>{{ __('admin.public.home.access_card_subtitle') }}</h2>
            <p>{{ __('admin.public.home.access_card_text') }}</p>
            <a class="public-inline-link" href="{{ route('login') }}">{{ __('admin.Accedi') }}</a>
        </article>
    </section>

    <section class="public-panel public-panel--soft">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">{{ __('admin.public.home.most_used_pages') }}</p>
            <h2>{{ __('admin.public.home.most_used_pages_text') }}</h2>
        </div>

        <div class="doc-topic-grid doc-topic-grid--compact doc-topic-grid--home">
            @foreach (array_slice($docPages, 0, 4) as $page)
                @include('guests.partials.doc-topic-card', ['page' => $page])
            @endforeach
        </div>
    </section>
@endsection
