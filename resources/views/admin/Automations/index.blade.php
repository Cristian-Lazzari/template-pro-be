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
            ['label' => 'Automazioni'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">Automazioni</h1>
            <p>Trigger marketing configurati, senza scheduler reale in questa fase.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.automations.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>Crea nuova</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'automations'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-list-check"></i>
                </span>
                Elenco automazioni
            </h3>
        </div>

        @if ($automations->count() > 0)
            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Status</th>
                            <th>Trigger</th>
                            <th>Modello mail</th>
                            <th>Promozioni</th>
                            <th>Cooldown</th>
                            <th>Ultimo run</th>
                            <th>Coinvolti</th>
                            <th>Inviate</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($automations as $automation)
                            <tr>
                                <td><strong>{{ $automation->name }}</strong></td>
                                <td>
                                    @include('admin.Marketing.partials.status-pill', [
                                        'status' => $automation->status,
                                        'label' => $statuses[$automation->status] ?? $automation->status,
                                    ])
                                </td>
                                <td>{{ $triggers[$automation->trigger] ?? ($automation->trigger ?: '-') }}</td>
                                <td>{{ $automation->model?->name ?? '-' }}</td>
                                <td>
                                    @forelse ($automation->promotions as $promotion)
                                        <div>{{ $promotion->name }}</div>
                                    @empty
                                        -
                                    @endforelse
                                </td>
                                <td>{{ data_get($automation->metadata, 'cooldown_days') ?? '-' }}</td>
                                <td>{{ $automation->last_run_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $automation->total_activation }}</td>
                                <td>{{ $automation->total_sent }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-sm btn-outline-light" href="{{ route('admin.automations.show', $automation) }}" title="Dettaglio">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a class="btn btn-sm btn-outline-light" href="{{ route('admin.automations.edit', $automation) }}" title="Modifica">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.automations.activate', $automation) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit" title="Attiva">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.automations.pause', $automation) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" type="submit" title="Pausa">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.automations.archive', $automation) }}" method="POST">
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
                {{ $automations->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <div>
                    <strong>Nessuna automazione presente.</strong>
                    <p>Crea un trigger marketing per preparare audience in modo controllato.</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
