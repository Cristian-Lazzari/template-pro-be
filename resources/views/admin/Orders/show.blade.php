@extends('layouts.base')

@section('contents')
@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-info">
        {{ $data }}
    </div>
@endif
@if (session('error'))
    @php
        $data = session('error')
    @endphp
    <div class="alert alert-danger">
        {{ $data }}
    </div>
@endif




<a onclick="history.back()" class="btn btn-outline-light my-5">
    <x-icon name="arrow-90deg-left" />
</a>
@php
    $dataOra = DateTime::createFromFormat('d/m/Y H:i', $order->date_slot);
    $oraFormattata = $dataOra?->format('H:i');
    $dataFormattata = $dataOra?->format('d/m/Y');
    $giornoSettimana = (int) ($dataOra?->format('w') ?? 0);
    $giorniSettimana = ['domenica', 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato'];

    $detailItems = [];

    foreach ($order->menus as $menu) {
        $details = [];

        if ($menu->fixed_menu == '2') {
            $chosenProducts = [];
            $choices = json_decode($menu->pivot->choices, true) ?? [];

            foreach ($choices as $choiceId) {
                foreach ($menu->products as $product) {
                    if ($product->id == $choiceId) {
                        $chosenProducts[] = ($product->pivot->label ?? 'Prodotto') . ': ' . $product->name . ' (' . $product->category->name . ')';
                        break;
                    }
                }
            }

            if ($chosenProducts !== []) {
                $details[] = ['label' => __('admin.Prodotti'), 'values' => $chosenProducts];
            }
        } else {
            $menuProducts = [];

            foreach ($menu->products as $product) {
                $menuProducts[] = $product->name . ' (' . $product->category->name . ')';
            }

            if ($menuProducts !== []) {
                $details[] = ['label' => __('admin.Prodotti'), 'values' => $menuProducts];
            }
        }

        $detailItems[] = [
            'quantity' => $menu->pivot->quantity,
            'name' => $menu->name,
            'details' => $details,
        ];
    }

    foreach ($order->products as $product) {
        $options = json_decode($product->pivot->option, true) ?? [];
        $adds = json_decode($product->pivot->add, true) ?? [];
        $removes = json_decode($product->pivot->remove, true) ?? [];

        $details = [];

        if ($options !== []) {
            $details[] = ['label' => __('admin.Opzioni'), 'values' => array_map(fn ($value) => '+ ' . $value, $options)];
        }

        if ($adds !== []) {
            $details[] = ['label' => __('admin.Ingredienti_extra'), 'values' => array_map(fn ($value) => '+ ' . $value, $adds)];
        }

        if ($removes !== []) {
            $details[] = ['label' => __('admin.Ingredienti_rimossi'), 'values' => array_map(fn ($value) => '- ' . $value, $removes)];
        }

        $detailItems[] = [
            'quantity' => $product->pivot->quantity,
            'name' => $product->name,
            'details' => $details,
        ];
    }

    $dateLabel = trim(($giorniSettimana[$giornoSettimana] ?? '') . ' ' . ($dataFormattata ?? ''));
    $fulfillmentTitle = isset($order->comune) ? __('admin.Consegnare_a_domicilio') : __('admin.Ritiro_dasporto');
    $fulfillmentValue = isset($order->comune) ? collect([$order->comune, $order->address, $order->address_n])->filter()->implode(', ') : null;
    $cancelButtonLabel = in_array($order->status, [3, 5], true) ? 'Rimborsa e Annulla' : 'Annulla';
    $confirmResultLabel = $order->status === 3 ? 'Confermato e incassato' : 'Confermato';
    $cancelResultLabel = in_array($order->status, [3, 5], true) ? 'Rimborsato e annullato' : 'Annullato';
@endphp

<div class="order-detail-page">
    <x-dashboard.order-detail
        :status="$order->status"
        :order-code="'O' . $order->id"
        :time="$oraFormattata"
        :date-label="$dateLabel"
        :customer="$order->name . ' ' . $order->surname"
        :email="$order->email"
        :phone="$order->phone"
        :items="$detailItems"
        :delivery-cost="$delivery_cost ? number_format($delivery_cost / 100, 2, ',', '.') : null"
        :total="number_format($order->tot_price / 100, 2, ',', '.')"
        :fulfillment-title="$fulfillmentTitle"
        :fulfillment-value="$fulfillmentValue"
        :fulfillment-type="isset($order->comune) ? 'delivery' : 'takeaway'"
        :note="$order->message"
        :sent-at="\Carbon\Carbon::parse($order->created_at)->translatedFormat('H:i:s l j F Y')"
        :marketing="$order->news_letter ? 'si' : 'no'"
    >
        @if (in_array($order->status, [0, 2, 3]))
            <button type="button" data-bs-toggle="modal" data-bs-target="#confirmModal" class="w-100 my_btn_3">{{ __('admin.Conferma') }}</button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#changeModal" class="w-100 my_btn_3 post_btn">
                <x-icon name="clock" />
                {{ __('admin.Posticipa_e_Conferma') }}
            </button>
        @endif
        @if (in_array($order->status, [1, 2, 3, 5]))
            <button type="button" data-bs-toggle="modal" data-bs-target="#cancelModal" class="w-100 my_btn_5">{{ $cancelButtonLabel }}</button>
        @endif
    </x-dashboard.order-detail>
</div>

<!-- Modale per la posticipazione -->
<div class="modal fade" id="changeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="changeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.orders.changetime') }}" method="POST" class="w-100">
            @csrf
            <input value="{{$order->id}}" type="hidden" name="id">
            <x-dashboard.action-modal
                title-id="changeModalLabel"
                eyebrow="{{ __('admin.Conferma_e_posticipa_questo_ordine') }}"
                tone="warning"
                entity-label="{{ __('admin.Ordine_di') }}"
                :subject="$order->name"
                :date-slot="$order->date_slot"
            >
                <x-slot name="details">
                    <div class="dashboard-action-modal__detail">
                        <span>Stato finale</span>
                        <strong>Confermato</strong>
                    </div>

                    <div class="dashboard-action-modal__detail">
                        <span>Fascia originale</span>
                        <strong>La lasci attiva o la blocchi ora</strong>
                    </div>
                </x-slot>

                <div class="dashboard-action-modal__field">
                    <label for="order-new-time">{{ __('admin.Seleziona_lorario_corretto') }}</label>

                    <select id="order-new-time" name="new_time" required>
                        @php
                            $slot = DateTime::createFromFormat('H:i', $times_start);
                            $slotEnd = DateTime::createFromFormat('H:i', $times_end);
                            $interval = intval($times_interval);
                            $hasOption = false;
                        @endphp

                        @while ($slot && $slot <= $slotEnd)
                            @php
                                $timeValue = $slot->format('H:i');
                                $hasOption = true;
                            @endphp
                            <option value="{{ $timeValue }}" {{ $timeValue == $oraFormattata ? 'selected' : '' }}>{{ $timeValue }}</option>
                            @php $slot->modify("+{$interval} minutes"); @endphp
                        @endwhile

                        @unless ($hasOption)
                            <option value="">{{ __('admin.Nessun_orario_disponibile') }}</option>
                        @endunless
                    </select>
                </div>

                <p class="dashboard-action-modal__hint">Scegli uno slot realistico. Il cliente riceve l aggiornamento con il nuovo orario.</p>

                <x-slot name="footer">
                    <button type="submit" name="block" value="0" class="my_btn_5">{{ __('admin.Lascia_attivo') }}</button>
                    <button type="submit" name="block" value="1" class="my_btn_3">{{ __('admin.Blocca_questo_orario') }}</button>
                </x-slot>
            </x-dashboard.action-modal>
        </form>
    </div>
</div>

<!-- Modale per la conferma -->
<div class="modal fade" id="confirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <x-dashboard.action-modal
            title-id="confirmModalLabel"
            eyebrow="Conferma ordine"
            tone="success"
            entity-label="{{ __('admin.Ordine_di') }}"
            :subject="$order->name"
            :date-slot="$order->date_slot"
        >
            <x-slot name="details">
                <div class="dashboard-action-modal__detail">
                    <span>Stato finale</span>
                    <strong>{{ $confirmResultLabel }}</strong>
                </div>

                <div class="dashboard-action-modal__detail">
                    <span>Avviso cliente</span>
                    <strong>Email automatica</strong>
                </div>
            </x-slot>

            <p class="dashboard-action-modal__hint">Il cliente riceve subito la conferma con il riepilogo dell ordine.</p>

            <x-slot name="footer">
                <form action="{{ route('admin.orders.status') }}" method="POST">
                    @csrf
                    <input value="0" type="hidden" name="wa">
                    <input value="1" type="hidden" name="c_a">
                    <input value="{{$order->id}}" type="hidden" name="id">
                    <button type="submit" class="w-100 my_btn_3">Conferma ordine</button>
                </form>
            </x-slot>
        </x-dashboard.action-modal>
    </div>
</div>

<!-- Modale per l'annullamento -->
<div class="modal fade" id="cancelModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <x-dashboard.action-modal
            title-id="cancelModalLabel"
            eyebrow="{{ $cancelButtonLabel }}"
            tone="danger"
            entity-label="{{ __('admin.Ordine_di') }}"
            :subject="$order->name"
            :date-slot="$order->date_slot"
            :description="in_array($order->status, [3, 5], true) ? 'Il pagamento risulta gia incassato: questa azione avvia anche il rimborso.' : 'Usa questa azione solo se non puoi evadere l ordine richiesto.'"
        >
            <x-slot name="details">
                <div class="dashboard-action-modal__detail">
                    <span>Stato finale</span>
                    <strong>{{ $cancelResultLabel }}</strong>
                </div>

                <div class="dashboard-action-modal__detail">
                    <span>Avviso cliente</span>
                    <strong>Email automatica</strong>
                </div>
            </x-slot>

            <p class="dashboard-action-modal__hint">Procedi solo dopo un ultimo controllo su giorno, orario e ordine.</p>

            <x-slot name="footer">
                <form action="{{ route('admin.orders.status') }}" method="POST">
                    @csrf
                    <input value="0" type="hidden" name="wa">
                    <input value="0" type="hidden" name="c_a">
                    <input value="{{$order->id}}" type="hidden" name="id">
                    <button type="submit" class="w-100 my_btn_5">{{ $cancelButtonLabel }}</button>
                </form>
            </x-slot>
        </x-dashboard.action-modal>
    </div>
</div>

@endsection
