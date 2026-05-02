@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show dashboard-home__flash" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.campaigns.index')],
            ['label' => 'Promozioni'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">Promozioni</h1>
            <p>Regole promozionali, validita e contatori operativi.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.promotions.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>Crea nuova</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'promotions'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-list-check"></i>
                </span>
                Elenco promozioni
            </h3>
        </div>

        @if ($promotions->count() > 0)
            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Uso</th>
                            <th>Sconto</th>
                            <th>Permanente</th>
                            <th>Coinvolti</th>
                            <th>Inviate</th>
                            <th>Usate</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($promotions as $promotion)
                            <tr>
                                <td><strong>{{ $promotion->name }}</strong></td>
                                <td><code>{{ $promotion->slug }}</code></td>
                                <td>
                                    @include('admin.Marketing.partials.status-pill', [
                                        'status' => $promotion->status,
                                        'label' => $statuses[$promotion->status] ?? $promotion->status,
                                    ])
                                </td>
                                <td>{{ $caseUses[$promotion->case_use] ?? ($promotion->case_use ?: '-') }}</td>
                                <td>
                                    {{ $discountTypes[$promotion->type_discount] ?? ($promotion->type_discount ?: '-') }}
                                    @if ($promotion->discount !== null)
                                        <br>{{ number_format((float) $promotion->discount, 2, ',', '.') }}
                                    @endif
                                </td>
                                <td>
                                    <x-dashboard.state-pill :tone="$promotion->permanent ? 'active' : 'neutral'">
                                        {{ $promotion->permanent ? 'Si' : 'No' }}
                                    </x-dashboard.state-pill>
                                </td>
                                <td>{{ $promotion->total_activation }}</td>
                                <td>{{ $promotion->total_sent }}</td>
                                <td>{{ $promotion->total_used }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-sm btn-outline-light" href="{{ route('admin.promotions.show', $promotion) }}" title="Dettaglio">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a class="btn btn-sm btn-outline-light" href="{{ route('admin.promotions.edit', $promotion) }}" title="Modifica">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.promotions.publish', $promotion) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit" title="Pubblica">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.promotions.pause', $promotion) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" type="submit" title="Pausa">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.promotions.archive', $promotion) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Archivia">
                                                <i class="bi bi-archive-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $promotions->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                <div>
                    <strong>Nessuna promozione presente.</strong>
                    <p>Crea la prima regola promozionale per collegarla a campagne o automazioni.</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
