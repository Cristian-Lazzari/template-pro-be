@php
    use Carbon\Carbon;

    $dateSlot = $content_mail['date_slot'];
    if (strpos($dateSlot, ' ') !== false) {
        $formattedDate = Carbon::createFromFormat('d/m/Y H:i', $dateSlot)->translatedFormat('l j F \a\l\l\e H:i');
    } else {
        $formattedDate = Carbon::createFromFormat('d/m/Y', $dateSlot)->translatedFormat('l j F');
    }

    $status = $content_mail['status'] ?? 2;
    if ($status == 0) {
        $statusColor  = '#b91c1c';
        $statusBg     = '#fef2f2';
        $statusBorder = '#fca5a5';
        $statusLabel  = __('admin.emails.status_cancelled');
    } elseif ($status == 1) {
        $statusColor  = '#15803d';
        $statusBg     = '#f0fdf4';
        $statusBorder = '#86efac';
        $statusLabel  = __('admin.emails.status_confirmed');
    } elseif ($status == 6) {
        $statusColor  = '#b45309';
        $statusBg     = '#fffbeb';
        $statusBorder = '#fcd34d';
        $statusLabel  = __('admin.emails.status_refunded');
    } else {
        $statusColor  = '#b45309';
        $statusBg     = '#fffbeb';
        $statusBorder = '#fcd34d';
        $statusLabel  = __('admin.emails.status_pending');
    }

    $appName  = config('configurazione.APP_NAME', config('app.name'));
    $appUrl   = config('configurazione.APP_URL');
    $isDemoUrl = $appUrl === 'https://db-demo3.future-plus.it';
    $logoSrc  = $isDemoUrl
        ? $appUrl . '/public/favicon.png'
        : config('configurazione.domain') . '/img/favicon.png';

    $appliedPromotions = $content_mail['promotions'] ?? [];
    $mailTo            = $content_mail['to'] ?? 'user';
@endphp
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $appName }}</title>
    <style>
        span.im { color: #1e1b4b !important; }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#eef0f5; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">

<!-- Wrapper esterno -->
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#eef0f5;">
    <tr>
        <td align="center" style="padding:28px 12px 40px;">

            <!-- Card principale -->
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
                   style="max-width:600px; width:100%; background-color:#ffffff; border-radius:14px;
                          box-shadow:0 4px 32px rgba(0,0,0,0.10); overflow:hidden;">

                <!-- Barra colorata superiore in base allo stato -->
                <tr>
                    <td style="background-color:{{ $statusColor }}; height:5px; font-size:0; line-height:0;">&nbsp;</td>
                </tr>

                <!-- ===== HEADER: logo + nome ristorante ===== -->
                <tr>
                    <td align="center" style="background-color:#0f0b2e; padding:28px 40px 24px;">
                        <img src="{{ $logoSrc }}" alt="{{ $appName }}"
                             style="width:60px; height:60px; border-radius:14px;
                                    border:2px solid rgba(255,255,255,0.18); display:block; margin:0 auto 12px;">
                        <p style="color:rgba(255,255,255,0.55); font-size:12px; letter-spacing:0.12em;
                                   text-transform:uppercase; margin:0; font-weight:600;">
                            {{ $appName }}
                        </p>
                    </td>
                </tr>

                <!-- ===== TITOLO + STATO ===== -->
                <tr>
                    <td align="center" style="padding:32px 40px 0;">

                        <!-- Badge stato -->
                        <span style="display:inline-block; padding:5px 16px; border-radius:20px;
                                     background-color:{{ $statusBg }}; color:{{ $statusColor }};
                                     font-size:11px; font-weight:800; letter-spacing:0.10em;
                                     text-transform:uppercase; border:1px solid {{ $statusBorder }};">
                            {{ $statusLabel }}
                        </span>

                        <h1 style="color:#0f0b2e; font-size:21px; font-weight:700; line-height:1.35;
                                   margin:16px 0 6px; text-align:center;">
                            {{ $content_mail['title'] }}
                        </h1>

                        @if (!empty($content_mail['subtitle']))
                            <p style="color:#4b5563; font-size:14px; margin:0 0 4px; text-align:center; line-height:1.5;">
                                {{ $content_mail['subtitle'] }}
                            </p>
                        @endif

                        <p style="font-size:10px; color:#9ca3af; margin:12px 0 0;">
                            * {{ __('admin.auto_mail') }}
                        </p>
                    </td>
                </tr>

                <!-- Divisore -->
                <tr>
                    <td style="padding:20px 40px 0;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr><td style="border-top:1px solid #e5e7eb; font-size:0; line-height:0;">&nbsp;</td></tr>
                        </table>
                    </td>
                </tr>

                <!-- ===== DATA ===== -->
                <tr>
                    <td style="padding:20px 40px 0;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                               style="background-color:#f8f9fc; border-radius:10px; border-left:4px solid #0f0b2e;">
                            <tr>
                                <td style="padding:14px 18px;">
                                    <p style="color:#6b7280; font-size:10px; text-transform:uppercase;
                                               letter-spacing:0.10em; font-weight:700; margin:0 0 4px;">
                                        {{ __('admin.Data_prenotata') }}
                                    </p>
                                    <p style="color:#0f0b2e; font-size:16px; font-weight:700; margin:0;">
                                        {{ ucfirst($formattedDate) }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- ================================================================
                     SEZIONE ORDINE
                     ================================================================ --}}
                @if ($content_mail['type'] == 'or')

                    <!-- Etichetta riepilogo -->
                    <tr>
                        <td style="padding:24px 40px 10px;">
                            <p style="color:#6b7280; font-size:10px; text-transform:uppercase;
                                       letter-spacing:0.10em; font-weight:700; margin:0;">
                                {{ __('admin.emails.order_summary') }}
                            </p>
                        </td>
                    </tr>

                    <!-- ---- MENU ---- -->
                    @foreach ($content_mail['cart']['menus'] as $i)
                    <tr>
                        <td style="padding:0 40px 10px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="background-color:#f8f9fc; border-radius:10px; overflow:hidden;">
                                <tr>
                                    @if (isset($i->image))
                                    <td width="76" valign="top" style="padding:12px 0 12px 12px;">
                                        <img src="{{ asset('public/storage/' . $i->image) }}" alt="{{ $i->name }}"
                                             style="width:68px; height:68px; border-radius:8px; object-fit:cover; display:block;">
                                    </td>
                                    @endif
                                    <td valign="top" style="padding:14px 16px;">

                                        <!-- Riga nome + quantità + prezzo -->
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td valign="top">
                                                    <p style="color:#0f0b2e; font-size:15px; font-weight:700; margin:0 0 2px;">
                                                        {{ $i->name }}
                                                    </p>
                                                    <p style="color:#9ca3af; font-size:11px; margin:0;">{{ __('admin.common.menu') }}</p>
                                                </td>
                                                <td valign="top" align="right" style="white-space:nowrap; padding-left:10px;">
                                                    @if ($i->pivot->quantity > 1)
                                                        <span style="display:inline-block; background-color:#0f0b2e; color:#fff;
                                                                     font-size:10px; font-weight:800; padding:2px 8px;
                                                                     border-radius:10px; margin-bottom:4px;">
                                                            &times;{{ $i->pivot->quantity }}
                                                        </span><br>
                                                    @endif
                                                    <span style="color:#0f0b2e; font-size:15px; font-weight:700;
                                                                 font-family:monospace;">
                                                        {{ \App\Support\Currency::formatCents($i->price) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>

                                        @if ($i->fixed_menu == '2')
                                            @php
                                                $right_c = [];
                                                $scelti  = json_decode($i->pivot->choices);
                                                foreach ($scelti as $choiceId) {
                                                    foreach ($i->products as $p) {
                                                        if ($p->id == $choiceId) { $right_c[] = $p; break; }
                                                    }
                                                }
                                            @endphp
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                                   style="margin-top:10px; border-top:1px solid #e5e7eb;">
                                                <tr>
                                                    <td style="padding-top:8px;">
                                                        <p style="color:#6b7280; font-size:10px; text-transform:uppercase;
                                                                   letter-spacing:0.08em; font-weight:700; margin:0 0 6px;">
                                                            {{ __('admin.Prodotti_scelti') }}
                                                        </p>
                                                        @foreach ($right_c as $c)
                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                                               style="margin-bottom:4px;">
                                                            <tr>
                                                                <td>
                                                                    <p style="color:#374151; font-size:13px; margin:0;">
                                                                        <strong style="color:#0f0b2e;">{{ $c->pivot->label }}:</strong>
                                                                        {{ $c->name }}
                                                                        <span style="color:#9ca3af;">({{ $c->category->name }})</span>
                                                                    </p>
                                                                </td>
                                                                @if ($c->pivot->extra_price)
                                                                <td align="right" style="white-space:nowrap; padding-left:8px;">
                                                                    <p style="color:#15803d; font-size:13px; font-weight:700;
                                                                               font-family:monospace; margin:0;">
                                                                        +{{ \App\Support\Currency::formatCents($c->pivot->extra_price) }}
                                                                    </p>
                                                                </td>
                                                                @endif
                                                            </tr>
                                                        </table>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            </table>
                                        @else
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                                   style="margin-top:10px; border-top:1px solid #e5e7eb;">
                                                <tr>
                                                    <td style="padding-top:8px;">
                                                        <p style="color:#6b7280; font-size:10px; text-transform:uppercase;
                                                                   letter-spacing:0.08em; font-weight:700; margin:0 0 6px;">
                                                            {{ __('admin.Prodotti_nel_menu') }}
                                                        </p>
                                                        @foreach ($i->products as $c)
                                                            <p style="color:#374151; font-size:13px; margin:2px 0;">
                                                                {{ $c->name }}
                                                                <span style="color:#9ca3af;">({{ $c->category->name }})</span>
                                                            </p>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    <!-- ---- PRODOTTI ---- -->
                    @foreach ($content_mail['cart']['products'] as $i)
                    @php $arrD = json_decode($i->pivot->remove); @endphp
                    <tr>
                        <td style="padding:0 40px 10px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="background-color:#f8f9fc; border-radius:10px; overflow:hidden;">
                                <tr>
                                    @if (isset($i->image))
                                    <td width="76" valign="top" style="padding:12px 0 12px 12px;">
                                        <img src="{{ asset('public/storage/' . $i->image) }}" alt="{{ $i->name }}"
                                             style="width:68px; height:68px; border-radius:8px; object-fit:cover; display:block;">
                                    </td>
                                    @endif
                                    <td valign="top" style="padding:14px 16px;">

                                        <!-- Riga nome + quantità + prezzo -->
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td valign="top">
                                                    <p style="color:#0f0b2e; font-size:15px; font-weight:700; margin:0 0 2px;">
                                                        {{ $i->name }}
                                                    </p>
                                                    <p style="color:#9ca3af; font-size:11px; margin:0;">{{ __('admin.common.product') }}</p>
                                                </td>
                                                <td valign="top" align="right" style="white-space:nowrap; padding-left:10px;">
                                                    @if ($i->pivot->quantity > 1)
                                                        <span style="display:inline-block; background-color:#0f0b2e; color:#fff;
                                                                     font-size:10px; font-weight:800; padding:2px 8px;
                                                                     border-radius:10px; margin-bottom:4px;">
                                                            &times;{{ $i->pivot->quantity }}
                                                        </span><br>
                                                    @endif
                                                    <span style="color:#0f0b2e; font-size:15px; font-weight:700;
                                                                 font-family:monospace;">
                                                        {{ \App\Support\Currency::formatCents($i->price) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>

                                        @if (count($i->r_option) || count($i->r_add) || count($arrD))
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                               style="margin-top:10px; border-top:1px solid #e5e7eb;">
                                            <tr>
                                                <td style="padding-top:8px;">

                                                    @if (count($i->r_option))
                                                        <p style="color:#6b7280; font-size:10px; text-transform:uppercase;
                                                                   letter-spacing:0.08em; font-weight:700; margin:0 0 4px;">
                                                            {{ __('admin.Opzioni') }}
                                                        </p>
                                                        @foreach ($i->r_option as $a)
                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                                               style="margin-bottom:3px;">
                                                            <tr>
                                                                <td>
                                                                    <p style="color:#374151; font-size:13px; margin:0;">{{ $a->name }}</p>
                                                                </td>
                                                                @if ($a->price)
                                                                <td align="right" style="white-space:nowrap; padding-left:8px;">
                                                                    <p style="color:#15803d; font-size:13px; font-weight:700;
                                                                               font-family:monospace; margin:0;">
                                                                        +{{ \App\Support\Currency::formatCents($a->price) }}
                                                                    </p>
                                                                </td>
                                                                @endif
                                                            </tr>
                                                        </table>
                                                        @endforeach
                                                    @endif

                                                    @if (count($i->r_add))
                                                        <p style="color:#6b7280; font-size:10px; text-transform:uppercase;
                                                                   letter-spacing:0.08em; font-weight:700; margin:{{ count($i->r_option) ? '10px' : '0' }} 0 4px;">
                                                            {{ __('admin.Ingredienti_extra') }}
                                                        </p>
                                                        @foreach ($i->r_add as $a)
                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                                               style="margin-bottom:3px;">
                                                            <tr>
                                                                <td>
                                                                    <p style="color:#374151; font-size:13px; margin:0;">+ {{ $a->name }}</p>
                                                                </td>
                                                                @if ($a->price)
                                                                <td align="right" style="white-space:nowrap; padding-left:8px;">
                                                                    <p style="color:#15803d; font-size:13px; font-weight:700;
                                                                               font-family:monospace; margin:0;">
                                                                        +{{ \App\Support\Currency::formatCents($a->price) }}
                                                                    </p>
                                                                </td>
                                                                @endif
                                                            </tr>
                                                        </table>
                                                        @endforeach
                                                    @endif

                                                    @if (count($arrD))
                                                        <p style="color:#6b7280; font-size:10px; text-transform:uppercase;
                                                                   letter-spacing:0.08em; font-weight:700; margin:{{ (count($i->r_option) || count($i->r_add)) ? '10px' : '0' }} 0 4px;">
                                                            {{ __('admin.Ingredienti_rimossi') }}
                                                        </p>
                                                        @foreach ($arrD as $a)
                                                            <p style="color:#b91c1c; font-size:13px; margin:2px 0;">
                                                                &minus; {{ $a }}
                                                            </p>
                                                        @endforeach
                                                    @endif

                                                </td>
                                            </tr>
                                        </table>
                                        @endif

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    <!-- ---- CONSEGNA o TOTALE ---- -->
                    @if (isset($content_mail['comune']))

                        <!-- Indirizzo consegna -->
                        <tr>
                            <td style="padding:6px 40px 0;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                       style="background-color:#f8f9fc; border-radius:10px;">
                                    <tr>
                                        <td style="padding:16px 18px;">
                                            <p style="color:#6b7280; font-size:10px; text-transform:uppercase;
                                                       letter-spacing:0.10em; font-weight:700; margin:0 0 6px;">
                                                {{ __('admin.Indirizzo_per_la_consegna') }}
                                            </p>
                                            <p style="color:#0f0b2e; font-size:15px; font-weight:600; margin:0 0 8px;">
                                                {{ $content_mail['address'] }}, {{ $content_mail['address_n'] }}, {{ $content_mail['comune'] }}
                                            </p>
                                            @if ($content_mail['delivery_cost'])
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                                   style="border-top:1px solid #e5e7eb; padding-top:8px; margin-top:4px;">
                                                <tr>
                                                    <td style="padding-top:8px;">
                                                        <p style="color:#4b5563; font-size:13px; margin:0;">
                                                            {{ __('admin.Costo_della_consegna_a_domicilio') }}
                                                        </p>
                                                    </td>
                                                    <td align="right" style="padding-top:8px; white-space:nowrap; padding-left:8px;">
                                                        <p style="color:#0f0b2e; font-size:14px; font-weight:700;
                                                                   font-family:monospace; margin:0;">
                                                            +{{ \App\Support\Currency::formatCents($content_mail['delivery_cost']) }}
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                    @else

                        <!-- Totale carrello -->
                        <tr>
                            <td style="padding:6px 40px 0;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                       style="background-color:#0f0b2e; border-radius:10px;">
                                    <tr>
                                        <td style="padding:16px 20px;">
                                            <p style="color:rgba(255,255,255,0.6); font-size:10px; text-transform:uppercase;
                                                       letter-spacing:0.10em; font-weight:700; margin:0;">
                                                {{ __('admin.Totale_carrello') }}
                                            </p>
                                        </td>
                                        <td align="right" style="padding:16px 20px; white-space:nowrap;">
                                            <p style="color:#ffffff; font-size:22px; font-weight:800;
                                                       font-family:monospace; margin:0;">
                                                {{ \App\Support\Currency::formatCents($content_mail['total_price']) }}
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:10px 40px 0;">
                                <p style="color:#6b7280; font-size:13px; margin:0; text-align:center;">
                                    {{ __('admin.modalita_consegna_asporto', ['name' => $appName]) }}
                                </p>
                            </td>
                        </tr>

                    @endif

                {{-- ================================================================
                     SEZIONE PRENOTAZIONE
                     ================================================================ --}}
                @elseif ($content_mail['type'] == 'res')

                    <tr>
                        <td style="padding:24px 40px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="background-color:#f8f9fc; border-radius:10px;">
                                <tr>
                                    <td style="padding:18px 20px;">

                                        @if ($content_mail['property_adv']['dt'] && $content_mail['sala'] !== 0)
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                               style="margin-bottom:14px; padding-bottom:14px; border-bottom:1px solid #e5e7eb;">
                                            <tr>
                                                <td>
                                                    <p style="color:#6b7280; font-size:10px; text-transform:uppercase;
                                                               letter-spacing:0.10em; font-weight:700; margin:0 0 4px;">
                                                        {{ __('admin.Sala_prenota') }}
                                                    </p>
                                                    <p style="color:#0f0b2e; font-size:16px; font-weight:700; margin:0;">
                                                        {{ $content_mail['sala'] == 1
                                                            ? $content_mail['property_adv']['sala_1']
                                                            : $content_mail['property_adv']['sala_2'] }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                        @endif

                                        @if (is_string($content_mail['n_person']))
                                            @php $n_person = json_decode($content_mail['n_person'], true); @endphp
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                @if (!empty($n_person['adult']))
                                                <tr>
                                                    <td style="padding:4px 0;">
                                                        <p style="color:#4b5563; font-size:13px; margin:0;">
                                                            {{ __('admin.Numero_di_adulti') }}
                                                        </p>
                                                    </td>
                                                    <td align="right" style="padding:4px 0;">
                                                        <p style="color:#0f0b2e; font-size:18px; font-weight:800; margin:0;">
                                                            {{ $n_person['adult'] }}
                                                        </p>
                                                    </td>
                                                </tr>
                                                @endif
                                                @if (!empty($n_person['child']))
                                                <tr>
                                                    <td style="padding:4px 0;">
                                                        <p style="color:#4b5563; font-size:13px; margin:0;">
                                                            {{ __('admin.Numero_di_bambini') }}
                                                        </p>
                                                    </td>
                                                    <td align="right" style="padding:4px 0;">
                                                        <p style="color:#0f0b2e; font-size:18px; font-weight:800; margin:0;">
                                                            {{ $n_person['child'] }}
                                                        </p>
                                                    </td>
                                                </tr>
                                                @endif
                                            </table>
                                        @endif

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                @endif

                {{-- ================================================================
                     PROMOZIONI
                     ================================================================ --}}
                @if (!empty($appliedPromotions))
                <tr>
                    <td style="padding:16px 40px 0;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                               style="background-color:#f0fdf4; border-radius:10px; border:1.5px solid #86efac;">
                            <tr>
                                <td style="padding:18px 20px;">

                                    <p style="color:#15803d; font-size:10px; font-weight:800; text-transform:uppercase;
                                               letter-spacing:0.10em; margin:0 0 10px;">
                                        &#127873;
                                        @if ($mailTo === 'admin')
                                            {{ __('admin.emails.promotion_applied') }}
                                        @else
                                            {{ __('admin.emails.active_offer') }}
                                        @endif
                                    </p>

                                    @foreach ($appliedPromotions as $promotion)
                                        <p style="color:#0f0b2e; font-size:15px; font-weight:700; margin:0 0 3px;">
                                            {{ $promotion['promotion_name'] }}
                                        </p>
                                        <p style="color:#4b5563; font-size:13px; margin:0 0 8px;">
                                            {{ $promotion['type_label'] }}
                                        </p>

                                        @if ($mailTo === 'admin')
                                            @if (($promotion['discount_amount'] ?? 0) > 0)
                                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                    <tr>
                                                        <td>
                                                            <p style="color:#4b5563; font-size:13px; margin:0;">{{ __('admin.emails.applied_discount') }}</p>
                                                        </td>
                                                        <td align="right">
                                                            <p style="color:#15803d; font-size:16px; font-weight:800;
                                                                       font-family:monospace; margin:0;">
                                                                &minus;{{ \App\Support\Currency::formatCents($promotion['discount_amount']) }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            @elseif (($promotion['type_discount'] ?? '') === 'gift')
                                                <p style="color:#0f0b2e; font-size:13px; font-weight:700; margin:4px 0;">
                                                    {{ __('admin.emails.gift_applied') }}
                                                </p>
                                            @endif
                                            @if (($content_mail['type'] ?? null) === 'or')
                                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                                       style="margin-top:8px; border-top:1px solid #bbf7d0;">
                                                    <tr>
                                                        <td style="padding-top:8px;">
                                                            <p style="color:#4b5563; font-size:12px; margin:0;">{{ __('admin.emails.discounted_order_total') }}</p>
                                                        </td>
                                                        <td align="right" style="padding-top:8px;">
                                                            <p style="color:#15803d; font-size:16px; font-weight:800;
                                                                       font-family:monospace; margin:0;">
                                                                {{ \App\Support\Currency::formatCents($content_mail['total_price']) }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            @endif
                                            @if (!empty($promotion['affected_items']))
                                                <p style="color:#6b7280; font-size:11px; margin:8px 0 0;">
                                                    {{ __('admin.emails.details') }} {{ collect($promotion['affected_items'])->map(fn ($item) => $item['name'] ?? $item['label'] ?? $item['type'] ?? null)->filter()->implode(', ') }}
                                                </p>
                                            @endif
                                        @else
                                            @if (($promotion['discount_amount'] ?? 0) > 0)
                                                <p style="color:#0f0b2e; font-size:14px; margin:4px 0;">
                                                    {!! __('admin.emails.discount_on_reservation_html', ['amount' => '<strong style="color:#15803d;">' . e(\App\Support\Currency::formatCents($promotion['discount_amount'])) . '</strong>']) !!}
                                                </p>
                                            @elseif (($promotion['type_discount'] ?? '') === 'gift')
                                                <p style="color:#0f0b2e; font-size:14px; font-weight:700; margin:4px 0;">
                                                    {{ __('admin.emails.gift_registered') }}
                                                </p>
                                            @else
                                                <p style="color:#0f0b2e; font-size:14px; margin:4px 0;">
                                                    {{ $promotion['label'] ?? '' }}
                                                </p>
                                            @endif
                                            <p style="color:#6b7280; font-size:12px; margin:8px 0 0;">
                                                {{ __('admin.emails.offer_applied_on_visit') }}
                                            </p>
                                        @endif
                                    @endforeach

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif

                {{-- ================================================================
                     MESSAGGIO CLIENTE
                     ================================================================ --}}
                @if ($content_mail['message'] !== null)
                <tr>
                    <td style="padding:16px 40px 0;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                               style="background-color:#f8f9fc; border-radius:10px; border-left:4px solid #d1d5db;">
                            <tr>
                                <td style="padding:14px 18px;">
                                    <p style="color:#6b7280; font-size:10px; text-transform:uppercase;
                                               letter-spacing:0.10em; font-weight:700; margin:0 0 6px;">
                                        {{ __('admin.Messaggio') }}
                                    </p>
                                    <p style="color:#374151; font-size:14px; font-style:italic; margin:0; line-height:1.6;">
                                        &ldquo;{{ $content_mail['message'] }}&rdquo;
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif

                {{-- ================================================================
                     CTA ADMIN
                     ================================================================ --}}
                @if ($mailTo == 'admin')
                <tr>
                    <td style="padding:20px 40px 8px;">
                        <a href="tel:{{ $content_mail['phone'] }}"
                           style="display:block; text-align:center; padding:14px 20px; background-color:#159478;
                                  color:#ffffff; font-size:15px; font-weight:700; text-decoration:none;
                                  border-radius:9px; margin-bottom:8px;">
                            &#128222; {{ __('admin.emails.call_name', ['name' => $content_mail['name']]) }}
                        </a>
                        @if ($content_mail['type'] == 'or')
                            <a href="{{ config('configurazione.APP_URL') }}/admin/orders/{{ $content_mail['order_id'] }}"
                               style="display:block; text-align:center; padding:14px 20px; background-color:#0f0b2e;
                                      color:#ffffff; font-size:15px; font-weight:700; text-decoration:none;
                                      border-radius:9px;">
                                {{ __('admin.Visualizza_nella_Dashboard') }}
                            </a>
                        @elseif ($content_mail['type'] == 'res')
                            <a href="{{ config('configurazione.APP_URL') }}/admin/reservations/{{ $content_mail['res_id'] }}"
                               style="display:block; text-align:center; padding:14px 20px; background-color:#0f0b2e;
                                      color:#ffffff; font-size:15px; font-weight:700; text-decoration:none;
                                      border-radius:9px;">
                                {{ __('admin.Visualizza_nella_Dashboard') }}
                            </a>
                        @endif
                    </td>
                </tr>
                @endif

                {{-- ================================================================
                     LINK ANNULLA (utente)
                     ================================================================ --}}
                @if (config('configurazione.subscription') > 2
                    && $mailTo == 'user'
                    && !in_array($content_mail['status'], [0, 6]))
                @php
                    $cancelId  = $content_mail['type'] === 'or'
                        ? ($content_mail['order_id'] ?? null)
                        : ($content_mail['res_id']   ?? null);
                    $cancelUrl = $cancelId
                        ? \Illuminate\Support\Facades\URL::signedRoute('api.client_default', [
                            'type' => $content_mail['type'],
                            'id'   => $cancelId,
                          ])
                        : null;
                @endphp
                @if ($cancelUrl)
                <tr>
                    <td align="center" style="padding:20px 40px 0;">
                        <p style="color:#6b7280; font-size:12px; margin:0 0 10px; line-height:1.5;">
                            {{ __('admin._Per_annullare_lordine_o_la_prenotazione_in_autonomia_premi_questo_bottone') }}
                        </p>
                        <a href="{{ $cancelUrl }}"
                           style="display:inline-block; padding:10px 28px; background-color:#b91c1c;
                                  color:#ffffff; font-size:14px; font-weight:700; text-decoration:none;
                                  border-radius:8px;">
                            {{ __('admin.Annulla') }}
                        </a>
                    </td>
                </tr>
                @endif
                @endif

                <!-- Spazio inferiore interno alla card -->
                <tr><td style="height:28px;">&nbsp;</td></tr>

            </table>
            {{-- /Card principale --}}

            <!-- ===== FOOTER ===== -->
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
                   style="max-width:600px; width:100%; background-color:#0f0b2e; border-radius:0 0 14px 14px; margin-top:0;">
                <tr>
                    <td align="center" style="padding:28px 24px 24px;">

                        @if ($mailTo == 'user' && $content_mail['status'] !== 0)
                            <p style="color:rgba(255,255,255,0.65); font-size:13px; margin:0 0 14px; line-height:1.6;">
                                {{ __('admin.contatta_tel_mail', ['name' => $appName]) }}
                            </p>
                            <a href="tel:{{ $content_mail['admin_phone'] }}"
                               style="display:inline-block; padding:11px 28px; background-color:#ffffff;
                                      color:#0f0b2e; font-size:15px; font-weight:800; text-decoration:none;
                                      border-radius:8px; margin-bottom:22px;">
                                {{ __('admin.Chiama') }} {{ $appName }}
                            </a>
                        @endif

                        <p style="color:rgba(255,255,255,0.4); font-size:11px; margin:0 0 5px; line-height:1.6;">
                            {{ __('admin.end_copy', ['name' => $appName]) }}
                        </p>
                        <p style="color:rgba(255,255,255,0.35); font-size:11px; margin:0;">
                            Powered by
                            <a href="https://future-plus.it"
                               style="color:rgba(255,255,255,0.6); text-decoration:none; font-weight:700;">
                                Future +
                            </a>
                        </p>

                    </td>
                </tr>
            </table>
            {{-- /Footer --}}

        </td>
    </tr>
</table>

</body>
</html>
