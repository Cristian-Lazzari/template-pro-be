@extends('layouts.base')



@section('contents')

<h1>Prenotazioni Tavoli</h1>




    {{-- Legenda  --}}
    <div class="py-3">
        <?php 
        $statuses = ['In Elaborazione', 'Confermato', 'Annullato'];
        ?>
        @foreach ($statuses as $status)
            @if ($status == 'In Elaborazione')
                <span class="text-warning">
                    <span>In Elaborazione</span>
            @elseif ($status == 'Confermato')
                <span class="text-success">
                    <span>Confermato</span>
            @else
                <span class="text-danger">
                    <span>Annullato</span>
            @endif
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle-fill me-2" viewBox="0 0 16 16">
                    <circle cx="7" cy="7" r="7"/>
                </svg>
            </span>
        @endforeach
    </div>

    <table class="table table-hover">
        
        
            @foreach ($reservations as $reservation)
                <?php
                $data_ora = DateTime::createFromFormat('d/m/Y H:i', $reservation->date_slot);
                $ora_formattata = $data_ora->format('H:i');
                $data_formattata = $data_ora->format('d/m');

                if ($reservation->status == 0) {
                    $status_bg_color = 'bg-warning';
                } else if ($reservation->status == 1) {
                    $status_bg_color = 'bg-success';
                } else {
                    $status_bg_color = 'bg-danger';
                }
                ?>
                <tr class="table_row">
                    {{-- DATA  --}}
                    <td 
                        class="{{ $status_bg_color }}" 
                        style="--bs-bg-opacity: .6;" 
                        onclick="window.location.href='{{ route('admin.reservations.show', $reservation->id) }}'"
                    >
                        {{ $data_formattata }}
                    </td>

                    {{-- ORA  --}}
                    <td 
                        class="{{ $status_bg_color }}" 
                        style="--bs-bg-opacity: .6;" 
                        onclick="window.location.href='{{ route('admin.reservations.show', $reservation->id) }}'"
                    >
                        {{ $ora_formattata }}
                    </td>

                    {{-- NOME  --}}
                    <td 
                        class="{{ $status_bg_color }}" 
                        style="--bs-bg-opacity: .6;" 
                        onclick="window.location.href='{{ route('admin.reservations.show', $reservation->id) }}'"
                    >
                        {{ $reservation->name }}
                    </td>

                    {{-- OSPITI  --}}
                    <td 
                        class="{{ $status_bg_color }}" 
                        style="--bs-bg-opacity: .6;" 
                        onclick="window.location.href='{{ route('admin.reservations.show', $reservation->id) }}'"
                    >
                        {{ $reservation->n_person }}
                    </td>

                    {{-- TELEFONO  --}}
                    <td 
                        class="text-truncate d-none d-lg-table-cell {{ $status_bg_color }}" style="--bs-bg-opacity: .6;" 
                        onclick="window.location.href='{{ route('admin.reservations.show', $reservation->id) }}'"
                    >
                        <a 
                            class="phone text-decoration-none" 
                            href="{{ "https://wa.me/" . '39' . $reservation->phone }}"
                        >
                            {{ $reservation->phone }}
                        </a>
                    </td>

                    {{-- EMAIL  --}}
                    <td 
                        class="text-truncate d-none d-lg-table-cell {{ $status_bg_color }}" style="--bs-bg-opacity: .6;" 
                        onclick="window.location.href='{{ route('admin.reservations.show', $reservation->id) }}'"
                    >
                        {{ $reservation->email }}
                    </td>

                    {{-- DATA CREAZIONE  --}}
                    <td 
                        class="d-none d-lg-table-cell {{ $status_bg_color }}" 
                        style="--bs-bg-opacity: .6;" 
                        onclick="window.location.href='{{ route('admin.reservations.show', $reservation->id) }}'"
                    >
                        {{ date('d/m/Y H:i', strtotime($reservation->created_at)) }}
                    </td>

                    {{-- BOTTONI  --}}
                    <td class="{{ $status_bg_color }}" style="--bs-bg-opacity: .6;">
                        {{-- CONFERMA PRENOTA<IONE  --}}
                        @if ($reservation->status !== 1)
                            <button 
                                title="Conferma Ordine" 
                                class="btn btn-success" 
                                data-bs-toggle="modal" 
                                data-bs-target="#confirmModal-{{ $reservation->id }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-white" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="confirmModal-{{ $reservation->id }}" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form  action="{{ route('admin.reservations.confirmReservation', $reservation->id) }}" method="post">
                                            @csrf
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="confirmModalLabel">Conferma: vuoi inviare una notifica al cliente?</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label for="no_c">No
                                                    <input class="me-2" type="radio" name="confirm" id="no_c" value="no">
                                                </label>
                                                <label for="w_app_c">WhatsApp
                                                    <input class="me-2" type="radio" name="confirm" id="w_app_c" value="wa">
                                                </label>
                                                <label for="email_c">Email
                                                    <input class="me-2" type="radio" name="confirm" id="email_c" value="em">
                                                </label>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                                                <button type="submit" class="btn btn-primary">Procedi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </td>
                </tr>
            @endforeach
      

@endsection