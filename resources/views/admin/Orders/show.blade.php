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
            <button type="button" data-bs-toggle="modal" data-bs-target="#cancelModal" class="w-100 my_btn_5">{{ in_array($order->status, [3, 5]) ? 'Rimborsa e Annulla' : 'Annulla' }}</button>
        @endif
    </x-dashboard.order-detail>
</div>

<!-- Modale per la posticipazione -->
<div class="modal fade" id="changeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="changeModalLabel" aria-hidden="true">
    <div class="modal-dialog ">
        <form action="{{ route('admin.orders.changetime') }}" method="POST" class="modal-content mymodal_make_res">
            @csrf
            <input value="{{$order->id}}" type="hidden" name="id">
            <div class="modal-header">
                <h1 class="modal-title fs-2" id="changeModalLabel">{{ __('admin.Conferma_e_posticipa_questo_ordine') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fs-4">
                <p>
                    {{ __('admin.Ordine_di') }} <strong>{{$order->name}} </strong>{{ __('admin.per_il') }} <strong>{{$order->date_slot}}</strong>
                </p>
                {{-- <p>{{ __('admin.Oltre_alla_mail_automatica_vuoi_anche_inviare_un_messaggio_su_whatsapp') }}</p> --}}
            </div>
            <section class="modal-body fs-4">
                <p>{{ __('admin.Seleziona_lorario_corretto') }}</p>
                <select name="new_time" required>
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
                <p class="mt-4 mb-3">{{ __('admin.Vuoi_bloccare_altri_ordini_per_questa_fascia_oraria') }}</p>

            </section>
            <div class="modal-footer">
                <button type="submit" name="block" value="0" class="my_btn_5">{{ __('admin.Lascia_attivo') }}</button>
                <button type="submit" name="block" value="1" class="my_btn_3">{{ __('admin.Blocca_questo_orario') }}</button>
            </div>
            
        </form>
    </div>
</div>

<!-- Modale per la conferma -->
<div class="modal fade" id="confirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content mymodal_make_res">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="confirmModalLabel">{{ __('admin.Gestione_notifica_per_conferma') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fs-4">
                <p>
                    {{ __('admin.Ordine_di') }} <strong>{{$order->name}} </strong>{{ __('admin.per_il') }} <strong>{{$order->date_slot}}</strong>
                </p>
                {{-- <p>{{ __('admin.Oltre_alla_mail_automatica_vuoi_anche_inviare_un_messaggio_su_whatsapp') }}</p> --}}
            </div>
            <div class="modal-footer">
                <form action="{{ route('admin.orders.status') }}" method="POST">
                    @csrf
                    <input value="0" type="hidden" name="wa">
                    <input value="1" type="hidden" name="c_a">
                    <input value="{{$order->id}}" type="hidden" name="id">
                    <button type="submit" class="w-100 my_btn_3">{{__('admin.Conferma')}}</button>
                </form>
            {{-- <form action="{{ route('admin.orders.status') }}" method="POST">
                    @csrf
                    <input value="1" type="hidden" name="wa">
                    <input value="1" type="hidden" name="c_a">
                    <input value="{{$order->id}}" type="hidden" name="id">
                    <button type="submit" class="w-100 my_btn_3">Si</button>
            </form> --}}
            </div>
        </div>
    </div>
</div>

<!-- Modale per l'annullamento -->
<div class="modal fade" id="cancelModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content mymodal_make_res">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="cancelModalLabel">{{ __('admin.Gestione_notifica_per_annullamento') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fs-4">
                <p>
                    {{ __('admin.Ordine_di') }} <strong>{{$order->name}} </strong>{{ __('admin.per_il') }} <strong>{{$order->date_slot}}</strong>
                </p>
                {{-- <p>{{ __('admin.Oltre_alla_mail_automatica_vuoi_anche_inviare_un_messaggio_su_whatsapp') }}</p> --}}
            </div>
            <div class="modal-footer">
                <form action="{{ route('admin.orders.status') }}" method="POST">
                    @csrf
                    <input value="0" type="hidden" name="wa">
                    <input value="0" type="hidden" name="c_a">
                    <input value="{{$order->id}}" type="hidden" name="id">
                    <button type="submit" class="w-100 my_btn_5">{{__('admin.Annulla')}}</button>
            </form>
            {{-- <form action="{{ route('admin.orders.status') }}" method="POST">
                    @csrf
                    <input value="1" type="hidden" name="wa">
                    <input value="0" type="hidden" name="c_a">
                    <input value="{{$order->id}}" type="hidden" name="id">
                    <button type="submit" class="w-100 my_btn_3">Si</button>
            </form> --}}
            </div>
        </div>
    </div>
</div>

@endsection
