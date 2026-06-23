@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.content.contents'), 'url' => route('admin.posts.index')],
            ['label' => $post->title],
        ],
    ])

    @if (session('ingredient_success'))
        @php $data = session('ingredient_success') @endphp
        <div class="alert alert-success">
            {{ __('admin.catalog.created_flash', ['name' => $data['name_ing']]) }}
        </div>
    @endif

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--warning">
                    <x-icon name="pencil-square" />
                </span>
                <strong>{{ __('admin.content.contents') }}</strong>
            </div>
            <h1 class="menu-dashboard__title">{{ __('admin.Modifica_il_Post') }}</h1>
            <p>{{ $post->title }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.posts.index') }}">
                <x-icon name="arrow-left-circle-fill" />
                <span>{{ __('admin.common.back') }}</span>
            </a>
        </div>
    </header>

    @include('admin.Posts._form', [
        'action' => route('admin.posts.update', $post),
        'post'   => $post,
    ])
</div>

@endsection
