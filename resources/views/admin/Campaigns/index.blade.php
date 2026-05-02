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
            ['label' => 'Campagne'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-envelope-paper-fill"></i>
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">Campagne</h1>
            <p>Invii manuali o programmati verso segmenti clienti.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.campaigns.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>Crea nuova</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'campaigns'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-list-check"></i>
                </span>
                Elenco campagne
            </h3>
        </div>

        @if ($campaigns->count() > 0)
            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Status</th>
                            <th>Segmento</th>
                            <th>Modello mail</th>
                            <th>Promozioni</th>
                            <th>Programmata</th>
                            <th>Coinvolti</th>
                            <th>Inviate</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaigns as $campaign)
                            <tr>
                                <td><strong>{{ $campaign->name }}</strong></td>
                                <td>
                                    @include('admin.Marketing.partials.status-pill', [
                                        'status' => $campaign->status,
                                        'label' => $statuses[$campaign->status] ?? $campaign->status,
                                    ])
                                </td>
                                <td>{{ $segments[$campaign->segment] ?? ($campaign->segment ?: '-') }}</td>
                                <td>{{ $campaign->model?->name ?? '-' }}</td>
                                <td>
                                    @forelse ($campaign->promotions as $promotion)
                                        <div>{{ $promotion->name }}</div>
                                    @empty
                                        -
                                    @endforelse
                                </td>
                                <td>{{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $campaign->total_activation }}</td>
                                <td>{{ $campaign->total_sent }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-sm btn-outline-light" href="{{ route('admin.campaigns.show', $campaign) }}" title="Dettaglio">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a class="btn btn-sm btn-outline-light" href="{{ route('admin.campaigns.edit', $campaign) }}" title="Modifica">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.campaigns.activate', $campaign) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit" title="Attiva">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" type="submit" title="Pausa">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST">
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
                {{ $campaigns->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <i class="bi bi-envelope-paper-fill"></i>
                </span>
                <div>
                    <strong>Nessuna campagna presente.</strong>
                    <p>Crea una campagna per preparare assegnazioni cliente-promozione.</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
