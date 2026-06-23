@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.content.contents'), 'url' => route('admin.posts.index')],
            ['label' => __('admin.Crea_nuovo_ps')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <x-icon name="file-earmark-image-fill" />
                </span>
                <strong>{{ __('admin.content.contents') }}</strong>
            </div>
            <h1 class="menu-dashboard__title">{{ __('admin.Crea_nuovo_ps') }}</h1>
            <p>Aggiungi un nuovo contenuto multimediale al sito.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.posts.index') }}">
                <x-icon name="arrow-left-circle-fill" />
                <span>{{ __('admin.Annulla') }}</span>
            </a>
        </div>
    </header>

    @include('admin.Posts._form', [
        'action' => route('admin.posts.store'),
    ])
</div>

@endsection
