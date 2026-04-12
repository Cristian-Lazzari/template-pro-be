@extends('layouts.base')

@section('contents')
@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $data }} 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (count($notify))
    <div id="alert-container" >
        @foreach ($notify as $r)
            @if ($r['type'] == 'res')  
                <div class="alert alert-dismissible fade show fixed-alert-res" role="alert">
                <a href="{{ route('admin.reservations.show', $r['id']) }}" class="btn btn-dark-outline">{{ __('admin.Dettagli') }}</a> 
            @else    
                <div class="alert alert-dismissible fade show fixed-alert-res" role="alert">
                <a href="{{ route('admin.orders.show', $r['id']) }}" class="btn btn-dark-outline">{{ __('admin.Dettagli') }}</a> 
            @endif 
                {{ $r['m'] }} 
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    </div>
@endif
@php
    $pack = (int) ($property_adv['services'] ?? 0);
    $double = (int) ($property_adv['dt'] ?? 0);
    $weekSet = $property_adv['week_set'] ?? [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []];
@endphp

<div class="dash_page">
    <h1>
        <i class="bi bi-calendar2-check-fill"></i>
        {{__('admin.t_dashboard')}}
    </h1>
    <div class="top_action my-5">

        
        <button id="editToggle" class="my_btn_2 " data-bs-toggle="modal" data-bs-target="#staticBackdropav" >
            <i class="bi bi-arrow-repeat"></i>
            Disponibilità
        </button>
        <button  type="button" class=" my_btn_1 btn_delete" data-bs-toggle="modal" data-bs-target="#exampleModal1">
            <i class="bi bi-ban"></i>
            Blocca giorni
        </button>
        <a class="my_btn_3 ml-auto" href="{{ route('admin.reservations.index') }}">
            <i class="bi bi-credit-card-2-front-fill"></i>
            {{__('admin.Vedi_tutti')}}
        </a> 
    </div>
    <div class="date">
        @if (count($calendar))
            @php 
                $i = 0; 
                $currentDay = date("d");
                $currentMonth = date("m");
                $currentYear = date("Y");
            @endphp
            <div id="calendar_1" class="carousel slide my_carousel">
                <div class="carousel-indicators">
                    @foreach ($calendar as $m)
                        <button  type="button" data-bs-target="#calendar_1" data-bs-slide-to="{{$i}}"
                        @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                            class="active" aria-current="true" 
                        @endif
                        aria-label="{{ 'Slide ' . $i }}"></button>
                        @php $i ++ @endphp
                    @endforeach
                    @php $i = 0; @endphp
                </div>
                <div class="top_line">
                    <button class="prev_btn" type="button" data-bs-target="#calendar_1" data-bs-slide="prev">
                        <i class="bi bi-caret-left-fill"></i>
                    </button>
                    <button class="post_btn" type="button" data-bs-target="#calendar_1" data-bs-slide="next">
                        <i class="bi bi-caret-right-fill"></i>
                    </button>
                </div>
                <div class="carousel-inner">
                    @foreach ($calendar as $m)
                        <div class="carousel-item @if ($currentMonth == $m['month'] && $currentYear == $m['year']) active @endif">
                            <h2> {{ \Carbon\Carbon::create()->month($m['month'])->translatedFormat('F') }} - {{$m['year']}} </h2>
                            <div class="top_stat">
                                @if($m['n_res'])
                                    <div class="line">
                                        <h4>{{__('admin.Prenotazioni')}}</h4>
                                        <div class="stat first">
                                            <i class="bi bi-card-heading"></i>
                                            <span>{{$m['n_res']}}</span>
                                        </div>
                                        
                                        <div class="stat">
                                            <i class="bi bi-people-fill"></i>
                                            <span>{{$m['guests']}}</span>
                                        </div>
                                    </div>
                                @endif
                                @if($m['n_order'])
                                    <div class="line">
                                        <h4>{{__('admin.Ordini')}}</h4>
                                        <div class="stat first">
                                            <i class="bi bi-inboxes"></i>
                                            <span>{{$m['n_order']}}</span>
                                        </div>
                                        <div class="stat ">
                                            <i class="bi bi-piggy-bank-fill"></i>
                                            <span class="cash" ><strong>€</strong>{{$m['cash'] / 100}}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="calendar">
                                <div class="c-name">

                                    @for ($i = 1; $i <= 7; $i++)
                                        <h4>{{ Str::substr(\Carbon\Carbon::create()->startOfWeek()->addDays($i-1)->translatedFormat('D'),0,2) }}</h4>
                                    @endfor

                                </div>
                                <div class="calendar_page">
                                    @foreach ($m['days'] as $d)
                                        <button data-day='@json($d)'
                                        class="day  
                                        @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) current @endif 
                                        @if(in_array($d['status'], [0,3])) day_off @endif " 
                                        style="grid-column-start:{{$d['day_w'] }}">        
                                            <p class="p_day">{{$d['day']}}</p>
                                            @if ($d['guests'] > 0)
                                                <span class="bookings"> <strong> {{$d['guests']}} </strong>
                                                    <i class="bi bi-person-lines-fill"></i>
                                                </span>
                                            @endif
                                            @if ($d['n_order'] > 0)
                                                <span class="bookings top"> <strong> {{$d['n_order']}} </strong>
                                                    <i class="bi bi-inboxes"></i>
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                        @php $i ++ @endphp
                    @endforeach
                </div>
                
            </div>
        @elseif(config('configurazione.subscription') == 1)
            <div class="date-off d-back-g">
                <a href="https://future-plus.it/#pacchetti">{{__('admin.up_sell')}}</a>
            </div>
        @endif
       
    </div>
    <div id="day-details"></div>
    


    <div class="modal fade " id="staticBackdropav" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropavLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered dashboard-availability-dialog">
            <form class="w-100 dashboard-availability-form" action="{{ route('admin.dates.generate') }}" method="post">
                @csrf
                <x-dashboard.action-modal
                    title-id="staticBackdropavLabel"
                    class="dashboard-availability-modal"
                    :title="__('admin.availability_modal_title')"
                    :eyebrow="__('admin.availability_modal_eyebrow')"
                    tone="mint"
                    :description="__('admin.availability_modal_description')"
                >
                    <x-slot name="titleIcon">
                        <i class="bi bi-arrow-repeat"></i>
                    </x-slot>

                    <div class="dashboard-availability-layout">
                        <section class="dashboard-availability-section">
                            <div class="dashboard-availability-section__head">
                                <h2>{{ __('admin.availability_rules_title') }}</h2>
                                <p>{{ __('admin.availability_rules_description') }}</p>
                            </div>

                            <div class="dashboard-availability-fields">
                                <div class="dashboard-availability-field">
                                    <div class="dashboard-availability-field__head">
                                        <label for="delay_or">{{ __('admin.Latenza_ordini') }}</label>
                                        <button type="button" class="dashboard-availability-help-toggle" data-availability-help-toggle aria-expanded="false" aria-controls="delay_or_help">
                                            <i class="bi bi-info-circle"></i>
                                            <span class="visually-hidden">{{ __('admin.Info') }}</span>
                                        </button>
                                    </div>
                                    <input name="delay_or" id="delay_or" type="time" value="{{$property_adv['delay_or'] ?? ''}}">
                                    <p id="delay_or_help" class="dashboard-availability-field__help" hidden>{{ __('admin.availability_help_orders') }}</p>
                                    @error('delay_or') <p class="error">{{ $message }}</p> @enderror
                                </div>
                                <div class="dashboard-availability-field">
                                    <div class="dashboard-availability-field__head">
                                        <label for="delay_res">{{ __('admin.Latenza_prenotazioni') }}</label>
                                        <button type="button" class="dashboard-availability-help-toggle" data-availability-help-toggle aria-expanded="false" aria-controls="delay_res_help">
                                            <i class="bi bi-info-circle"></i>
                                            <span class="visually-hidden">{{ __('admin.Info') }}</span>
                                        </button>
                                    </div>
                                    <input name="delay_res" id="delay_res" type="time" value="{{$property_adv['delay_res'] ?? ''}}">
                                    <p id="delay_res_help" class="dashboard-availability-field__help" hidden>{{ __('admin.availability_help_reservations') }}</p>
                                    @error('delay_res') <p class="error">{{ $message }}</p> @enderror
                                </div>
                                <div class="dashboard-availability-field">
                                    <div class="dashboard-availability-field__head">
                                        <label for="max_day_res">{{ __('admin.Latenza_prenotazioni_giorni') }}</label>
                                        <button type="button" class="dashboard-availability-help-toggle" data-availability-help-toggle aria-expanded="false" aria-controls="max_day_res_help">
                                            <i class="bi bi-info-circle"></i>
                                            <span class="visually-hidden">{{ __('admin.Info') }}</span>
                                        </button>
                                    </div>
                                    <input name="max_day_res" id="max_day_res" type="number" min="1" value="{{$property_adv['max_day_res'] ?? ''}}">
                                    <p id="max_day_res_help" class="dashboard-availability-field__help" hidden>{{ __('admin.availability_help_days') }}</p>
                                    @error('max_day_res') <p class="error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </section>

                        <section class="dashboard-availability-section">
                            <div class="dashboard-availability-section__head">
                                <h2>{{ __('admin.availability_capacity_title') }}</h2>
                                <p>{{ __('admin.availability_capacity_description') }}</p>
                            </div>

                            <div class="dashboard-availability-fields">
                                <div class="dashboard-availability-field @if(!(in_array($pack, [2,4]) && $double)) d-none @endif">
                                    <label for="max_table_1">{{ __('admin.availability_seats_for_room', ['room' => $property_adv['sala_1']]) }}</label>
                                    <input name="max_table_1" id="max_table_1" type="number" min="0" placeholder="{{ __('admin.availability_seats_per_slot') }}" value="{{$property_adv['max_table_1'] ?? ''}}">
                                    @error('max_table_1') <p class="error">{{ $message }}</p> @enderror
                                </div>
                                <div class="dashboard-availability-field @if(!(in_array($pack, [2,4]) && $double)) d-none @endif">
                                    <label for="max_table_2">{{ __('admin.availability_seats_for_room', ['room' => $property_adv['sala_2']]) }}</label>
                                    <input name="max_table_2" id="max_table_2" type="number" min="0" placeholder="{{ __('admin.availability_seats_per_slot') }}" value="{{$property_adv['max_table_2'] ?? ''}}">
                                    @error('max_table_2') <p class="error">{{ $message }}</p> @enderror
                                </div>
                                <div class="dashboard-availability-field @if(!(in_array($pack, [2,4]) && !$double)) d-none @endif">
                                    <label for="max_table">{{ __('admin.N_di_posti') }}</label>
                                    <input name="max_table" id="max_table" type="number" min="0" placeholder="{{ __('admin.availability_seats_per_slot') }}" value="{{$property_adv['max_table'] ?? ''}}">
                                    @error('max_table') <p class="error">{{ $message }}</p> @enderror
                                </div>
                                <div class="dashboard-availability-field @if(!(in_array($pack, [3,4]))) d-none @endif">
                                    <label for="max_asporto">{{ __('admin.N_di_ordini_dasporto') }}</label>
                                    <input name="max_asporto" id="max_asporto" type="number" min="0" placeholder="{{ __('admin.availability_takeaway_orders_per_slot') }}" value="{{$property_adv['max_asporto'] ?? ''}}">
                                    @error('max_asporto') <p class="error">{{ $message }}</p> @enderror
                                </div>
                                <div class="dashboard-availability-field @if(!(in_array($pack, [3,4]))) d-none @endif">
                                    <label for="max_domicilio">{{ __('admin.N_di_oridini_a_domicilio') }}</label>
                                    <input name="max_domicilio" id="max_domicilio" type="number" min="0" placeholder="{{ __('admin.availability_delivery_orders_per_slot') }}" value="{{$property_adv['max_domicilio'] ?? ''}}">
                                    @error('max_domicilio') <p class="error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </section>

                        <section class="dashboard-availability-section dashboard-availability-section--days">
                            <div class="dashboard-availability-section__head">
                                <h2>{{ __('admin.availability_operating_week_title') }}</h2>
                                <p>{{ __('admin.availability_operating_week_description') }}</p>
                            </div>

                            <div class="dashboard-availability-fields">
                                <div class="dashboard-availability-field">
                                    <label for="times_interval">{{ __('admin.Intervallo_minuti') }}</label>
                                    <input name="times_interval" id="times_interval" type="number" min="1" value="{{$property_adv['times_interval'] ?? ''}}">
                                    @error('times_interval') <p class="error">{{ $message }}</p> @enderror
                                </div>
                                <div class="dashboard-availability-field">
                                    <label for="times_start">{{ __('admin.Orario_inizio') }}</label>
                                    <input name="times_start" id="times_start" type="time" value="{{$property_adv['times_start'] ?? ''}}">
                                    @error('times_start') <p class="error">{{ $message }}</p> @enderror
                                </div>
                                <div class="dashboard-availability-field">
                                    <label for="times_end">{{ __('admin.Orario_fine') }}</label>
                                    <input name="times_end" id="times_end" type="time" value="{{$property_adv['times_end'] ?? ''}}">
                                    @error('times_end') <p class="error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="dashboard-availability-days" id="availability-days"></div>
                            <p id="availability-slots-feedback" class="error dashboard-availability-feedback d-none"></p>
                            @error('days_on') <p class="error dashboard-availability-feedback">{{ __('admin.seleziona_Attiva_nei_giorni_i_cui_sei_operativo') }}</p> @enderror
                            <p class="dashboard-action-modal__hint">{{ __('admin.availability_regenerate_hint') }}</p>
                        </section>
                    </div>

                    <x-slot name="footer">
                        <button type="button" class="my_btn_2" data-bs-dismiss="modal">{{ __('admin.availability_close_without_saving') }}</button>
                        <button type="submit" class="my_btn_3">{{ __('admin.availability_save') }}</button>
                    </x-slot>
                </x-dashboard.action-modal>
            </form>
        </div>
    </div>
        <form  action="{{ route('admin.settings.cancelDates')}}"   method="POST">
        @csrf
        <!-- Modal -->
        @php $i= 0; @endphp
        <div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModal1Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mymodal_calendar">
                <div class="modal-content  mymodal_make_res">
                    <div class="modal-body box_container">
                        <div id="c2" class="carousel slide my_carousel" >
                            <div class="carousel-indicators">
                                @foreach ($calendar as $m)
                                    <button  type="button" data-bs-target="#c2" data-bs-slide-to="{{$i}}"
                                    @if ($currentMonth == $m['month'] && $currentYear == $m['year']) class="active" aria-current="true"@endif
                                    aria-label="{{ 'Slide ' . $i }}"></button>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                            <div class="top_line">
                                <button class="prev_btn" type="button" data-bs-target="#c2" data-bs-slide="prev">
                                    <i class="bi bi-caret-left-fill"></i>
                                </button>
                                <button class="post_btn" type="button" data-bs-target="#c2" data-bs-slide="next">
                                    <i class="bi bi-caret-right-fill"></i>
                                </button>
                            </div>
                            <div class="carousel-inner date_modal">
                                @php $i = 0; @endphp
                                @foreach ($calendar as $m)
                                    <div class="carousel-item @if ($currentMonth == $m['month'] && $currentYear == $m['year'])  active @endif ">
                                        <h2>{{['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'][$m['month']]}} - {{$m['year']}}</h2>
                                        <div class="calendar">
                                        
                                            <div class="c-name">
                                                @php
                                                $day_name = ['lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
                                                @endphp
                                                @foreach ($day_name as $item)
                                                    <h4>{{$item}}</h4>
                                                @endforeach
                                            </div>
                                            <div class="calendar_page">

                                                @foreach ($m['days'] as $d)
                                                    @if($d['status'] !== 0)
                                                        <input type="checkbox" name="day_off[]" id="{{$d['date']}}" value="{{$d['date']}}"
                                                        @if ($d['status'] == 3) checked @endif>
                                                    @endif
                                                    @if($d['status'] !== 0) <label
                                                        for="{{$d['date']}}"
                                                    @else
                                                        <div
                                                    @endif
                                                        class="day  
                                                        @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) day-active @endif 
                                                        @if($d['status'] == 0) day_off @endif "
                                                        style="grid-column-start:{{$d['day_w'] }}"
                                                        @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) current @endif 
                                                    >        
                                                        <p class="p_day">{{$d['day']}}</p>
                                                        @if ($d['guests'] > 0)
                                                            <span class="bookings"> <strong> {{$d['guests']}} </strong>
                                                                <i class="bi bi-person-lines-fill"></i>
                                                            </span>
                                                        @endif
                                                        @if ($d['n_order'] > 0)
                                                            <span class="bookings top"> <strong> {{$d['n_order']}} </strong>
                                                                <i class="bi bi-inboxes"></i>
                                                            </span>
                                                        @endif
                                                    @if($d['status'] !== 0)
                                                    </label> @else </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                            
                        </div>
                        <div class="actions w-100">
                            <button class="my_btn_2 btn_delete" type="button" data-bs-dismiss="modal" >{{ __('admin.Annulla') }}</button>
                            <button class="my_btn_3" type="submit">{{ __('admin.Conferma') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const dayButtons = document.querySelectorAll("#calendar_1 .day");
    const detailsContainer = document.getElementById("day-details");
    const blockTimeUrl = "{{ route('admin.dates.blockTime') }}";
    const availabilityDaysContainer = document.getElementById("availability-days");
    const availabilityFeedback = document.getElementById("availability-slots-feedback");
    const availabilityForm = document.querySelector("#staticBackdropav form");
    const availabilitySubmitButton = availabilityForm?.querySelector('button[type="submit"]');
    const availabilityModalElement = document.getElementById("staticBackdropav");
    const startInput = document.getElementById("times_start");
    const endInput = document.getElementById("times_end");
    const intervalInput = document.getElementById("times_interval");
    const initialWeekSet = @json($weekSet);
    const enabledServices = {
        table: @json(in_array($pack, [2, 4])),
        takeAway: @json(in_array($pack, [3, 4])),
        delivery: @json(in_array($pack, [3, 4])),
    };
    const serviceLabels = {
        table: @json(__('admin.availability_service_table')),
        takeAway: @json(__('admin.Asporto')),
        delivery: @json(__('admin.Domicilio')),
    };
    const availabilityTexts = {
        configure: @json(__('admin.availability_configure')),
        activeSlotSingular: @json(__('admin.availability_active_slot_singular')),
        activeSlotPlural: @json(__('admin.availability_active_slot_plural')),
        noActiveSlots: @json(__('admin.availability_no_active_slots')),
        invalidSlots: @json(__('admin.availability_invalid_slots')),
    };
    const availabilityHelpButtons = availabilityForm
        ? Array.from(availabilityForm.querySelectorAll('[data-availability-help-toggle]'))
        : [];
    const dayNames = {
        1: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(0)->translatedFormat('l') }}",
        2: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(1)->translatedFormat('l') }}",
        3: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(2)->translatedFormat('l') }}",
        4: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(3)->translatedFormat('l') }}",
        5: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(4)->translatedFormat('l') }}",
        6: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(5)->translatedFormat('l') }}",
        7: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(6)->translatedFormat('l') }}",
    };

    const timeServiceIcons = {
        table: `<i class="bi bi-people"></i>`,
        takeAway: `<i class="bi bi-bag"></i>`,
        delivery: `<i class="bi bi-truck"></i>`,
    };

    const detailIcons = {
        confirm: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi okk bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>`,
        warning: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi to_see bi-exclamation-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/></svg>`,
        null: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi null bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>`,
        paid: `<i class="bi bi-credit-card-2-back"></i>`,
        adult: `<i class="bi bi-person-standing"></i>`,
        child: `<i class="bi bi-person-arms-up"></i>`,
    };

    function normalizeWeekSet(weekSet) {
        const normalized = {};
        for (let day = 1; day <= 7; day += 1) {
            normalized[day] = weekSet?.[day] ?? weekSet?.[String(day)] ?? {};
        }
        return normalized;
    }

    function setAvailabilityHelpState(button, expanded) {
        const helpId = button.getAttribute('aria-controls');
        const help = helpId ? document.getElementById(helpId) : null;

        button.setAttribute('aria-expanded', String(expanded));
        if (help) {
            help.hidden = !expanded;
        }
    }

    function collapseAvailabilityHelp(exceptHelpId = null) {
        availabilityHelpButtons.forEach((button) => {
            const helpId = button.getAttribute('aria-controls');
            if (helpId === exceptHelpId) {
                return;
            }

            setAvailabilityHelpState(button, false);
        });
    }

    function collectWeekSelections() {
        const selections = normalizeWeekSet({});
        if (!availabilityDaysContainer) {
            return selections;
        }

        availabilityDaysContainer.querySelectorAll('input[type="checkbox"][name^="times_slot_"]').forEach((input) => {
            const match = input.name.match(/^times_slot_\[(\d+)\]\[([0-9]{2}:[0-9]{2})\]\[\]$/);
            if (!match || !input.checked) {
                return;
            }

            const day = match[1];
            const time = match[2];
            if (!Array.isArray(selections[day][time])) {
                selections[day][time] = [];
            }
            selections[day][time].push(Number(input.value));
        });

        return selections;
    }

    function parseMinutes(value) {
        if (!value || !value.includes(':')) {
            return null;
        }

        const [hours, minutes] = value.split(':').map(Number);
        if (Number.isNaN(hours) || Number.isNaN(minutes)) {
            return null;
        }

        return (hours * 60) + minutes;
    }

    function formatMinutes(totalMinutes) {
        const hours = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
        const minutes = String(totalMinutes % 60).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    function buildTimeSlots(startValue, endValue, intervalValue) {
        const startMinutes = parseMinutes(startValue);
        const endMinutes = parseMinutes(endValue);
        const interval = Number(intervalValue);

        if (startMinutes === null || endMinutes === null || !Number.isInteger(interval) || interval <= 0 || startMinutes > endMinutes) {
            return [];
        }

        const slots = [];
        for (let minute = startMinutes; minute <= endMinutes; minute += interval) {
            slots.push(formatMinutes(minute));
            if (interval === 0) {
                break;
            }
        }

        return slots;
    }

    function buildServiceToggle(day, time, suffix, value, serviceKey, icon, label, checked) {
        const safeTime = time.replace(':', '-');
        const inputId = `times_${day}_${safeTime}_${suffix}`;

        return `
            <input type="checkbox" ${checked ? 'checked' : ''} class="btn-check dashboard-availability-slot__check" id="${inputId}" name="times_slot_[${day}][${time}][]" value="${value}">
            <label class="dashboard-availability-slot__option dashboard-availability-slot__option--${serviceKey}" for="${inputId}">
                <span class="dashboard-availability-slot__option-icon">${icon}</span>
                <span class="dashboard-availability-slot__option-label">${label}</span>
            </label>
        `;
    }

    function renderAvailabilityDays({ preserveCollapse = true } = {}) {
        if (!availabilityDaysContainer || !startInput || !endInput || !intervalInput) {
            return;
        }

        const previousSelections = availabilityDaysContainer.children.length
            ? collectWeekSelections()
            : normalizeWeekSet(initialWeekSet);

        const collapseState = {};
        if (preserveCollapse) {
            availabilityDaysContainer.querySelectorAll('.multi-collapse').forEach((collapse) => {
                collapseState[collapse.dataset.day] = collapse.classList.contains('show');
            });
        }

        const slots = buildTimeSlots(startInput.value, endInput.value, intervalInput.value);
        const isValid = slots.length > 0;

        availabilityFeedback.classList.toggle('d-none', isValid);
        availabilityFeedback.textContent = isValid
            ? ''
            : availabilityTexts.invalidSlots;

        if (availabilitySubmitButton) {
            availabilitySubmitButton.disabled = !isValid;
        }

        if (!isValid) {
            availabilityDaysContainer.innerHTML = '';
            return;
        }

        let html = '';
        for (let day = 1; day <= 7; day += 1) {
            const daySelections = previousSelections[day] ?? {};
            const activeSlots = Object.values(daySelections).filter((services) => Array.isArray(services) && services.length > 0).length;
            const hasSelections = activeSlots > 0;
            const showCollapse = preserveCollapse ? (collapseState[day] ?? false) : false;
            const dayMeta = hasSelections
                ? `${activeSlots} ${activeSlots === 1 ? availabilityTexts.activeSlotSingular : availabilityTexts.activeSlotPlural}`
                : availabilityTexts.noActiveSlots;

            html += `
                <div class="dashboard-availability-day-card ${showCollapse ? 'is-open' : ''}">
                    <div class="dashboard-availability-day-card__header">
                        <div class="dashboard-availability-day-card__copy">
                            <h3>${dayNames[day]}</h3>
                            <p>${dayMeta}</p>
                        </div>

                        <button
                            type="button"
                            class="dashboard-availability-day-card__toggle"
                            data-bs-toggle="collapse"
                            data-bs-target="#multiCollapseExample${day}"
                            aria-expanded="${showCollapse}"
                            aria-controls="multiCollapseExample${day}"
                        >
                            <span>${availabilityTexts.configure}</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>

                    <div class="collapse multi-collapse ${showCollapse ? 'show' : ''}" id="multiCollapseExample${day}" data-day="${day}" data-bs-parent="#availability-days">
                        <div class="dashboard-availability-slots">
            `;

            slots.forEach((time) => {
                const selectedServices = Array.isArray(daySelections[time]) ? daySelections[time].map(Number) : [];
                html += `<div class="dashboard-availability-slot"><div class="dashboard-availability-slot__time">${time}</div><div class="dashboard-availability-slot__options">`;

                if (enabledServices.table) {
                    html += buildServiceToggle(day, time, 't', 1, 'table', timeServiceIcons.table, serviceLabels.table, selectedServices.includes(1));
                }
                if (enabledServices.takeAway) {
                    html += buildServiceToggle(day, time, 'a', 2, 'takeAway', timeServiceIcons.takeAway, serviceLabels.takeAway, selectedServices.includes(2));
                }
                if (enabledServices.delivery) {
                    html += buildServiceToggle(day, time, 'd', 3, 'delivery', timeServiceIcons.delivery, serviceLabels.delivery, selectedServices.includes(3));
                }

                html += `</div></div>`;
            });

            html += `
                    </div>
                </div>
            </div>
            `;
        }

        availabilityDaysContainer.innerHTML = html;
    }

    function getStatusClass(statusValue) {
        const parsedStatus = Number(statusValue);
        const statusMap = {
            0: 'null',
            1: 'okk',
            2: 'to_see',
            3: 'to_see',
            4: 'okk',
            5: 'okk',
            6: 'null',
        };

        return statusMap[parsedStatus] ?? 'to_see';
    }

    function renderDayDetails(button) {
        document.querySelectorAll(".day.day-active").forEach((dayButton) => dayButton.classList.remove("day-active"));
        button.classList.add("day-active");

        const dayData = JSON.parse(button.dataset.day);
        const { date, times, status } = dayData;
        let html = `<div class="day-info"><div class="time-list ${status == 3 || status == 0 ? 'op' : ''}">`;

        for (const [time, data] of Object.entries(times)) {
            const res = data.res;
            const or = data.or;
            const properties = (data.property ?? []).map(Number);
            const isBlocked = data.blocked === true;
            const selectedDate = new Date(`${date}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const canBlock = !isBlocked && selectedDate >= today;

            html += `<div class="time-item ${isBlocked ? 'blocked' : ''}"><div class="time-header"><strong>${time}</strong><div class="line ${isBlocked ? 'blocked-line' : ''}"></div><p class="prop">`;
            if (properties.includes(1)) html += timeServiceIcons.table;
            if (properties.includes(2)) html += timeServiceIcons.takeAway;
            if (properties.includes(3)) html += timeServiceIcons.delivery;
            html += `</p>
                ${isBlocked ? `<button type="button" class="unblock-time-btn" data-date="${date}" data-time="${time}"><i class="bi bi-toggle-off"></i></button>` : (canBlock ? `<button type="button" class="block-time-btn" data-date="${date}" data-time="${time}"><i class="bi bi-toggle-on"></i></button>` : '')}
            </div><div class="time-content">`;

            if (res.length > 0) {
                res.forEach((reservation) => {
                    const reservationStatus = Number(reservation.status);
                    const people = JSON.parse(reservation.n_person);
                    const paidLabel = reservationStatus === 6 ? `{{ __('admin.Rimborsato') }}` : `{{ __('admin.Pagato') }}`;
                    const reservationLink = "{{config('configurazione.APP_URL')}}" + '/admin/reservations/' + reservation.id;

                    html += `<a href="${reservationLink}" class="res-item ${getStatusClass(reservationStatus)}">
                        <div class="top">
                            <div class="id">R${reservation.id ?? ''}</div>
                            ${[0, 6].includes(reservationStatus) ? detailIcons.null : ''}
                            ${[2, 3].includes(reservationStatus) ? detailIcons.warning : ''}
                            ${[1, 4, 5].includes(reservationStatus) ? detailIcons.confirm : ''}
                            <div class="name">${reservation.name + ' ' + reservation.surname}</div>
                            ${[3, 5, 6].includes(reservationStatus) ? `<div class="${reservationStatus === 6 ? 'refound' : 'paid'} status">${detailIcons.paid} ${paidLabel}</div>` : ''}
                            <div class="guest">
                                ${people.adult > 0 ? people.adult + detailIcons.adult : ''}
                                ${people.child > 0 ? people.child + detailIcons.child : ''}
                            </div>
                        </div>
                    </a>`;
                });
            }

            if (or.length > 0) {
                or.forEach((order) => {
                    const orderStatus = Number(order.status);
                    const paidLabel = orderStatus === 6 ? `{{ __('admin.Rimborsato') }}` : `{{ __('admin.Pagato') }}`;
                    const orderLink = "{{config('configurazione.APP_URL')}}" + '/admin/orders/' + order.id;

                    html += `<a href="${orderLink}" class="order-item ${getStatusClass(orderStatus)}">
                        <div class="top">
                            <div class="id">O${order.id ?? ''}</div>
                            ${[0, 6].includes(orderStatus) ? detailIcons.null : ''}
                            ${[2, 3].includes(orderStatus) ? detailIcons.warning : ''}
                            ${[1, 4, 5].includes(orderStatus) ? detailIcons.confirm : ''}
                            <div class="name">${order.name + ' ' + order.surname}</div>
                            ${[3, 5, 6].includes(orderStatus) ? `<div class="${orderStatus === 6 ? 'refound' : 'paid'} status">${detailIcons.paid} ${paidLabel}</div>` : ''}
                            <div class="price">€${order.tot_price / 100}</div>
                        </div>
                        <div class="cart">`;

                    order.products.forEach((product) => {
                        html += `<div class="item_cart"><div class="name">${product.pivot?.quantity ?? 1}* ${product.name}</div><div class="price">€${product.price / 100}</div></div>`;
                    });
                    order.menus.forEach((menu) => {
                        html += `<div class="item_cart"><div class="name">${menu.pivot?.quantity ?? 1}* ${menu.name}</div><div class="price">€${menu.price / 100}</div></div>`;
                    });

                    html += `</div></a>`;
                });
            }

            html += `</div></div>`;
        }

        html += `</div></div>`;
        detailsContainer.innerHTML = html;
        attachBlockButtons();
    }

    function attachBlockButtons() {
        document.querySelectorAll('.block-time-btn, .unblock-time-btn').forEach((button) => {
            button.addEventListener('click', async () => {
                if (button.disabled) return;
                button.disabled = true;

                const date = button.dataset.date;
                const time = button.dataset.time;
                const action = button.classList.contains('block-time-btn') ? 'block' : 'unblock';

                try {
                    const response = await fetch(blockTimeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ date, time, action }),
                    });

                    const result = await response.json();
                    if (!result.success) {
                        console.error(`Errore nell'${action === 'block' ? 'blocco' : 'sblocco'} orario:`, result.message);
                        button.disabled = false;
                        return;
                    }

                    const timeItem = button.closest('.time-item');
                    const timeHeader = timeItem?.querySelector('.time-header');
                    const line = timeItem?.querySelector('.line');

                    if (!timeItem || !timeHeader) {
                        return;
                    }

                    if (action === 'block') {
                        timeItem.classList.add('blocked');
                        if (line) line.classList.add('blocked-line');
                        button.remove();

                        const unblockButton = document.createElement('button');
                        unblockButton.type = 'button';
                        unblockButton.className = 'unblock-time-btn';
                        unblockButton.dataset.date = date;
                        unblockButton.dataset.time = time;
                        unblockButton.innerHTML = `<i class="bi bi-toggle-off"></i>`;
                        timeHeader.appendChild(unblockButton);
                    } else {
                        timeItem.classList.remove('blocked');
                        if (line) line.classList.remove('blocked-line');
                        button.remove();

                        const blockButton = document.createElement('button');
                        blockButton.type = 'button';
                        blockButton.className = 'block-time-btn';
                        blockButton.dataset.date = date;
                        blockButton.dataset.time = time;
                        blockButton.innerHTML = `<i class="bi bi-toggle-on"></i>`;
                        timeHeader.appendChild(blockButton);
                    }

                    attachBlockButtons();

                    dayButtons.forEach((dayButton) => {
                        const dayData = JSON.parse(dayButton.dataset.day);
                        if (dayData.date === date && dayData.times[time]) {
                            dayData.times[time].blocked = (action === 'block');
                            dayButton.dataset.day = JSON.stringify(dayData);
                        }
                    });
                } catch (error) {
                    console.error(`Error ${action}ing time:`, error);
                    button.disabled = false;
                }
            });
        });
    }

    [startInput, endInput, intervalInput].forEach((input) => {
        input?.addEventListener('input', renderAvailabilityDays);
        input?.addEventListener('change', renderAvailabilityDays);
    });

    availabilityHelpButtons.forEach((button) => {
        setAvailabilityHelpState(button, false);

        button.addEventListener('click', () => {
            const helpId = button.getAttribute('aria-controls');
            const isExpanded = button.getAttribute('aria-expanded') === 'true';

            collapseAvailabilityHelp(isExpanded ? null : helpId);
            setAvailabilityHelpState(button, !isExpanded);
        });
    });

    availabilityModalElement?.addEventListener('show.bs.modal', () => {
        collapseAvailabilityHelp();
        renderAvailabilityDays({ preserveCollapse: false });
        window.requestAnimationFrame(() => {
            availabilityForm?.querySelector('.dashboard-action-modal .modal-body')?.scrollTo({ top: 0 });
        });
    });

    availabilityModalElement?.addEventListener('hidden.bs.modal', () => {
        collapseAvailabilityHelp();
    });

    renderAvailabilityDays({ preserveCollapse: false });

    dayButtons.forEach((button) => {
        button.addEventListener("click", () => renderDayDetails(button));
    });
});
</script>


@endsection
