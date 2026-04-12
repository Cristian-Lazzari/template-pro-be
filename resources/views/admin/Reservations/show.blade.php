@extends('layouts.base')

@section('contents')
<a onclick="history.back()" class="btn btn-outline-light my-5">
    <i class="bi bi-arrow-90deg-left"></i>
</a>
@php
    $dataOra = DateTime::createFromFormat('d/m/Y H:i', $reservation->date_slot);
    $oraFormattata = $dataOra?->format('H:i');
    $dataFormattata = $dataOra?->format('d/m/Y');
    $giornoSettimana = (int) ($dataOra?->format('w') ?? 0);
    $giorniSettimana = ['domenica', 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato'];
    $nPerson = json_decode($reservation->n_person, true) ?? [];
    $roomLabel = null;

    if ($reservation->sala !== null && $reservation->sala !== 0) {
        $roomLabel = $reservation->sala == 1 ? ($property_adv['sala_1'] ?? null) : ($property_adv['sala_2'] ?? null);
    }

    $reservationCancelButtonLabel = 'Annulla';
@endphp

<div class="reservation-detail-page">
    <x-dashboard.reservation-detail
        :status="$reservation->status"
        :reservation-code="'R' . $reservation->id"
        :time="$oraFormattata"
        :date-label="trim(($giorniSettimana[$giornoSettimana] ?? '') . ' ' . ($dataFormattata ?? ''))"
        :customer="$reservation->name . ' ' . $reservation->surname"
        :email="$reservation->email"
        :phone="$reservation->phone"
        :adults="(int) ($nPerson['adult'] ?? 0)"
        :children="(int) ($nPerson['child'] ?? 0)"
        :room-label="$roomLabel"
        :note="$reservation->message"
        :sent-at="\Carbon\Carbon::parse($reservation->created_at)->translatedFormat('H:i:s l j F Y')"
        :marketing="$reservation->news_letter ? 'si' : 'no'"
    >
        @if (in_array($reservation->status, [0, 2, 3]))
            <button type="button" data-bs-toggle="modal" data-bs-target="#confirmModal" class="my_btn_3">{{ __('admin.Conferma') }}</button>
        @endif
        @if (in_array($reservation->status, [1, 2, 3, 5]))
            <button type="button" data-bs-toggle="modal" data-bs-target="#cancelModal" class="my_btn_5">{{ $reservationCancelButtonLabel }}</button>
        @endif
    </x-dashboard.reservation-detail>
</div>

      {{-- Modale per conferma --}}
    <div class="modal fade" id="confirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <x-dashboard.action-modal
                title-id="confirmModalLabel"
                title="Conferma prenotazione"
                eyebrow="Conferma"
                tone="success"
                entity-label="{{ __('admin.Prenotazione_di') }}"
                :subject="$reservation->name"
                :date-slot="$reservation->date_slot"
                description="La mail automatica parte sempre. Qui scegli solo se aggiungere anche WhatsApp."
            >
                <x-slot name="details">
                    <div class="dashboard-action-modal__detail">
                        <span>Stato finale</span>
                        <strong>Confermata</strong>
                    </div>

                    <div class="dashboard-action-modal__detail">
                        <span>Canale base</span>
                        <strong>Email automatica</strong>
                    </div>
                </x-slot>

                <p class="dashboard-action-modal__hint">Usa WhatsApp solo se vuoi un contatto piu diretto o la prenotazione e vicina.</p>

                <x-slot name="footer">
                    <form action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="0" type="hidden" name="wa">
                        <input value="1" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">
                        <button type="submit" class="w-100 my_btn_2">Solo email</button>
                    </form>

                    <form action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="1" type="hidden" name="wa">
                        <input value="1" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">
                        <button type="submit" class="w-100 my_btn_3">Email + WhatsApp</button>
                    </form>
                </x-slot>
            </x-dashboard.action-modal>
        </div>
    </div>

    {{-- Modale per annullamento --}}
    <div class="modal fade" id="cancelModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <x-dashboard.action-modal
                title-id="cancelModalLabel"
                title="Annulla prenotazione"
                eyebrow="Annulla"
                tone="danger"
                entity-label="{{ __('admin.Prenotazione_di') }}"
                :subject="$reservation->name"
                :date-slot="$reservation->date_slot"
                description="Usa questa azione solo se non puoi confermare la richiesta. La mail automatica parte sempre."
            >
                <x-slot name="details">
                    <div class="dashboard-action-modal__detail">
                        <span>Stato finale</span>
                        <strong>Annullata</strong>
                    </div>

                    <div class="dashboard-action-modal__detail">
                        <span>Canale base</span>
                        <strong>Email automatica</strong>
                    </div>
                </x-slot>

                <p class="dashboard-action-modal__hint">Se ti serve un contatto piu immediato puoi aggiungere anche il messaggio WhatsApp.</p>

                <x-slot name="footer">
                    <form action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="0" type="hidden" name="wa">
                        <input value="0" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">
                        <button type="submit" class="w-100 my_btn_2">Solo email</button>
                    </form>

                    <form action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="1" type="hidden" name="wa">
                        <input value="0" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">
                        <button type="submit" class="w-100 my_btn_5">Email + WhatsApp</button>
                    </form>
                </x-slot>
            </x-dashboard.action-modal>
        </div>
    </div>
{{-- 
    <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="exampleModal{{$item->id}}Label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn_close" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle-fill" style="font-size: 20px"></i>
                        {{__('admin.Chiudi')}}
                    </button>
                    <div class="action_top">
                        <a href="{{ route('admin.posts.edit', $item) }}" class="edit">
                            <i style="vertical-align: sub; font-size: 20px" class="bi bi-pencil-square"></i>
                        </a>
                        
                        <form action="{{ route('admin.posts.status') }}" method="POST">
                            @csrf
                            <input type="hidden" name="archive" value="0">
                            <input type="hidden" name="v" value="1">
                            <input type="hidden" name="a" value="0">
                            <input type="hidden" name="id" value="{{$item->id}}">
                            <button type="submit" class=" edit
                                @if(!$item->visible) not @endif 
                                visible">
                                <i class="bi bi-eye-fill" style="font-size: 20px"></i>
                                <i class="bi bi-eye-slash-fill" style="font-size: 20px"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.posts.status') }}" method="POST">
                            @csrf
                            <input type="hidden" name="archive" value="0">
                            <input type="hidden" name="v" value="0">
                            <input type="hidden" name="a" value="1">
                            <input type="hidden" name="id" value="{{$item->id}}">
                            <button class="edit" type="submit">
                                <i class="bi bi-trash-fill" style="font-size: 20px"></i>
                            </button>
                        </form>
                
                    </div>
                    <div class="name_cat">
                        <div class="name">{{$item->title}}</div>
                        <div class="cat">{{$item->path}}</div>
                    </div>
                    @if ($item->description)
                        <section>
                            <h4>
                                <i class="bi bi-card-text" style="font-size: 20px"></i>
                                {{__('admin.Descrizione')}}</h4>
                            <p>{{$item->description}}</p>
                        </section>
                    @endif
                    @if ($item->hashtag)
                        <section>
                            <h4>
                                <strong>#</strong>
                                Hashtags</h4>
                            <p>{{$item->hashtag}}</p>
                        </section>
                    @endif
                    @if ($item->link)
                        <section>
                            <h4>
                                <i class="bi bi-link-45deg" style="font-size: 20px"></i>
                                Link</h4>
                            <a href="{{$item->link}}">{{$item->link}}</a>
                        </section>
                    @endif


                </div>
            </div>
        </div>
    </div> --}}

@endsection
