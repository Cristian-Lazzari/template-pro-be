@extends('layouts.base')

@section('contents')
@if (session('success'))
    @php $data = session('success') @endphp
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $data }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@php
$pack = ['', 'Essentials', 'Work on', 'Boost up', 'Prova gratuita', 'Boost up+'];
$subscription = (int) config('configurazione.subscription', 1);
$adv = json_decode($setting['advanced']->property, 1) ?: [];
$paymentMethods = is_array($adv['method'] ?? null) ? $adv['method'] : [];

$tavoliStatus   = (int) $setting['Prenotazione Tavoli']['status'];
$asportoStatus  = (int) $setting['Prenotazione Asporti']['status'];
$domicilioStatus= (int) $setting['Possibilità di consegna a domicilio']['status'];
$ferieStatus    = (int) $setting['Periodo di Ferie']['status'];
$promoStatus    = (int) $setting['Promozione Tavoli']['status'];

$tavoliState = match ($tavoliStatus) {
    2 => ['label' => __('admin.settings.online_status'),   'tone' => 'active'],
    1 => ['label' => __('admin.settings.phone_status'), 'tone' => 'warning'],
    default => ['label' => __('admin.common.disabled'), 'tone' => 'off'],
};
$asportoState = match ($asportoStatus) {
    2 => ['label' => __('admin.settings.online_status'),   'tone' => 'active'],
    1 => ['label' => __('admin.settings.phone_status'), 'tone' => 'warning'],
    default => ['label' => __('admin.common.disabled'), 'tone' => 'off'],
};
$domicilioState = match ($domicilioStatus) {
    1 => ['label' => __('admin.settings.active_status_short'), 'tone' => 'active'],
    default => ['label' => __('admin.common.disabled'), 'tone' => 'off'],
};
$ferieState = $ferieStatus === 1
    ? ['label' => __('admin.settings.on_holiday'),  'tone' => 'warning']
    : ['label' => __('admin.settings.operational_status_short'), 'tone' => 'active'];
$promoState = $promoStatus === 1
    ? ['label' => __('admin.common.active_status'), 'tone' => 'active']
    : ['label' => __('admin.common.disabled'), 'tone' => 'off'];

$menuFixState = match ((string) ($adv['menu_fix_set'] ?? '0')) {
    '0' => ['label' => __('admin.Menu_fisso'),   'tone' => 'neutral'],
    '1' => ['label' => __('admin.Tutti'),   'tone' => 'active'],
    '2' => ['label' => __('admin.Menu_alla_carta'),   'tone' => 'neutral'],
    default => ['label' => __('admin.common.default'), 'tone' => 'neutral'],
};
$servicesState = match ((string) ($adv['services'] ?? '4')) {
    '2' => ['label' => __('admin.common.table'),  'tone' => 'active'],
    '3' => ['label' => __('admin.common.takeaway'), 'tone' => 'active'],
    '4' => ['label' => __('admin.Tutti'),   'tone' => 'active'],
    default => ['label' => __('admin.common.custom'), 'tone' => 'warning'],
};
$takeawayTypeState = ((int) ($adv['too'] ?? 0)) === 1
    ? ['label' => __('admin.settings.separate'), 'tone' => 'active']
    : ['label' => __('admin.settings.single'),    'tone' => 'neutral'];
$doubleRoomState = ((int) ($adv['dt'] ?? 0)) === 1
    ? ['label' => __('admin.common.active_status'), 'tone' => 'active']
    : ['label' => __('admin.common.disabled'), 'tone' => 'off'];

$asporto_p   = json_decode($setting['Prenotazione Asporti']['property'], 1);
$domicilio_p = json_decode($setting['Possibilità di consegna a domicilio']['property'], 1);
$ferieProp   = json_decode($setting['Periodo di Ferie']['property'], true);
$promo_table = json_decode($setting['Promozione Tavoli']['property'], true);
$languageSetting = json_decode($setting['Lingua']['property'], 1) ?: [];
$languages = is_array($languageSetting['languages'] ?? null) ? $languageSetting['languages'] : ['it'];
$languages = array_values(array_filter($languages, fn ($language) => is_string($language) && trim($language) !== ''));
$languages = empty($languages) ? ['it'] : $languages;
$activeLocale = $languageSetting['default'] ?? config('configurazione.default_lang') ?? app()->getLocale() ?? config('app.locale') ?? 'it';
$activeLocale = is_string($activeLocale) && trim($activeLocale) !== '' ? trim($activeLocale) : ($languages[0] ?? 'it');
$property_orari     = json_decode($setting['Orari di attività']['property'], true);
$property_posizione = json_decode($setting['Posizione']['property'], true);
$property_contatti  = json_decode($setting['Contatti']['property'], true);

$toneClass = fn(string $tone) => match($tone) {
    'active'  => 'stt-pill--active-border',
    'warning' => 'stt-pill--warning-border',
    default   => 'stt-pill--off-border',
};
@endphp

<div class="dash_page settings-page">

    {{-- HEADER — invariato --}}
    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-gear-wide-connected"></i>
                </span>
                <strong>{{ __('admin.Impostazioni') }}</strong>
            </div>
            <h1 class="menu-dashboard__title">{{ __('admin.Impostazioni') }}</h1>
        </div>
        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.dashboard') }}" class="order-detail__contact">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>{{ __('admin.nav.dashboard') }}</span>
            </a>
        </div>
    </header>

    <style>
    /* ═══════════════════════════════════════════════════════
       SETTINGS PAGE — Design System v2
       Variabili CSS centralizzate + classi semantiche
       Prefissi: stt- (generale), ops- (modale avanzate), loc- (dettagli locale)
    ═══════════════════════════════════════════════════════ */

    :root {
        --stt-bg-card:       #1a1f4e;
        --stt-bg-deep:       #141830;
        --stt-bg-elevated:   rgba(255,255,255,.045);
        --stt-border:        rgba(45,212,191,.13);
        --stt-border-subtle: rgba(255,255,255,.08);
        --stt-accent:        #2DD4BF;
        --stt-accent-dim:    rgba(45,212,191,.15);
        --stt-text-primary:  #e2e8f0;
        --stt-text-muted:    #64748b;
        --stt-text-dim:      #94a3b8;
        --stt-active:        #10b981;
        --stt-active-dim:    rgba(16,185,129,.12);
        --stt-active-border: rgba(16,185,129,.45);
        --stt-warning:       #f59e0b;
        --stt-warning-dim:   rgba(245,158,11,.1);
        --stt-warning-border:rgba(245,158,11,.45);
        --stt-error:         #ef4444;
        --stt-error-dim:     rgba(239,68,68,.08);
        --stt-error-border:  rgba(239,68,68,.4);
        --stt-radius-lg:     16px;
        --stt-radius-md:     10px;
        --stt-radius-sm:     8px;
        --stt-radius-pill:   50px;
        --stt-touch:         44px;
        --stt-gap:           1.75rem;
        --stt-gap-sm:        1rem;
    }

    /* ── Reset e wrapper ── */
    /* Contiene tutto l'overflow della pagina settings a livello di dash_page */
    .dash_page.settings-page{overflow-x:hidden;max-width:100%}
    .stt-wrap{width:100%;padding:0 0 6rem;font-size:16px;box-sizing:border-box;overflow-x:hidden;min-width:0}
    .stt-wrap *,.stt-wrap *::before,.stt-wrap *::after{box-sizing:border-box}
    @media(max-width:600px){.stt-wrap{font-size:15px}}

    /* ── Layout griglia principale ── */
    .stt-layout{display:grid;grid-template-columns:1fr 260px;gap:var(--stt-gap);align-items:start}
    @media(max-width:960px){.stt-layout{grid-template-columns:1fr}}

    /* ── Sezioni ── */
    .stt-section{margin-bottom:var(--stt-gap)}

    /* ── Label di sezione ── */
    .stt-label{
        display:flex;align-items:center;gap:.5rem;
        font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
        color:var(--stt-accent);margin-bottom:.75rem;opacity:.85
    }
    .stt-label i{font-size:14px;opacity:.7}

    /* ── Card base ── */
    .stt-card{
        background:var(--stt-bg-card);
        border:1px solid var(--stt-border);
        border-radius:var(--stt-radius-lg);
        padding:1.5rem
    }
    @media(max-width:600px){.stt-card{padding:1.1rem 1rem;border-radius:12px}}
    .stt-card-flush{padding:0;overflow:hidden}
    .stt-card--elevated{background:rgba(26,31,78,.85);border-color:rgba(45,212,191,.2)}

    /* ── Section header interno (titolo + badge affiancati) ── */
    .stt-sec-head{
        display:flex;align-items:center;justify-content:space-between;
        margin-bottom:1.25rem;padding-bottom:.9rem;
        border-bottom:1px solid var(--stt-border-subtle)
    }
    .stt-sec-head__title{font-size:15px;font-weight:700;color:var(--stt-text-primary);display:flex;align-items:center;gap:.5rem}
    .stt-sec-head__title i{color:var(--stt-accent);font-size:16px}

    /* ── Pills stato operativo (legacy — mantenute per compatibilità JS) ── */
    .stt-pill{display:none} /* nascosti: sostituiti da stt-op-card */
    .stt-state--active{color:var(--stt-active)}
    .stt-state--warning{color:var(--stt-warning)}
    .stt-state--off{color:var(--stt-error)}
    .stt-state--neutral{color:var(--stt-text-dim)}
    .stt-pill--active-border{}
    .stt-pill--warning-border{}
    .stt-pill--off-border{}

    /* ── Status card grid (nuovo layout stato operativo) ── */
    .stt-op-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:.75rem;
        width:100%;overflow:hidden
    }
    @media(max-width:600px){
        .stt-op-grid{grid-template-columns:1fr;gap:.5rem}
    }

    .stt-op-card{
        display:flex;align-items:center;gap:.75rem;
        padding:.75rem .9rem;
        border-radius:var(--stt-radius-md);
        border:1px solid var(--stt-border-subtle);
        border-left:4px solid var(--stt-border-subtle);
        background:var(--stt-bg-elevated);
        cursor:pointer;user-select:none;
        min-height:52px;overflow:hidden;
        transition:border-color .18s,background .18s,transform .15s,box-shadow .18s;
        position:relative
    }
    .stt-op-card:hover{
        background:rgba(255,255,255,.07);
        transform:translateY(-1px);
        box-shadow:0 4px 12px rgba(0,0,0,.25)
    }
    .stt-op-card:active{transform:translateY(0) scale(.985)}

    /* bordo sinistro per tono */
    .stt-op-card.stt-pill--active-border{
        border-left-color:var(--stt-active);
        background:var(--stt-active-dim);
        border-color:var(--stt-active-border);
        border-left-color:var(--stt-active)
    }
    .stt-op-card.stt-pill--warning-border{
        border-left-color:var(--stt-warning);
        background:var(--stt-warning-dim);
        border-color:var(--stt-warning-border);
        border-left-color:var(--stt-warning)
    }
    .stt-op-card.stt-pill--off-border{
        border-left-color:var(--stt-error);
        background:var(--stt-error-dim);
        border-color:var(--stt-error-border);
        border-left-color:var(--stt-error)
    }

    .stt-op-card__icon{
        flex-shrink:0;
        width:38px;height:38px;
        display:flex;align-items:center;justify-content:center;
        border-radius:var(--stt-radius-sm);
        background:rgba(255,255,255,.06);
        font-size:17px;color:var(--stt-text-dim)
    }
    .stt-op-card.stt-pill--active-border .stt-op-card__icon{color:var(--stt-active);background:rgba(16,185,129,.14)}
    .stt-op-card.stt-pill--warning-border .stt-op-card__icon{color:var(--stt-warning);background:rgba(245,158,11,.14)}
    .stt-op-card.stt-pill--off-border .stt-op-card__icon{color:var(--stt-error);background:rgba(239,68,68,.12)}

    .stt-op-card__body{flex:1;min-width:0;overflow:hidden}
    .stt-op-card__name{
        font-size:13.5px;font-weight:600;
        color:var(--stt-text-primary);
        line-height:1.2;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis
    }

    .stt-op-card__badge{
        flex-shrink:0;
        font-size:11px;font-weight:700;
        letter-spacing:.03em;
        padding:.25rem .6rem;
        border-radius:50px;
        white-space:nowrap;
        background:rgba(255,255,255,.06);
        color:var(--stt-text-dim);
        border:1px solid transparent
    }
    /* badge colorati per tono — le classi stt-state-- rimangono sul badge per il JS */
    .stt-op-card.stt-pill--active-border .stt-op-card__badge{
        background:rgba(16,185,129,.18);
        color:var(--stt-active);
        border-color:var(--stt-active-border)
    }
    .stt-op-card.stt-pill--warning-border .stt-op-card__badge{
        background:rgba(245,158,11,.15);
        color:var(--stt-warning);
        border-color:var(--stt-warning-border)
    }
    .stt-op-card.stt-pill--off-border .stt-op-card__badge{
        background:rgba(239,68,68,.13);
        color:var(--stt-error);
        border-color:var(--stt-error-border)
    }

    /* ── Micro-feedback ── */
    .stt-loading{opacity:.5;pointer-events:none}
    @keyframes stt-ok{0%,100%{box-shadow:none}50%{box-shadow:0 0 0 3px rgba(16,185,129,.45)}}
    @keyframes stt-err{0%,100%{box-shadow:none}50%{box-shadow:0 0 0 3px rgba(239,68,68,.45)}}
    .stt-flash-ok{animation:stt-ok .6s ease}
    .stt-flash-err{animation:stt-err .6s ease;border-color:var(--stt-error-border)!important}
    .stt-inline-err{font-size:13px;color:var(--stt-error);margin-top:.35rem;display:none;font-weight:500}

    /* ── Lingua e valuta ── */
    .stt-locale-grid{display:grid;grid-template-columns:1fr 1fr;gap:var(--stt-gap)}
    @media(max-width:600px){.stt-locale-grid{grid-template-columns:1fr;gap:1.25rem}}
    .stt-sublabel{font-size:11px;color:var(--stt-text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.65rem}
    .stt-lang-pills{display:flex;flex-wrap:wrap;gap:.4rem}
    .stt-lang-pill{
        padding:.5rem 1rem;border-radius:var(--stt-radius-pill);
        border:1.5px solid var(--stt-border-subtle);
        background:var(--stt-bg-elevated);color:var(--stt-text-dim);
        font-size:14px;font-weight:700;cursor:pointer;
        transition:all .15s;line-height:1.4;
        min-height:var(--stt-touch);display:inline-flex;align-items:center
    }
    .stt-lang-pill:hover{border-color:rgba(45,212,191,.4);color:var(--stt-text-primary)}
    .stt-lang-pill.active{border-color:var(--stt-accent);background:var(--stt-accent-dim);color:var(--stt-accent)}
    .stt-select{
        width:100%;background:var(--stt-bg-elevated);
        border:1.5px solid var(--stt-border-subtle);
        color:#fff;padding:.6rem .85rem;
        border-radius:var(--stt-radius-md);
        font-size:15px;appearance:none;cursor:pointer;
        transition:border-color .15s;
        min-height:var(--stt-touch)
    }
    .stt-select:focus{outline:none;border-color:var(--stt-accent)}
    .stt-select option{background:var(--stt-bg-card);color:#fff}
    .stt-select.stt-flash-ok{border-color:var(--stt-active)}

    /* ── Servizi grid ── */
    .stt-services-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:var(--stt-gap-sm)}
    @media(max-width:900px){.stt-services-grid{grid-template-columns:1fr 1fr}}
    @media(max-width:600px){.stt-services-grid{grid-template-columns:1fr}}
    .stt-svc-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:1rem;padding-bottom:.8rem;border-bottom:1px solid var(--stt-border-subtle)}
    .stt-svc-title{font-size:16px;font-weight:700;color:var(--stt-text-primary)}
    .stt-svc-toggle{
        display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;
        padding:.35rem .75rem;border-radius:var(--stt-radius-pill);
        border:1.5px solid var(--stt-border-subtle);
        transition:all .15s;font-size:13px;font-weight:700;
        user-select:none;white-space:nowrap;min-height:var(--stt-touch)
    }
    .stt-svc-toggle:hover{opacity:.8}

    /* ── Campi form ── */
    .stt-field{margin-bottom:1rem}
    .stt-field:last-child{margin-bottom:0}
    .stt-field-lbl{
        font-size:12px;color:var(--stt-text-muted);font-weight:600;
        margin-bottom:.4rem;display:block;text-transform:uppercase;letter-spacing:.06em
    }
    .stt-input{
        width:100%;background:var(--stt-bg-elevated);
        border:1.5px solid var(--stt-border-subtle);
        color:#fff;padding:.6rem .85rem;
        border-radius:var(--stt-radius-md);font-size:15px;
        transition:border-color .15s,background .15s;resize:vertical;
        min-height:var(--stt-touch)
    }
    .stt-input:focus{outline:none;border-color:var(--stt-accent);background:rgba(255,255,255,.07)}
    .stt-input::placeholder{color:rgba(255,255,255,.22)}
    .stt-input.stt-flash-ok{border-color:var(--stt-active)}
    .stt-input.stt-flash-err{border-color:var(--stt-error)}

    /* ── Bottoni pagamento ── */
    .stt-pay-row{display:flex;gap:.4rem;flex-wrap:wrap}
    .stt-pay-btn{
        display:flex;align-items:center;justify-content:center;gap:.35rem;
        padding:.5rem .75rem;border-radius:var(--stt-radius-sm);
        border:1.5px solid var(--stt-border-subtle);background:var(--stt-bg-elevated);
        color:var(--stt-text-dim);cursor:pointer;font-size:14px;
        transition:all .15s;white-space:nowrap;min-height:var(--stt-touch)
    }
    .stt-pay-btn:hover{border-color:rgba(255,255,255,.25);color:var(--stt-text-primary)}
    .stt-pay-btn.active{border-color:var(--stt-accent);background:var(--stt-accent-dim);color:var(--stt-accent)}

    /* ── Toggle switch ── */
    .stt-sw-row{display:flex;align-items:center;gap:1rem;margin-bottom:1.1rem}
    .stt-sw{position:relative;width:48px;height:28px;cursor:pointer;flex-shrink:0}
    .stt-sw input{opacity:0;width:0;height:0;position:absolute}
    .stt-sw-track{
        position:absolute;inset:0;
        background:rgba(255,255,255,.1);border:1.5px solid rgba(255,255,255,.15);
        border-radius:var(--stt-radius-pill);
        transition:background .2s,border-color .2s
    }
    .stt-sw-track::after{
        content:'';position:absolute;top:4px;left:4px;
        width:16px;height:16px;background:var(--stt-text-dim);
        border-radius:50%;transition:transform .2s,background .2s
    }
    .stt-sw input:checked~.stt-sw-track{background:var(--stt-active-dim);border-color:var(--stt-active)}
    .stt-sw input:checked~.stt-sw-track::after{transform:translateX(20px);background:var(--stt-active)}
    .stt-sw-lbl{font-size:16px;font-weight:600;color:var(--stt-text-primary);margin:0}
    .stt-sw-sub{font-size:13px;color:var(--stt-text-muted);margin:.2rem 0 0}

    /* ── Two-col (ferie + promo) — collassa naturalmente ── */
    .stt-two-col{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:var(--stt-gap-sm)}
    .stt-date-row{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
    .stt-date-row .stt-input{flex:1;min-width:120px}
    .stt-date-sep{color:var(--stt-text-muted);font-size:16px;flex-shrink:0}

    /* ═══════════════════════════════════════
       SEZIONE 6 — Dettagli locale: Tab layout
    ═══════════════════════════════════════ */
    .loc-tabs{
        display:flex;gap:.35rem;flex-wrap:nowrap;
        overflow-x:auto;overflow-y:hidden;
        -webkit-overflow-scrolling:touch;
        scrollbar-width:none;
        margin-bottom:1rem;
        border-bottom:1px solid var(--stt-border-subtle);
        padding-bottom:.75rem
    }
    .loc-tabs::-webkit-scrollbar{display:none}
    .loc-tab{
        display:inline-flex;align-items:center;gap:.4rem;
        padding:.5rem 1rem;border-radius:var(--stt-radius-sm);
        border:1.5px solid transparent;
        background:transparent;color:var(--stt-text-muted);
        font-size:13px;font-weight:600;cursor:pointer;flex-shrink:0;
        transition:all .15s;min-height:var(--stt-touch);white-space:nowrap
    }
    .loc-tab:hover{color:var(--stt-text-dim);background:var(--stt-bg-elevated)}
    .loc-tab.active{
        color:var(--stt-accent);background:var(--stt-accent-dim);
        border-color:rgba(45,212,191,.3)
    }
    .loc-tab i{font-size:15px}
    .loc-panel{display:none;padding:clamp(.75rem,3vw,1.5rem)}
    .loc-panel.active{display:block}

    /* Griglia contatti — collassa naturalmente senza media query */
    .loc-contact-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.65rem}

    /* Input group restyle per il form locale */
    .loc-field{margin-bottom:.85rem}
    .loc-field:last-of-type{margin-bottom:0}
    .loc-field label{
        display:block;font-size:12px;font-weight:600;color:var(--stt-text-muted);
        text-transform:uppercase;letter-spacing:.06em;margin-bottom:.35rem
    }
    .loc-field .stt-input{min-height:var(--stt-touch)}
    .loc-field input[type="file"]{
        width:100%;padding:.55rem .75rem;
        background:var(--stt-bg-elevated);
        border:1.5px dashed var(--stt-border-subtle);
        border-radius:var(--stt-radius-md);
        color:var(--stt-text-dim);font-size:14px;cursor:pointer
    }
    .loc-field input[type="file"]:focus{outline:none;border-color:var(--stt-accent)}

    /* Griglia giorni/orari */
    .loc-days-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
    @media(max-width:500px){.loc-days-grid{grid-template-columns:1fr}}
    .loc-day-row{display:flex;align-items:stretch;border-radius:var(--stt-radius-md);overflow:hidden;border:1.5px solid var(--stt-border-subtle)}
    .loc-day-lbl{
        flex-shrink:0;width:88px;padding:.55rem .7rem;
        background:rgba(255,255,255,.04);
        color:var(--stt-text-dim);font-size:13px;font-weight:600;
        display:flex;align-items:center;
        border-right:1.5px solid var(--stt-border-subtle)
    }
    @media(max-width:500px){.loc-day-lbl{width:76px;font-size:12px}}
    .loc-day-input{
        flex:1;min-width:0;background:var(--stt-bg-elevated);
        border:none;color:#fff;padding:.55rem .7rem;font-size:14px;
        min-height:var(--stt-touch)
    }
    .loc-day-input:focus{outline:none;background:rgba(255,255,255,.08)}
    .loc-day-input::placeholder{color:rgba(255,255,255,.2)}

    /* Address list (comuni / whatsapp) */
    .loc-address-list{
        display:flex;flex-direction:column;gap:.4rem;
        margin-bottom:1rem;max-height:180px;overflow-y:auto
    }
    .loc-address-list span{
        display:block;padding:.5rem .75rem;
        background:var(--stt-bg-elevated);
        border:1px solid var(--stt-border-subtle);
        border-radius:var(--stt-radius-sm);
        font-size:13px;color:var(--stt-text-dim)
    }
    .loc-actions{display:flex;gap:.5rem;flex-wrap:wrap}

    /* Submit unico in fondo al form locale */
    .loc-submit-bar{
        display:flex;align-items:center;justify-content:flex-end;
        padding:1rem 1.5rem;
        border-top:1px solid var(--stt-border-subtle);
        background:rgba(0,0,0,.1)
    }
    @media(max-width:600px){.loc-submit-bar{padding:.8rem 1rem}}

    /* ═══════════════════════════════════════
       ACCORDION FALLBACK (mantenuto per BS)
    ═══════════════════════════════════════ */
    .stt-accordion .accordion-item{background:var(--stt-bg-card);border:none;border-bottom:1px solid var(--stt-border-subtle)}
    .stt-accordion .accordion-item:last-child{border-bottom:none}
    .stt-accordion .accordion-button{background:var(--stt-bg-card);color:var(--stt-text-primary);font-size:16px;font-weight:600;padding:1.1rem 1.5rem;box-shadow:none}
    @media(max-width:600px){.stt-accordion .accordion-button{font-size:15px;padding:.9rem 1rem}}
    .stt-accordion .accordion-button:not(.collapsed){background:rgba(45,212,191,.06);color:var(--stt-accent)}
    .stt-accordion .accordion-button::after{filter:brightness(0) invert(1);opacity:.4}
    .stt-accordion .accordion-body{background:rgba(0,0,0,.12);padding:1.1rem 1.5rem}
    @media(max-width:600px){.stt-accordion .accordion-body{padding:.9rem 1rem}}

    /* ═══════════════════════════════════════
       SIDEBAR
    ═══════════════════════════════════════ */
    .stt-sidebar{display:flex;flex-direction:column;gap:1rem}
    @media(max-width:960px){.stt-sidebar{display:none}}
    .stt-pack-card{display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1.4rem 1rem;text-align:center}
    .stt-pack-card .stt-logo{width:48px;height:48px;border-radius:10px;object-fit:cover}
    .stt-pack-card h2{font-size:17px;font-weight:700;color:#fff;margin:0}
    .stt-pack-link{font-size:13px;color:var(--stt-text-muted);display:inline-flex;align-items:center;gap:.35rem;margin-top:.15rem}
    .stt-note{font-size:13px;color:var(--stt-text-muted);margin-top:.5rem;line-height:1.5}

    /* ── Strip mobile (sidebar compatta, visibile solo <960px) ── */
    .stt-mobile-strip{display:none}
    @media(max-width:960px){
        .stt-mobile-strip{
            display:flex;align-items:center;justify-content:space-between;
            gap:1rem;flex-wrap:wrap;
            background:var(--stt-bg-card);border:1px solid var(--stt-border);
            border-radius:var(--stt-radius-lg);padding:1rem 1.25rem;
            margin-bottom:var(--stt-gap)
        }
        .stt-mobile-strip__brand{display:flex;align-items:center;gap:.65rem}
        .stt-mobile-strip__brand img{width:32px;height:32px;border-radius:8px;object-fit:cover}
        .stt-mobile-strip__brand-text{display:flex;flex-direction:column;gap:.1rem}
        .stt-mobile-strip__name{font-size:14px;font-weight:700;color:var(--stt-text-primary)}
        .stt-mobile-strip__pack{font-size:12px;color:var(--stt-text-muted)}
    }

    /* ── Footer sezione avanzate ── */
    .stt-footer{text-align:center;padding:1.75rem 0 .5rem}

    /* ═══════════════════════════════════════
       MODALE IMPOSTAZIONI AVANZATE
       Prefisso: ops-
    ═══════════════════════════════════════ */
    .ops-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem}
    @media(max-width:640px){.ops-grid{grid-template-columns:1fr}}

    /* Card singola blocco avanzate */
    .ops-card{
        background:rgba(255,255,255,.03);
        border:1px solid var(--stt-border-subtle);
        border-radius:var(--stt-radius-md);
        padding:1.1rem 1.25rem
    }
    .ops-card__head{
        display:flex;align-items:center;justify-content:space-between;
        margin-bottom:.85rem;padding-bottom:.7rem;
        border-bottom:1px solid var(--stt-border-subtle)
    }
    .ops-card__title{font-size:13px;font-weight:700;color:var(--stt-text-primary);display:flex;align-items:center;gap:.4rem}
    .ops-card__title i{color:var(--stt-accent);font-size:14px}
    .ops-badge{
        display:inline-flex;align-items:center;
        padding:.2rem .65rem;border-radius:var(--stt-radius-pill);
        font-size:11px;font-weight:700;letter-spacing:.04em
    }
    .ops-badge--active{background:var(--stt-active-dim);color:var(--stt-active);border:1px solid var(--stt-active-border)}
    .ops-badge--warning{background:var(--stt-warning-dim);color:var(--stt-warning);border:1px solid var(--stt-warning-border)}
    .ops-badge--off{background:var(--stt-error-dim);color:var(--stt-error);border:1px solid var(--stt-error-border)}
    .ops-badge--neutral{background:rgba(255,255,255,.06);color:var(--stt-text-dim);border:1px solid var(--stt-border-subtle)}

    /* Radio pills all'interno dei blocchi */
    .ops-radio-group{display:flex;flex-wrap:wrap;gap:.4rem}
    .ops-radio-label{
        display:inline-flex;align-items:center;gap:.35rem;
        padding:.45rem .85rem;border-radius:var(--stt-radius-pill);
        border:1.5px solid var(--stt-border-subtle);
        background:transparent;color:var(--stt-text-dim);
        font-size:13px;font-weight:600;cursor:pointer;
        transition:all .15s;min-height:var(--stt-touch)
    }
    .ops-radio-label:hover{border-color:rgba(45,212,191,.35);color:var(--stt-text-primary)}
    .ops-radio-label input[type="radio"]{display:none}
    .ops-radio-label:has(input:checked){
        border-color:var(--stt-accent);
        background:var(--stt-accent-dim);
        color:var(--stt-accent)
    }
    /* Fallback per browser senza :has() */
    .ops-radio-label input:checked + span{color:var(--stt-accent)}

    /* Input coppie (sale, dati legali) */
    .ops-pair{display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-top:.75rem}
    @media(max-width:480px){.ops-pair{grid-template-columns:1fr}}
    .ops-field{display:flex;flex-direction:column;gap:.3rem}
    .ops-field label{font-size:11px;font-weight:700;color:var(--stt-text-muted);text-transform:uppercase;letter-spacing:.07em}
    .ops-field input{
        background:var(--stt-bg-elevated);
        border:1.5px solid var(--stt-border-subtle);
        border-radius:var(--stt-radius-md);
        color:var(--stt-text-primary);
        padding:.55rem .8rem;font-size:14px;
        transition:border-color .15s;
        min-height:var(--stt-touch);width:100%
    }
    .ops-field input:focus{outline:none;border-color:var(--stt-accent);background:rgba(255,255,255,.07)}
    .ops-field input::placeholder{color:rgba(255,255,255,.2)}

    /* Blocco info legali — occupa intera riga */
    .ops-legal{
        background:rgba(255,255,255,.03);
        border:1px solid var(--stt-border-subtle);
        border-radius:var(--stt-radius-md);
        padding:1.1rem 1.25rem;margin-bottom:1rem
    }
    .ops-legal__title{
        font-size:12px;font-weight:700;color:var(--stt-accent);
        text-transform:uppercase;letter-spacing:.08em;
        margin-bottom:.9rem;display:flex;align-items:center;gap:.4rem
    }
    .ops-legal__title i{font-size:14px}
    .ops-legal-grid{display:grid;grid-template-columns:1fr 1fr;gap:.65rem}
    @media(max-width:480px){.ops-legal-grid{grid-template-columns:1fr}}

    /* Metodi di pagamento */
    .ops-methods-label{
        font-size:12px;font-weight:700;color:var(--stt-text-muted);
        text-transform:uppercase;letter-spacing:.07em;margin-bottom:.65rem
    }
    .ops-methods-wrap{display:flex;flex-wrap:wrap;gap:.5rem}
    .ops-method-item{position:relative}
    .ops-method-item input[type="checkbox"]{
        position:absolute;opacity:0;width:1px;height:1px
    }
    .ops-method-item label{
        display:flex;align-items:center;justify-content:center;
        padding:.45rem .6rem;
        border-radius:var(--stt-radius-sm);
        border:1.5px solid var(--stt-border-subtle);
        background:rgba(255,255,255,.04);
        cursor:pointer;transition:all .15s;
        min-width:52px;min-height:var(--stt-touch)
    }
    .ops-method-item label:hover{border-color:rgba(255,255,255,.25);background:rgba(255,255,255,.08)}
    .ops-method-item input:checked + label{
        border-color:var(--stt-accent);background:var(--stt-accent-dim)
    }
    .ops-method-item label .bi{font-size:20px;color:var(--stt-text-dim)}
    .ops-method-item input:checked + label .bi{color:var(--stt-accent)}
    .payment-icon{display:block;filter:brightness(.85)}
    .ops-method-item input:checked + label .payment-icon{filter:brightness(1)}

    /* ── Modale impostazioni avanzate — dimensioni ── */
    .settings-modal-dialog{
        max-width: min(95vw, 1080px) !important;
        width: 95vw !important;
        margin: 1.5rem auto !important;
    }
    /* Garantisce che il body del modale scrolli senza superare il viewport */
    .settings-modal-dialog .modal-content{
        max-height: calc(100dvh - 3rem);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .settings-modal-dialog .modal-body{
        overflow-y: auto;
        flex: 1 1 auto;
        min-height: 0;
    }
    @media(max-width:600px){
        .settings-modal-dialog{ margin: .5rem auto !important; width:99vw !important; }
        .settings-modal-dialog .modal-content{ max-height: calc(100dvh - 1rem); }
    }

    /* Warning critico */
    .ops-warning{
        display:none;text-align:center;
        padding:.75rem 1rem;margin-top:.75rem;
        background:var(--stt-error-dim);
        border:1px solid var(--stt-error-border);
        border-radius:var(--stt-radius-md);
        color:var(--stt-error);font-size:14px;font-weight:600
    }

    .settings-page :is(.menu-dashboard__hero,.stt-wrap,.stt-layout,.stt-main,.stt-section,.stt-card,.stt-card-flush,.stt-services-grid,.stt-locale-grid,.stt-two-col,.loc-panel,.ops-grid,.ops-card,.ops-legal,.dashboard-action-modal__content){min-width:0}
    .dash_page.settings-page{width:100%;max-width:100%;gap:clamp(14px,2vw,20px)}
    .stt-layout{width:100%;grid-template-columns:minmax(0,1fr) minmax(220px,260px)}
    .stt-main{width:100%;min-width:0}
    .stt-op-grid{grid-template-columns:repeat(auto-fit,minmax(min(100%,210px),1fr))}
    .stt-op-card,.stt-svc-toggle{min-width:0}
    .stt-op-card__badge{max-width:48%;overflow:hidden;text-overflow:ellipsis}
    .stt-pill--active-border{border-color:var(--stt-active-border)!important;background:var(--stt-active-dim)!important}
    .stt-pill--warning-border{border-color:var(--stt-warning-border)!important;background:var(--stt-warning-dim)!important}
    .stt-pill--off-border{border-color:var(--stt-error-border)!important;background:var(--stt-error-dim)!important}
    .stt-op-card.stt-pill--active-border{border-left-color:var(--stt-active)!important}
    .stt-op-card.stt-pill--warning-border{border-left-color:var(--stt-warning)!important}
    .stt-op-card.stt-pill--off-border{border-left-color:var(--stt-error)!important}
    .stt-locale-grid,.stt-services-grid{grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr))}
    .stt-pay-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(108px,1fr))}
    .stt-pay-btn{width:100%;min-width:0;white-space:normal;text-align:center}
    .stt-date-row{display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr)}
    .stt-date-row .stt-input{min-width:0}
    .loc-tab-nav{padding:1.25rem 1.5rem 0}
    .loc-tab{max-width:100%;min-width:max-content}
    .loc-contact-grid,.loc-days-grid{grid-template-columns:repeat(auto-fit,minmax(min(100%,230px),1fr))}
    .loc-day-row{min-width:0}
    .loc-day-lbl{min-width:76px}
    .loc-map-preview{display:block;width:100%;max-height:160px;object-fit:cover;border-radius:10px;margin-bottom:1rem}
    .loc-address-list span,.stt-note,.ops-field input,.stt-input{overflow-wrap:anywhere}
    .loc-actions .my_btn_1{flex:1 1 160px}
    .loc-submit-bar{gap:.75rem}
    .loc-submit-bar .my_btn_1{margin-left:0}
    .stt-mobile-strip__brand,.stt-pack-link{min-width:0}
    .stt-mobile-strip__brand-text,.stt-pack-card h2{min-width:0;max-width:100%;overflow:hidden;text-overflow:ellipsis}
    .stt-pack-link__icon{width:13px;height:13px;flex:0 0 auto}
    .stt-empty-state{color:var(--stt-text-muted);font-style:italic}
    .stt-sublabel--social{margin-top:1rem;margin-bottom:.65rem}
    .stt-wa-icon{color:var(--stt-active)}
    .ops-grid,.ops-legal-grid,.ops-pair{grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr))}
    .ops-card--wide{grid-column:1/-1}
    .ops-radio-group--spaced{margin-bottom:.85rem}
    .ops-pair--flush{margin-top:0}
    .ops-legal--methods{margin-top:0}
    .ops-methods-label{margin-bottom:.75rem}
    .ops-methods-label .bi{color:var(--stt-accent)}
    .ops-methods-wrap{display:grid;grid-template-columns:repeat(auto-fit,minmax(56px,1fr))}
    .ops-method-item label{width:100%;min-width:0}
    .payment-icon{max-width:100%;height:auto}
    .settings-modal-dialog{max-width:min(1080px,calc(100vw - 24px))!important;width:min(1080px,calc(100vw - 24px))!important}
    .settings-basic-modal .check_c{display:flex;flex-wrap:wrap;gap:.5rem;min-width:0}
    .settings-basic-modal .check_c label{min-width:0;white-space:normal}
    @media(max-width:960px){
        .stt-layout{display:block}
        .stt-mobile-strip{min-width:0}
    }
    @media(max-width:640px){
        .stt-wrap{padding-bottom:4rem}
        .stt-mobile-strip{align-items:stretch;padding:.9rem}
        .stt-mobile-strip__brand{flex:1 1 180px}
        .stt-sec-head,.stt-svc-head,.stt-sw-row{align-items:flex-start}
        .stt-svc-toggle{width:100%;justify-content:center}
        .stt-date-row{grid-template-columns:1fr}
        .stt-date-sep{display:none}
        .loc-tab-nav{padding:1rem 1rem 0}
        .loc-panel{padding:1rem}
        .loc-submit-bar{align-items:stretch;flex-direction:column;padding:1rem}
        .loc-submit-bar .my_btn_1{width:100%}
        .settings-modal-dialog{width:calc(100vw - 12px)!important;max-width:calc(100vw - 12px)!important;margin:.5rem auto!important}
        .ops-card,.ops-legal{padding:1rem}
    }
    @media(max-width:380px){
        .stt-card{padding:.9rem}
        .stt-op-card{gap:.55rem;padding:.65rem}
        .stt-op-card__icon{width:34px;height:34px}
        .stt-op-card__name{font-size:13px}
        .stt-op-card__badge{font-size:10px;padding:.22rem .48rem}
        .loc-day-row{flex-direction:column}
        .loc-day-lbl{width:100%;border-right:0;border-bottom:1.5px solid var(--stt-border-subtle)}
    }
    </style>

    <div class="stt-wrap">

        {{-- ── Strip mobile (sidebar compatta, <960px) ── --}}
        <div class="stt-mobile-strip">
            <div class="stt-mobile-strip__brand">
                <a href="{{ config('configurazione.domain') }}">
                    <img src="{{ config('configurazione.domain') . '/img/favicon.png' }}" alt="">
                </a>
                <div class="stt-mobile-strip__brand-text">
                    <span class="stt-mobile-strip__name">{{ config('configurazione.APP_NAME') }}</span>
                    <span class="stt-mobile-strip__pack">{{ $pack[$subscription] ?? __('admin.common.active') }}</span>
                </div>
            </div>
        </div>

        <div class="stt-layout">

            {{-- ══ COLONNA PRINCIPALE ══ --}}
            <div class="stt-main">

                {{-- Sezione 1 — Stato operativo --}}
                <div class="stt-section">
                    <p class="stt-label">{{ __('admin.settings.operational_status') }}</p>

                    <div class="stt-op-grid">

                        {{-- Tavoli --}}
                        <div class="stt-op-card {{ $toneClass($tavoliState['tone']) }}"
                             data-stt-toggle="tavoli" data-stt-max="2"
                             role="button" tabindex="0"
                             aria-label="{{ __('admin.settings.change_table_status') }}" title="{{ __('admin.settings.click_to_change_mode') }}">
                            <div class="stt-op-card__icon">
                                <i class="bi bi-table"></i>
                            </div>
                            <div class="stt-op-card__body">
                                <p class="stt-op-card__name">{{ __('admin.common.table') }}</p>

                            </div>
                            <span class="stt-op-card__badge stt-state stt-state--{{ $tavoliState['tone'] }}"
                                  data-stt-badge="tavoli">{{ $tavoliState['label'] }}</span>
                        </div>

                        {{-- Asporto --}}
                        <div class="stt-op-card {{ $toneClass($asportoState['tone']) }}"
                             data-stt-toggle="asporto" data-stt-max="2"
                             role="button" tabindex="0"
                             aria-label="{{ __('admin.settings.change_takeaway_status') }}" title="{{ __('admin.settings.click_to_change_mode') }}">
                            <div class="stt-op-card__icon">
                                <i class="bi bi-bag-fill"></i>
                            </div>
                            <div class="stt-op-card__body">
                                <p class="stt-op-card__name">{{ __('admin.common.takeaway') }}</p>

                            </div>
                            <span class="stt-op-card__badge stt-state stt-state--{{ $asportoState['tone'] }}"
                                  data-stt-badge="asporto">{{ $asportoState['label'] }}</span>
                        </div>

                        {{-- Domicilio (solo piano > 1) --}}
                        @if ($subscription > 1)
                        <div class="stt-op-card {{ $toneClass($domicilioState['tone']) }}"
                             data-stt-toggle="domicilio" data-stt-max="1"
                             role="button" tabindex="0"
                             aria-label="{{ __('admin.settings.change_delivery_status') }}" title="{{ __('admin.settings.click_to_change_mode') }}">
                            <div class="stt-op-card__icon">
                                <i class="bi bi-bicycle"></i>
                            </div>
                            <div class="stt-op-card__body">
                                <p class="stt-op-card__name">{{ __('admin.common.delivery') }}</p>

                            </div>
                            <span class="stt-op-card__badge stt-state stt-state--{{ $domicilioState['tone'] }}"
                                  data-stt-badge="domicilio">{{ $domicilioState['label'] }}</span>
                        </div>
                        @endif

                        {{-- Ferie --}}
                        <div class="stt-op-card {{ $toneClass($ferieState['tone']) }}"
                             data-stt-toggle="ferie" data-stt-max="1"
                             role="button" tabindex="0"
                             aria-label="{{ __('admin.settings.change_holiday_status') }}" title="{{ __('admin.settings.click_to_change_mode') }}">
                            <div class="stt-op-card__icon">
                                <i class="bi bi-umbrella-fill"></i>
                            </div>
                            <div class="stt-op-card__body">
                                <p class="stt-op-card__name">{{ __('admin.common.holiday') }}</p>

                            </div>
                            <span class="stt-op-card__badge stt-state stt-state--{{ $ferieState['tone'] }}"
                                  data-stt-badge="ferie">{{ $ferieState['label'] }}</span>
                        </div>

                        {{-- Promo --}}
                        <div class="stt-op-card {{ $toneClass($promoState['tone']) }}"
                             data-stt-toggle="promo" data-stt-max="1"
                             role="button" tabindex="0"
                             aria-label="{{ __('admin.settings.change_promo_status') }}" title="{{ __('admin.settings.click_to_change_mode') }}">
                            <div class="stt-op-card__icon">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                            <div class="stt-op-card__body">
                                <p class="stt-op-card__name">{{ __('admin.common.promo') }}</p>

                            </div>
                            <span class="stt-op-card__badge stt-state stt-state--{{ $promoState['tone'] }}"
                                  data-stt-badge="promo">{{ $promoState['label'] }}</span>
                        </div>

                    </div>
                </div>

                {{-- Sezione 2 — Lingua e valuta --}}
                <div class="stt-section">
                    <p class="stt-label">{{ __('admin.settings.language_currency') }}</p>
                    <div class="stt-card">
                        <div class="stt-locale-grid">
                            <div>
                                <p class="stt-sublabel">{{ __('admin.settings.default_language') }}</p>
                                <div class="stt-lang-pills">
                                    @foreach ($languages as $l)
                                        <button type="button"
                                            class="stt-lang-pill {{ $activeLocale == $l ? 'active' : '' }}"
                                            data-stt-lang="{{ $l }}">{{ strtoupper($l) }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <p class="stt-sublabel">{{ __('admin.settings.price_currency') }}</p>
                                <select class="stt-select" data-stt-currency>
                                    @foreach ($supportedCurrencies as $currency)
                                        <option value="{{ $currency['code'] }}" @selected($activeCurrency['code'] === $currency['code'])>
                                            {{ $currency['label'] }} ({{ $currency['code'] }} - {{ $currency['symbol'] }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="stt-inline-err" id="stt-currency-err"></span>
                                <p class="stt-note mt-2">{{ __('admin.settings.saved_values_not_converted') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sezione 3 — Servizi --}}
                <div class="stt-section">
                    <p class="stt-label">{{ __('admin.settings.services') }}</p>
                    <div class="stt-services-grid">

                        {{-- Asporto --}}
                        <div class="stt-card">
                            <div class="stt-svc-head">
                                <span class="stt-svc-title"><i class="bi bi-bag-fill me-1"></i>{{ __('admin.common.takeaway') }}</span>
                                <div class="stt-svc-toggle {{ $toneClass($asportoState['tone']) }}"
                                     data-stt-toggle="asporto" data-stt-max="2">
                                    <span class="stt-state stt-state--{{ $asportoState['tone'] }}" data-stt-badge="asporto">{{ $asportoState['label'] }}</span>
                                </div>
                            </div>
                            @if ($subscription > 2)
                            <div class="stt-field">
                                <span class="stt-field-lbl">{{ __('admin.settings.accepted_payment') }}</span>
                                <div class="stt-pay-row" data-stt-pay="asporto_pay">
                                    <button type="button" class="stt-pay-btn {{ $asporto_p['pay'] == 0 ? 'active' : '' }}" data-stt-pay-val="0">
                                        <i class="bi bi-cash-coin"></i> {{ __('admin.settings.cash') }}
                                    </button>
                                    <button type="button" class="stt-pay-btn {{ $asporto_p['pay'] == 1 ? 'active' : '' }}" data-stt-pay-val="1">
                                        <i class="bi bi-cash-coin"></i><i class="bi bi-credit-card-fill"></i> {{ __('admin.settings.both') }}
                                    </button>
                                    <button type="button" class="stt-pay-btn {{ $asporto_p['pay'] == 2 ? 'active' : '' }}" data-stt-pay-val="2">
                                        <i class="bi bi-credit-card-fill"></i> {{ __('admin.settings.card') }}
                                    </button>
                                </div>
                            </div>
                            @endif
                            @if ($subscription > 1)
                            <div class="stt-field">
                                <span class="stt-field-lbl">{{ __('admin.Prezzo_minimo') }}</span>
                                <input type="number" class="stt-input"
                                       data-stt-field="min_price_a"
                                       step="{{ \App\Support\Currency::inputStep() }}"
                                       value="{{ \App\Support\Currency::formatForInput($asporto_p['min_price'] ?? 0) }}">
                                <span class="stt-inline-err"></span>
                            </div>
                            @endif
                        </div>

                        {{-- Domicilio --}}
                        @if ($subscription > 1)
                        <div class="stt-card">
                            <div class="stt-svc-head">
                                <span class="stt-svc-title"><i class="bi bi-bicycle me-1"></i>{{ __('admin.common.delivery') }}</span>
                                <div class="stt-svc-toggle {{ $toneClass($domicilioState['tone']) }}"
                                     data-stt-toggle="domicilio" data-stt-max="1">
                                    <span class="stt-state stt-state--{{ $domicilioState['tone'] }}" data-stt-badge="domicilio">{{ $domicilioState['label'] }}</span>
                                </div>
                            </div>
                            @if ($subscription > 2)
                            <div class="stt-field">
                                <span class="stt-field-lbl">{{ __('admin.settings.accepted_payment') }}</span>
                                <div class="stt-pay-row" data-stt-pay="domicilio_pay">
                                    <button type="button" class="stt-pay-btn {{ $domicilio_p['pay'] == 0 ? 'active' : '' }}" data-stt-pay-val="0">
                                        <i class="bi bi-cash-coin"></i> {{ __('admin.settings.cash') }}
                                    </button>
                                    <button type="button" class="stt-pay-btn {{ $domicilio_p['pay'] == 1 ? 'active' : '' }}" data-stt-pay-val="1">
                                        <i class="bi bi-cash-coin"></i><i class="bi bi-credit-card-fill"></i> {{ __('admin.settings.both') }}
                                    </button>
                                    <button type="button" class="stt-pay-btn {{ $domicilio_p['pay'] == 2 ? 'active' : '' }}" data-stt-pay-val="2">
                                        <i class="bi bi-credit-card-fill"></i> {{ __('admin.settings.card') }}
                                    </button>
                                </div>
                            </div>
                            @endif
                            <div class="stt-field">
                                <span class="stt-field-lbl">{{ __('admin.Prezzo_minimo') }}</span>
                                <input type="number" class="stt-input"
                                       data-stt-field="min_price_d"
                                       step="{{ \App\Support\Currency::inputStep() }}"
                                       value="{{ \App\Support\Currency::formatForInput($domicilio_p['min_price'] ?? 0) }}">
                                <span class="stt-inline-err"></span>
                            </div>
                            <div class="stt-field">
                                <span class="stt-field-lbl">{{ __('admin.Prezzo_consegna') }}</span>
                                <input type="number" class="stt-input"
                                       data-stt-field="delivery_cost"
                                       step="{{ \App\Support\Currency::inputStep() }}"
                                       value="{{ \App\Support\Currency::formatForInput($domicilio_p['delivery_cost'] ?? 0) }}">
                                <span class="stt-inline-err"></span>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                {{-- Sezioni 4 & 5 affiancate --}}
                <div class="stt-two-col stt-section">

                    {{-- Sezione 4 — Ferie --}}
                    <div class="stt-card">
                        <p class="stt-label">{{ __('admin.Ferie') }}</p>
                        <div class="stt-sw-row">
                            <label class="stt-sw">
                                <input type="checkbox" {{ $ferieStatus === 1 ? 'checked' : '' }} data-stt-sw="ferie">
                                <span class="stt-sw-track"></span>
                            </label>
                            <div>
                                <p class="stt-sw-lbl" data-stt-badge="ferie">{{ $ferieState['label'] }}</p>
                                <p class="stt-sw-sub" id="stt-ferie-sub">{{ $ferieStatus === 1 ? __('admin.settings.restaurant_closed_orders') : __('admin.settings.restaurant_operational') }}</p>
                            </div>
                        </div>
                        <div class="stt-date-row">
                            <input type="date" class="stt-input"
                                   data-stt-field="from"
                                   value="{{ $ferieProp['from'] ?? '' }}">
                            <span class="stt-date-sep">→</span>
                            <input type="date" class="stt-input"
                                   data-stt-field="to"
                                   value="{{ $ferieProp['to'] ?? '' }}">
                        </div>
                        <span class="stt-inline-err" id="stt-ferie-err"></span>
                    </div>

                    {{-- Sezione 5 — Promozione Tavoli --}}
                    <div class="stt-card">
                        <p class="stt-label"><i class="bi bi-gift-fill me-1"></i>{{ __('admin.Promozione') }}</p>
                        <div class="stt-sw-row">
                            <label class="stt-sw">
                                <input type="checkbox" {{ $promoStatus === 1 ? 'checked' : '' }} data-stt-sw="promo">
                                <span class="stt-sw-track"></span>
                            </label>
                            <div>
                                <p class="stt-sw-lbl" data-stt-badge="promo">{{ $promoState['label'] }}</p>
                                <p class="stt-sw-sub">{{ __('admin.settings.visible_to_table_customers') }}</p>
                            </div>
                        </div>
                        <div class="stt-field">
                            <span class="stt-field-lbl">{{ __('admin.Titolo') }}</span>
                            <input type="text" class="stt-input"
                                   data-stt-field="promo_table_title"
                                   value="{{ $promo_table['title'] ?? '' }}"
                                   placeholder="{{ __('admin.settings.promo_placeholder') }}">
                            <span class="stt-inline-err"></span>
                        </div>
                        <div class="stt-field">
                            <span class="stt-field-lbl">{{ __('admin.Corpo') }}</span>
                            <textarea class="stt-input" rows="3"
                                      data-stt-field="promo_table_body"
                                      placeholder="{{ __('admin.settings.promo_description_placeholder') }}">{{ $promo_table['body'] ?? '' }}</textarea>
                            <span class="stt-inline-err"></span>
                        </div>
                        <div class="stt-field">
                            <span class="stt-field-lbl">{{ __('admin.settings.promo_cta_label') }}</span>
                            <select class="stt-input" data-stt-field="promo_table_cta">
                                <option value="prenota" {{ ($promo_table['cta'] ?? 'prenota') === 'prenota' ? 'selected' : '' }}>{{ __('admin.settings.promo_cta_prenota') }}</option>
                                <option value="ordina"  {{ ($promo_table['cta'] ?? '') === 'ordina'    ? 'selected' : '' }}>{{ __('admin.settings.promo_cta_ordina') }}</option>
                                <option value="offerte" {{ ($promo_table['cta'] ?? '') === 'offerte'   ? 'selected' : '' }}>{{ __('admin.settings.promo_cta_offerte') }}</option>
                                <option value="registrati" {{ ($promo_table['cta'] ?? '') === 'registrati' ? 'selected' : '' }}>{{ __('admin.settings.promo_cta_registrati') }}</option>
                            </select>
                            <span class="stt-inline-err"></span>
                        </div>
                    </div>

                </div>

                {{-- Sezione 6 — Dettagli del locale (tab layout, submit unico) --}}
                <div class="stt-section">
                    <p class="stt-label"><i class="bi bi-building"></i>{{ __('admin.settings.venue_details') }}</p>
                    <div class="stt-card stt-card-flush">

                        {{-- Tab nav --}}
                        <div class="loc-tab-nav" id="loc-tab-nav">
                            <div class="loc-tabs" role="tablist">
                                <button type="button" class="loc-tab active" role="tab"
                                        aria-selected="true" data-loc-tab="orari">
                                    <i class="bi bi-clock"></i>{{ __('admin.set_1') }}
                                </button>
                                <button type="button" class="loc-tab" role="tab"
                                        aria-selected="false" data-loc-tab="posizione">
                                    <i class="bi bi-geo-alt"></i>{{ __('admin.set_2') }}
                                </button>
                                <button type="button" class="loc-tab" role="tab"
                                        aria-selected="false" data-loc-tab="contatti">
                                    <i class="bi bi-person-lines-fill"></i>{{ __('admin.set_3') }}
                                </button>
                                @if ($subscription > 1)
                                <button type="button" class="loc-tab" role="tab"
                                        aria-selected="false" data-loc-tab="comuni">
                                    <i class="bi bi-map"></i>{{ __('admin.set_4') }}
                                </button>
                                @endif
                                @if ($subscription > 2)
                                <button type="button" class="loc-tab" role="tab"
                                        aria-selected="false" data-loc-tab="whatsapp">
                                    <i class="bi bi-whatsapp"></i>{{ __('admin.set_5') }}
                                </button>
                                @endif
                            </div>
                        </div>

                        {{-- Form con tutti i pannelli — submit unico in fondo --}}
                        <form class="setting" action="{{ route('admin.settings.updateAll') }}"
                              method="POST" enctype="multipart/form-data" id="loc-form">
                            @csrf

                            {{-- Pannello: Giorni e orari --}}
                            <div class="loc-panel active" id="loc-panel-orari" role="tabpanel">
                                @php
                                $days = [
                                    'lunedì'=>0,'martedì'=>1,'mercoledì'=>2,
                                    'giovedì'=>3,'venerdì'=>4,'sabato'=>5,'domenica'=>6,
                                ];
                                @endphp
                                <div class="loc-days-grid">
                                    @foreach ($days as $giorno => $index)
                                    @php
                                    $labelDate = \Carbon\Carbon::now()
                                        ->startOfWeek(\Carbon\Carbon::MONDAY)
                                        ->addDays($index);
                                    $labelDate->locale($activeLocale);
                                    $label = $labelDate->isoFormat('dddd');
                                    @endphp
                                    <div class="loc-day-row">
                                        <label class="loc-day-lbl" for="{{ $giorno }}">{{ ucfirst($label) }}</label>
                                        <input id="{{ $giorno }}" type="text" class="loc-day-input"
                                               placeholder="--:-- / --:--" name="{{ $giorno }}"
                                               value="{{ $property_orari[$giorno] ?? '' }}"
                                               aria-label="{{ ucfirst($label) }}">
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Pannello: Posizione --}}
                            <div class="loc-panel" id="loc-panel-posizione" role="tabpanel">
                                @if(isset($property_posizione['foto_maps']) && $property_posizione['foto_maps'] !== '')
                                    <img class="loc-map-preview"
                                         src="{{ asset('public/storage/' . $property_posizione['foto_maps']) }}"
                                         alt="{{ __('admin.settings.map') }}">
                                @endif
                                <div class="loc-field">
                                    <label>{{ __('admin.settings.map_photo') }}</label>
                                    <input type="file" id="file-input" name="foto_maps"
                                           accept="image/*">
                                </div>
                                <div class="loc-field">
                                    <label>{{ __('admin.Link_Google_Maps') }}</label>
                                    <input type="text" class="stt-input" name="link_maps"
                                           value="{{ $property_posizione['link_maps'] ?? '' }}"
                                           placeholder="https://maps.google.com/...">
                                </div>
                                <div class="loc-field">
                                    <label>{{ __('admin.Indirizzo') }}</label>
                                    <input type="text" class="stt-input" name="indirizzo"
                                           value="{{ $property_posizione['indirizzo'] ?? '' }}"
                                           placeholder="{{ __('admin.settings.address_placeholder') }}">
                                </div>
                            </div>

                            {{-- Pannello: Contatti --}}
                            <div class="loc-panel" id="loc-panel-contatti" role="tabpanel">
                                <div class="loc-contact-grid">
                                    <div class="loc-field">
                                        <label><i class="bi bi-telephone"></i> {{ __('admin.Telefono') }}</label>
                                        <input type="text" class="stt-input" name="telefono"
                                               value="{{ $property_contatti['telefono'] ?? '' }}"
                                               placeholder="+39 000 111 0000">
                                    </div>
                                    <div class="loc-field">
                                        <label><i class="bi bi-envelope"></i> {{ __('admin.Email') }}</label>
                                        <input type="text" class="stt-input" name="email"
                                               value="{{ $property_contatti['email'] ?? '' }}"
                                               placeholder="info@ristorante.it">
                                    </div>
                                </div>
                                <p class="stt-sublabel stt-sublabel--social">{{ __('admin.settings.social') }}</p>
                                <div class="loc-contact-grid">
                                    <div class="loc-field">
                                        <label><i class="bi bi-instagram"></i> Instagram</label>
                                        <input type="text" class="stt-input"
                                               placeholder="{{ __('admin.Link_instagram') }}"
                                               name="instagram" value="{{ $property_contatti['instagram'] ?? '' }}">
                                    </div>
                                    <div class="loc-field">
                                        <label><i class="bi bi-facebook"></i> Facebook</label>
                                        <input type="text" class="stt-input"
                                               placeholder="{{ __('admin.Link_facebook') }}"
                                               name="facebook" value="{{ $property_contatti['facebook'] ?? '' }}">
                                    </div>
                                    <div class="loc-field">
                                        <label><i class="bi bi-tiktok"></i> TikTok</label>
                                        <input type="text" class="stt-input"
                                               placeholder="{{ __('admin.Link_tiktok') }}"
                                               name="tiktok" value="{{ $property_contatti['tiktok'] ?? '' }}">
                                    </div>
                                    <div class="loc-field">
                                        <label><i class="bi bi-youtube"></i> YouTube</label>
                                        <input type="text" class="stt-input"
                                               placeholder="{{ __('admin.Link_youtube') }}"
                                               name="youtube" value="{{ $property_contatti['youtube'] ?? '' }}">
                                    </div>
                                    <div class="loc-field">
                                        <label><i class="bi bi-whatsapp"></i> WhatsApp</label>
                                        <input type="text" class="stt-input"
                                               placeholder="+39001110000"
                                               name="whatsapp" value="{{ $property_contatti['whatsapp'] ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Pannello: Comuni consegna --}}
                            @if ($subscription > 1)
                            <div class="loc-panel" id="loc-panel-comuni" role="tabpanel">
                                @php
                                if (is_string($setting['Comuni per il domicilio']['property'])) {
                                    $setting['Comuni per il domicilio']['property'] = json_decode($setting['Comuni per il domicilio']['property'], true);
                                }
                                @endphp
                                <div class="loc-address-list">
                                    @foreach ($setting['Comuni per il domicilio']['property'] as $i)
                                        <span>({{ $i['provincia'] }}) {{ $i['comune'] }} — {{ $i['cap'] }}{{ $i['price'] ? ' — ' . \App\Support\Currency::formatCents($i['price']) : '' }}</span>
                                    @endforeach
                                    @if (empty($setting['Comuni per il domicilio']['property']))
                                        <span class="stt-empty-state">{{ __('admin.settings.no_city_added') }}</span>
                                    @endif
                                </div>
                                <div class="loc-actions">
                                    <button type="button" class="my_btn_1"
                                            data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                        <i class="bi bi-plus-circle me-1"></i>{{ __('admin.Aggiungi') }}
                                    </button>
                                    <button type="button" class="my_btn_1 trash"
                                            data-bs-toggle="modal" data-bs-target="#staticBackdrop1">
                                        <i class="bi bi-trash me-1"></i>{{ __('admin.Rimuovi') }}
                                    </button>
                                </div>
                            </div>
                            @endif

                            {{-- Pannello: WhatsApp numeri --}}
                            @if ($subscription > 2)
                            <div class="loc-panel" id="loc-panel-whatsapp" role="tabpanel">
                                @php
                                if (is_string($setting['wa']['property'])) {
                                    $setting['wa']['property'] = json_decode($setting['wa']['property'], true);
                                }
                                @endphp
                                <div class="loc-address-list">
                                    @foreach ($setting['wa']['property']['numbers'] as $i)
                                        <span><i class="bi bi-whatsapp me-2 stt-wa-icon"></i>{{ $i }}</span>
                                    @endforeach
                                    @if (empty($setting['wa']['property']['numbers']))
                                        <span class="stt-empty-state">{{ __('admin.settings.no_number_configured') }}</span>
                                    @endif
                                </div>
                                <div class="loc-actions">
                                    <button type="button" class="my_btn_1"
                                            data-bs-toggle="modal" data-bs-target="#staticBackdrop2">
                                        <i class="bi bi-pencil me-1"></i>{{ __('admin.Modifica') }}
                                    </button>
                                </div>
                            </div>
                            @endif

                            {{-- Submit bar unica in fondo --}}
                            <div class="loc-submit-bar" id="loc-submit-bar">
                                <button type="submit" class="my_btn_1 my_btn_2">
                                    <i class="bi bi-check2 me-1"></i>{{ __('admin.Aggiorna') }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="stt-footer">
                    <button type="button" class="my_btn_3" data-bs-toggle="modal" data-bs-target="#staticBackdropav">
                        <i class="bi bi-sliders"></i>
                        {{ __('admin.Impostazioni_a') }}
                    </button>
                </div>

            </div>{{-- /stt-main --}}

            {{-- ══ SIDEBAR ══ --}}
            <aside class="stt-sidebar">
                <div class="stt-card stt-pack-card">
                    <a href="{{ config('configurazione.domain') }}">
                        <img class="stt-logo" src="{{ config('configurazione.domain') . '/img/favicon.png' }}" alt="">
                    </a>
                    <a href="{{ config('configurazione.domain') }}">
                        <h2>{{ config('configurazione.APP_NAME') }}</h2>
                    </a>
                    <a class="stt-pack-link" href="https://future-plus.it/#pacchetti">
                        <img class="stt-pack-link__icon" src="https://future-plus.it/img/favicon.png" alt="">
                        {{ __('admin.Pacchetto') }}: {{ $pack[$subscription] ?? __('admin.common.active') }}
                    </a>
                </div>

            </aside>

        </div>{{-- /stt-layout --}}
    </div>{{-- /stt-wrap --}}


    {{-- ══ MODALI (invariate) ══ --}}

    <div class="modal fade" id="staticBackdropav" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropavLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable large_m settings-modal-dialog">
            <form action="{{ route('admin.settings.advanced')}}" method="POST" class="w-100">
                @csrf
                <x-dashboard.action-modal
                    title-id="staticBackdropavLabel"
                    class="s_advanced settings-advanced-modal"
                    title="{{ __('admin.Impostazioni_a') }}"
                    tone="mint"

                >
                    <x-slot name="titleIcon">
                        <i class="bi bi-sliders"></i>
                    </x-slot>

                    {{-- ── Blocchi configurazione avanzata ── --}}
                    <div class="ops-grid">

                        {{-- Gestione menu --}}
                        <div class="ops-card">
                            <div class="ops-card__head">
                                <span class="ops-card__title">
                                    <i class="bi bi-journal-text"></i>
                                    {{__('admin.Gestione_menu')}}
                                </span>
                                <span class="ops-badge ops-badge--{{ $menuFixState['tone'] }}">{{ $menuFixState['label'] }}</span>
                            </div>
                            <div class="ops-radio-group">
                                <label class="ops-radio-label">
                                    <input @checked(($adv['menu_fix_set'] ?? '0') == '0') type="radio" name="menu_fix_set" value="0">
                                    <span>{{__('admin.Menu_fisso')}}</span>
                                </label>
                                <label class="ops-radio-label">
                                    <input @checked(($adv['menu_fix_set'] ?? '0') == '1') type="radio" name="menu_fix_set" value="1">
                                    <span>{{__('admin.Tutti')}}</span>
                                </label>
                                <label class="ops-radio-label">
                                    <input @checked(($adv['menu_fix_set'] ?? '0') == '2') type="radio" name="menu_fix_set" value="2">
                                    <span>{{__('admin.Menu_alla_carta')}}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Servizi attivi --}}
                        <div class="ops-card">
                            <div class="ops-card__head">
                                <span class="ops-card__title">
                                    <i class="bi bi-grid-3x3-gap"></i>
                                    {{__('admin.Servizi_attivi')}}
                                </span>
                                <span class="ops-badge ops-badge--{{ $servicesState['tone'] }}">{{ $servicesState['label'] }}</span>
                            </div>
                            <div class="ops-radio-group">
                                <label class="ops-radio-label">
                                    <input class="critical-radio1" @checked(($adv['services'] ?? '4') == '3') type="radio" name="services" value="3">
                                    <span>{{__('admin.Asporto')}}</span>
                                </label>
                                <label class="ops-radio-label">
                                    <input class="critical-radio1" @checked(($adv['services'] ?? '4') == '4') type="radio" name="services" value="4">
                                    <span>{{__('admin.Tutti')}}</span>
                                </label>
                                <label class="ops-radio-label">
                                    <input class="critical-radio1" @checked(($adv['services'] ?? '4') == '2') type="radio" name="services" value="2">
                                    <span>{{__('admin.Tavoli')}}</span>
                                </label>
                            </div>
                            <input type="hidden" id="attivo-originale1" value="{{ $adv['services'] ?? 4 }}">
                        </div>

                        {{-- Tipologie asporto --}}
                        <div class="ops-card">
                            <div class="ops-card__head">
                                <span class="ops-card__title">
                                    <i class="bi bi-bag-check"></i>
                                    {{ __('admin.Tipo') }} {{ __('admin.Asporto') }}
                                </span>
                                <span class="ops-badge ops-badge--{{ $takeawayTypeState['tone'] }}">{{ $takeawayTypeState['label'] }}</span>
                            </div>
                            <div class="ops-radio-group ops-radio-group--spaced">
                                <label class="ops-radio-label">
                                    <input class="critical-radio2" @checked(($adv['too'] ?? 0) == 0) type="radio" name="too" value="0">
                                    <span>{{ __('admin.settings.single') }}</span>
                                </label>
                                <label class="ops-radio-label">
                                    <input class="critical-radio2" @checked(($adv['too'] ?? 0) == 1) type="radio" name="too" value="1">
                                    <span>{{ __('admin.settings.separate') }}</span>
                                </label>
                            </div>
                            <input type="hidden" id="attivo-originale2" value="{{ $adv['too'] ?? 0 }}">
                            <div class="ops-pair ops-pair--flush">
                                <div class="ops-field">
                                    <label>{{ __('admin.settings.type_1') }}</label>
                                    <input type="text" name="too_1" value="{{ $adv['too_1'] ?? '' }}"
                                           placeholder="{{ __('admin.settings.type_1_placeholder') }}">
                                </div>
                                <div class="ops-field">
                                    <label>{{ __('admin.settings.type_2') }}</label>
                                    <input type="text" name="too_2" value="{{ $adv['too_2'] ?? '' }}"
                                           placeholder="{{ __('admin.settings.type_2_placeholder') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Doppia sala --}}
                        <div class="ops-card ops-card--wide">
                            <div class="ops-card__head">
                                <span class="ops-card__title">
                                    <i class="bi bi-layout-split"></i>
                                    {{__('admin.Doppia_sala')}}
                                </span>
                                <span class="ops-badge ops-badge--{{ $doubleRoomState['tone'] }}">{{ $doubleRoomState['label'] }}</span>
                            </div>
                            <div class="ops-radio-group ops-radio-group--spaced">
                                <label class="ops-radio-label">
                                    <input class="critical-radio3" @checked(($adv['dt'] ?? 0) == 0) type="radio" name="dt" value="0">
                                    <span>{{ __('admin.common.disabled') }}</span>
                                </label>
                                <label class="ops-radio-label">
                                    <input class="critical-radio3" @checked(($adv['dt'] ?? 0) == 1) type="radio" name="dt" value="1">
                                    <span>{{ __('admin.common.enabled') }}</span>
                                </label>
                            </div>
                            <input type="hidden" id="attivo-originale3" value="{{ $adv['dt'] ?? 0 }}">
                            <div class="ops-pair ops-pair--flush">
                                <div class="ops-field">
                                    <label>{{__('admin.Sala_1')}}</label>
                                    <input type="text" name="sala_1" value="{{ $adv['sala_1'] ?? '' }}"
                                           placeholder="{{ __('admin.settings.room_1_placeholder') }}">
                                </div>
                                <div class="ops-field">
                                    <label>{{__('admin.Sala_2')}}</label>
                                    <input type="text" name="sala_2" value="{{ $adv['sala_2'] ?? '' }}"
                                           placeholder="{{ __('admin.settings.room_2_placeholder') }}">
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ── Info legali ── --}}
                    <div class="ops-legal">
                        <p class="ops-legal__title">
                            <i class="bi bi-file-earmark-text"></i>
                            {{__('admin.Info_legali')}}
                        </p>
                        <div class="ops-legal-grid">
                            <div class="ops-field">
                                <label>{{__('admin.Ragione_sociale')}}</label>
                                <input type="text" name="r_sociale" value="{{ $adv['r_sociale'] ?? '' }}"
                                       placeholder="{{ __('admin.settings.company_placeholder') }}">
                            </div>
                            <div class="ops-field">
                                <label>{{__('admin.Piva')}}</label>
                                <input type="text" name="p_iva" value="{{ $adv['p_iva'] ?? '' }}"
                                       placeholder="IT00000000000">
                            </div>
                            <div class="ops-field">
                                <label>{{__('admin.Codice_rea')}}</label>
                                <input type="text" name="c_rea" value="{{ $adv['c_rea'] ?? '' }}"
                                       placeholder="AN-000000">
                            </div>
                            <div class="ops-field">
                                <label>{{__('admin.Capitale_sociale')}}</label>
                                <input type="number" name="c_sociale" value="{{ $adv['c_sociale'] ?? '' }}"
                                       placeholder="10000">
                            </div>
                            <div class="ops-field">
                                <label>{{__('admin.Codice_ateco')}}</label>
                                <input type="text" name="c_ateco" value="{{$adv['c_ateco'] ?? ''}}"
                                       placeholder="56.10.11">
                            </div>
                            <div class="ops-field">
                                <label>{{__('admin.Iscrizione_imprese')}}</label>
                                <input type="text" name="u_imprese" value="{{ $adv['u_imprese'] ?? '' }}"
                                       placeholder="RI-00000">
                            </div>
                        </div>
                    </div>

                    {{-- ── Metodi di pagamento accettati ── --}}
                    <div class="ops-legal ops-legal--methods">
                        <p class="ops-methods-label">
                            <i class="bi bi-credit-card me-1"></i>
                            {{__('admin.Metodi_pagamento')}}
                        </p>
                        <div class="ops-methods-wrap">
                            <div class="ops-method-item">
                                <input class="btn-check" type="checkbox" name="method[]" id="m_1" value="1"
                                       @if (in_array(1, $adv['method'])) checked @endif>
                                <label for="m_1" title="American Express">
                                    <svg class="payment-icon" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="pi-american_express" viewBox="0 0 38 24" width="38" height="24"><title id="pi-american_express">American Express</title><path fill="#000" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3Z" opacity=".07"></path><path fill="#006FCF" d="M35 1c1.1 0 2 .9 2 2v18c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2h32Z"></path><path fill="#FFF" d="M22.012 19.936v-8.421L37 11.528v2.326l-1.732 1.852L37 17.573v2.375h-2.766l-1.47-1.622-1.46 1.628-9.292-.02Z"></path><path fill="#006FCF" d="M23.013 19.012v-6.57h5.572v1.513h-3.768v1.028h3.678v1.488h-3.678v1.01h3.768v1.531h-5.572Z"></path><path fill="#006FCF" d="m28.557 19.012 3.083-3.289-3.083-3.282h2.386l1.884 2.083 1.89-2.082H37v.051l-3.017 3.23L37 18.92v.093h-2.307l-1.917-2.103-1.898 2.104h-2.321Z"></path><path fill="#FFF" d="M22.71 4.04h3.614l1.269 2.881V4.04h4.46l.77 2.159.771-2.159H37v8.421H19l3.71-8.421Z"></path><path fill="#006FCF" d="m23.395 4.955-2.916 6.566h2l.55-1.315h2.98l.55 1.315h2.05l-2.904-6.566h-2.31Zm.25 3.777.875-2.09.873 2.09h-1.748Z"></path><path fill="#006FCF" d="M28.581 11.52V4.953l2.811.01L32.84 9l1.456-4.046H37v6.565l-1.74.016v-4.51l-1.644 4.494h-1.59L30.35 7.01v4.51h-1.768Z"></path></svg>
                                </label>
                            </div>
                            <div class="ops-method-item">
                                <input class="btn-check" type="checkbox" name="method[]" id="m_2" value="2"
                                       @if (in_array(2, $adv['method'])) checked @endif>
                                <label for="m_2" title="Apple Pay">
                                    <svg class="payment-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" role="img" x="0" y="0" width="38" height="24" viewBox="0 0 165.521 105.965" xml:space="preserve" aria-labelledby="pi-apple_pay"><title id="pi-apple_pay">Apple Pay</title><path fill="#000" d="M150.698 0H14.823c-.566 0-1.133 0-1.698.003-.477.004-.953.009-1.43.022-1.039.028-2.087.09-3.113.274a10.51 10.51 0 0 0-2.958.975 9.932 9.932 0 0 0-4.35 4.35 10.463 10.463 0 0 0-.975 2.96C.113 9.611.052 10.658.024 11.696a70.22 70.22 0 0 0-.022 1.43C0 13.69 0 14.256 0 14.823v76.318c0 .567 0 1.132.002 1.699.003.476.009.953.022 1.43.028 1.036.09 2.084.275 3.11a10.46 10.46 0 0 0 .974 2.96 9.897 9.897 0 0 0 1.83 2.52 9.874 9.874 0 0 0 2.52 1.83c.947.483 1.917.79 2.96.977 1.025.183 2.073.245 3.112.273.477.011.953.017 1.43.02.565.004 1.132.004 1.698.004h135.875c.565 0 1.132 0 1.697-.004.476-.002.952-.009 1.431-.02 1.037-.028 2.085-.09 3.113-.273a10.478 10.478 0 0 0 2.958-.977 9.955 9.955 0 0 0 4.35-4.35c.483-.947.789-1.917.974-2.96.186-1.026.246-2.074.274-3.11.013-.477.02-.954.022-1.43.004-.567.004-1.132.004-1.699V14.824c0-.567 0-1.133-.004-1.699a63.067 63.067 0 0 0-.022-1.429c-.028-1.038-.088-2.085-.274-3.112a10.4 10.4 0 0 0-.974-2.96 9.94 9.94 0 0 0-4.35-4.35A10.52 10.52 0 0 0 156.939.3c-1.028-.185-2.076-.246-3.113-.274a71.417 71.417 0 0 0-1.431-.022C151.83 0 151.263 0 150.698 0z"></path><path fill="#FFF" d="M150.698 3.532l1.672.003c.452.003.905.008 1.36.02.793.022 1.719.065 2.583.22.75.135 1.38.34 1.984.648a6.392 6.392 0 0 1 2.804 2.807c.306.6.51 1.226.645 1.983.154.854.197 1.783.218 2.58.013.45.019.9.02 1.36.005.557.005 1.113.005 1.671v76.318c0 .558 0 1.114-.004 1.682-.002.45-.008.9-.02 1.35-.022.796-.065 1.725-.221 2.589a6.855 6.855 0 0 1-.645 1.975 6.397 6.397 0 0 1-2.808 2.807c-.6.306-1.228.511-1.971.645-.881.157-1.847.2-2.574.22-.457.01-.912.017-1.379.019-.555.004-1.113.004-1.669.004H14.801c-.55 0-1.1 0-1.66-.004a74.993 74.993 0 0 1-1.35-.018c-.744-.02-1.71-.064-2.584-.22a6.938 6.938 0 0 1-1.986-.65 6.337 6.337 0 0 1-1.622-1.18 6.355 6.355 0 0 1-1.178-1.623 6.935 6.935 0 0 1-.646-1.985c-.156-.863-.2-1.788-.22-2.578a66.088 66.088 0 0 1-.02-1.355l-.003-1.327V14.474l.002-1.325a66.7 66.7 0 0 1 .02-1.357c.022-.792.065-1.717.222-2.587a6.924 6.924 0 0 1 .646-1.981c.304-.598.7-1.144 1.18-1.623a6.386 6.386 0 0 1 1.624-1.18 6.96 6.96 0 0 1 1.98-.646c.865-.155 1.792-.198 2.586-.22.452-.012.905-.017 1.354-.02l1.677-.003h135.875"></path><g><g><path fill="#000" d="M43.508 35.77c1.404-1.755 2.356-4.112 2.105-6.52-2.054.102-4.56 1.355-6.012 3.112-1.303 1.504-2.456 3.959-2.156 6.266 2.306.2 4.61-1.152 6.063-2.858"></path><path fill="#000" d="M45.587 39.079c-3.35-.2-6.196 1.9-7.795 1.9-1.6 0-4.049-1.8-6.698-1.751-3.447.05-6.645 2-8.395 5.1-3.598 6.2-.95 15.4 2.55 20.45 1.699 2.5 3.747 5.25 6.445 5.151 2.55-.1 3.549-1.65 6.647-1.65 3.097 0 3.997 1.65 6.696 1.6 2.798-.05 4.548-2.5 6.247-5 1.95-2.85 2.747-5.6 2.797-5.75-.05-.05-5.396-2.101-5.446-8.251-.05-5.15 4.198-7.6 4.398-7.751-2.399-3.548-6.147-3.948-7.447-4.048"></path></g><g><path fill="#000" d="M78.973 32.11c7.278 0 12.347 5.017 12.347 12.321 0 7.33-5.173 12.373-12.529 12.373h-8.058V69.62h-5.822V32.11h14.062zm-8.24 19.807h6.68c5.07 0 7.954-2.729 7.954-7.46 0-4.73-2.885-7.434-7.928-7.434h-6.706v14.894z"></path><path fill="#000" d="M92.764 61.847c0-4.809 3.665-7.564 10.423-7.98l7.252-.442v-2.08c0-3.04-2.001-4.704-5.562-4.704-2.938 0-5.07 1.507-5.51 3.82h-5.252c.157-4.86 4.731-8.395 10.918-8.395 6.654 0 10.995 3.483 10.995 8.89v18.663h-5.38v-4.497h-.13c-1.534 2.937-4.914 4.782-8.579 4.782-5.406 0-9.175-3.222-9.175-8.057zm17.675-2.417v-2.106l-6.472.416c-3.64.234-5.536 1.585-5.536 3.95 0 2.288 1.975 3.77 5.068 3.77 3.95 0 6.94-2.522 6.94-6.03z"></path><path fill="#000" d="M120.975 79.652v-4.496c.364.051 1.247.103 1.715.103 2.573 0 4.029-1.09 4.913-3.899l.52-1.663-9.852-27.293h6.082l6.863 22.146h.13l6.862-22.146h5.927l-10.216 28.67c-2.34 6.577-5.017 8.735-10.683 8.735-.442 0-1.872-.052-2.261-.157z"></path></g></g></svg>
                                </label>
                            </div>
                            <div class="ops-method-item">
                                <input class="btn-check" type="checkbox" name="method[]" id="m_3" value="3"
                                       @if (in_array(3, $adv['method'])) checked @endif>
                                <label for="m_3" title="Google Pay">
                                    <svg class="payment-icon" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 38 24" width="38" height="24" aria-labelledby="pi-google_pay"><title id="pi-google_pay">Google Pay</title><path d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z" fill="#000" opacity=".07"></path><path d="M35 1c1.1 0 2 .9 2 2v18c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2h32" fill="#FFF"></path><path d="M18.093 11.976v3.2h-1.018v-7.9h2.691a2.447 2.447 0 0 1 1.747.692 2.28 2.28 0 0 1 .11 3.224l-.11.116c-.47.447-1.098.69-1.747.674l-1.673-.006zm0-3.732v2.788h1.698c.377.012.741-.135 1.005-.404a1.391 1.391 0 0 0-1.005-2.354l-1.698-.03zm6.484 1.348c.65-.03 1.286.188 1.778.613.445.43.682 1.03.65 1.649v3.334h-.969v-.766h-.049a1.93 1.93 0 0 1-1.673.931 2.17 2.17 0 0 1-1.496-.533 1.667 1.667 0 0 1-.613-1.324 1.606 1.606 0 0 1 .613-1.336 2.746 2.746 0 0 1 1.698-.515c.517-.02 1.03.093 1.49.331v-.208a1.134 1.134 0 0 0-.417-.901 1.416 1.416 0 0 0-.98-.368 1.545 1.545 0 0 0-1.319.717l-.895-.564a2.488 2.488 0 0 1 2.182-1.06zM23.29 13.52a.79.79 0 0 0 .337.662c.223.176.5.269.785.263.429-.001.84-.17 1.146-.472.305-.286.478-.685.478-1.103a2.047 2.047 0 0 0-1.324-.374 1.716 1.716 0 0 0-1.03.294.883.883 0 0 0-.392.73zm9.286-3.75l-3.39 7.79h-1.048l1.281-2.728-2.224-5.062h1.103l1.612 3.885 1.569-3.885h1.097z" fill="#5F6368"></path><path d="M13.986 11.284c0-.308-.024-.616-.073-.92h-4.29v1.747h2.451a2.096 2.096 0 0 1-.9 1.373v1.134h1.464a4.433 4.433 0 0 0 1.348-3.334z" fill="#4285F4"></path><path d="M9.629 15.721a4.352 4.352 0 0 0 3.01-1.097l-1.466-1.14a2.752 2.752 0 0 1-4.094-1.44H5.577v1.17a4.53 4.53 0 0 0 4.052 2.507z" fill="#34A853"></path><path d="M7.079 12.05a2.709 2.709 0 0 1 0-1.735v-1.17H5.577a4.505 4.505 0 0 0 0 4.075l1.502-1.17z" fill="#FBBC04"></path><path d="M9.629 8.44a2.452 2.452 0 0 1 1.74.68l1.3-1.293a4.37 4.37 0 0 0-3.065-1.183 4.53 4.53 0 0 0-4.027 2.5l1.502 1.171a2.715 2.715 0 0 1 2.55-1.875z" fill="#EA4335"></path></svg>
                                </label>
                            </div>
                            <div class="ops-method-item">
                                <input class="btn-check" type="checkbox" name="method[]" id="m_4" value="4"
                                       @if (in_array(4, $adv['method'])) checked @endif>
                                <label for="m_4" title="Maestro">
                                    <svg class="payment-icon" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" width="38" height="24" role="img" aria-labelledby="pi-maestro"><title id="pi-maestro">Maestro</title><path opacity=".07" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z"></path><path fill="#fff" d="M35 1c1.1 0 2 .9 2 2v18c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2h32"></path><circle fill="#EB001B" cx="15" cy="12" r="7"></circle><circle fill="#00A2E5" cx="23" cy="12" r="7"></circle><path fill="#7375CF" d="M22 12c0-2.4-1.2-4.5-3-5.7-1.8 1.3-3 3.4-3 5.7s1.2 4.5 3 5.7c1.8-1.2 3-3.3 3-5.7z"></path></svg>
                                </label>
                            </div>
                            <div class="ops-method-item">
                                <input class="btn-check" type="checkbox" name="method[]" id="m_5" value="5"
                                       @if (in_array(5, $adv['method'])) checked @endif>
                                <label for="m_5" title="Mastercard">
                                    <svg class="payment-icon" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" role="img" width="38" height="24" aria-labelledby="pi-master"><title id="pi-master">Mastercard</title><path opacity=".07" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z"></path><path fill="#fff" d="M35 1c1.1 0 2 .9 2 2v18c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2h32"></path><circle fill="#EB001B" cx="15" cy="12" r="7"></circle><circle fill="#F79E1B" cx="23" cy="12" r="7"></circle><path fill="#FF5F00" d="M22 12c0-2.4-1.2-4.5-3-5.7-1.8 1.3-3 3.4-3 5.7s1.2 4.5 3 5.7c1.8-1.2 3-3.3 3-5.7z"></path></svg>
                                </label>
                            </div>
                            <div class="ops-method-item">
                                <input class="btn-check" type="checkbox" name="method[]" id="m_6" value="6"
                                       @if (in_array(6, $adv['method'])) checked @endif>
                                <label for="m_6" title="Visa">
                                    <svg class="payment-icon" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" role="img" width="38" height="24" aria-labelledby="pi-visa"><title id="pi-visa">Visa</title><path opacity=".07" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z"></path><path fill="#fff" d="M35 1c1.1 0 2 .9 2 2v18c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2h32"></path><path d="M28.3 10.1H28c-.4 1-.7 1.5-1 3h1.9c-.3-1.5-.3-2.2-.6-3zm2.9 5.9h-1.7c-.1 0-.1 0-.2-.1l-.2-.9-.1-.2h-2.4c-.1 0-.2 0-.2.2l-.3.9c0 .1-.1.1-.1.1h-2.1l.2-.5L27 8.7c0-.5.3-.7.8-.7h1.5c.1 0 .2 0 .2.2l1.4 6.5c.1.4.2.7.2 1.1.1.1.1.1.1.2zm-13.4-.3l.4-1.8c.1 0 .2.1.2.1.7.3 1.4.5 2.1.4.2 0 .5-.1.7-.2.5-.2.5-.7.1-1.1-.2-.2-.5-.3-.8-.5-.4-.2-.8-.4-1.1-.7-1.2-1-.8-2.4-.1-3.1.6-.4.9-.8 1.7-.8 1.2 0 2.5 0 3.1.2h.1c-.1.6-.2 1.1-.4 1.7-.5-.2-1-.4-1.5-.4-.3 0-.6 0-.9.1-.2 0-.3.1-.4.2-.2.2-.2.5 0 .7l.5.4c.4.2.8.4 1.1.6.5.3 1 .8 1.1 1.4.2.9-.1 1.7-.9 2.3-.5.4-.7.6-1.4.6-1.4 0-2.5.1-3.4-.2-.1.2-.1.2-.2.1zm-3.5.3c.1-.7.1-.7.2-1 .5-2.2 1-4.5 1.4-6.7.1-.2.1-.3.3-.3H18c-.2 1.2-.4 2.1-.7 3.2-.3 1.5-.6 3-1 4.5 0 .2-.1.2-.3.2M5 8.2c0-.1.2-.2.3-.2h3.4c.5 0 .9.3 1 .8l.9 4.4c0 .1 0 .1.1.2 0-.1.1-.1.1-.1l2.1-5.1c-.1-.1 0-.2.1-.2h2.1c0 .1 0 .1-.1.2l-3.1 7.3c-.1.2-.1.3-.2.4-.1.1-.3 0-.5 0H9.7c-.1 0-.2 0-.2-.2L7.9 9.5c-.2-.2-.5-.5-.9-.6-.6-.3-1.7-.5-1.9-.5L5 8.2z" fill="#142688"></path></svg>
                                </label>
                            </div>
                            <div class="ops-method-item">
                                <input class="btn-check" type="checkbox" name="method[]" id="m_7" value="7"
                                       @if (in_array(7, $adv['method'])) checked @endif>
                                <label for="m_7" title="{{ __('admin.settings.cash') }}">
                                    <i class="bi bi-cash-coin"></i>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="critical-warning" class="ops-warning">
                        {{__('admin.Warning_reset_disponibilita')}}
                    </div>

                    <x-slot name="footer">
                        <button type="button" class="my_btn_1 d" data-bs-dismiss="modal">{{__('admin.Annulla')}}</button>
                        <button type="submit" class="my_btn_1 add">{{__('admin.Aggiorna')}}</button>
                    </x-slot>
                </x-dashboard.action-modal>
            </form>
        </div>
    </div>

    @if ($subscription > 1)
        <div class="modal fade" id="staticBackdrop1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdrop1Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form action="{{ route('admin.settings.updateAree')}}" method="POST" class="w-100">
                    @csrf
                    <input type="hidden" name="ar" value="remove">
                    <x-dashboard.action-modal title-id="staticBackdrop1Label" class="settings-basic-modal"
                        title="{{ __('admin.Seleziona_i_comuni_che_vuoi_rimuovere') }}" eyebrow="{{ __('admin.settings.delivery_area_eyebrow') }}"
                        tone="danger" description="{{ __('admin.settings.remove_delivery_areas_description') }}">
                        @php
                        if (is_string($setting['Comuni per il domicilio']['property'])) {
                            $setting['Comuni per il domicilio']['property'] = json_decode($setting['Comuni per il domicilio']['property'], true);
                        }
                        @endphp
                        <div class="check_c">
                            @foreach ($setting['Comuni per il domicilio']['property'] as $i)
                                <input type="checkbox" class="btn-check" id="a{{ $i['comune'] }}" name="comuni[]" value="{{ $i['comune'] }}">
                                <label class="btn btn-outline-danger" for="a{{ $i['comune'] }}">{{ $i['provincia'] }} - {{ $i['comune'] }}</label>
                            @endforeach
                        </div>
                        <x-slot name="footer">
                            <button type="button" class="my_btn_1" data-bs-dismiss="modal">{{__('admin.Annulla')}}</button>
                            <button type="submit" class="my_btn_2">{{__('admin.Rimuovi_comuni')}}</button>
                        </x-slot>
                    </x-dashboard.action-modal>
                </form>
            </div>
        </div>

        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form action="{{ route('admin.settings.updateAree')}}" method="POST" class="w-100">
                    @csrf
                    <input type="hidden" name="ar" value="add">
                    <x-dashboard.action-modal title-id="staticBackdropLabel" class="settings-basic-modal"
                        title="{{__('admin.Aggiungi_comune')}}" eyebrow="{{ __('admin.settings.delivery_area_eyebrow') }}" tone="mint"
                        description="{{ __('admin.settings.add_delivery_area_description') }}">
                        <div class="dashboard-action-modal__field">
                            <label for="comune">{{ __('admin.settings.city') }}</label>
                            <input name="comune" id="comune" type="text" placeholder="{{ __('admin.settings.city_placeholder') }}">
                        </div>
                        <div class="dashboard-action-modal__field">
                            <label for="provincia">{{__('admin.Provincia')}}</label>
                            <input name="provincia" id="provincia" type="text" placeholder="{{ __('admin.settings.province_placeholder') }}">
                        </div>
                        <div class="dashboard-action-modal__field">
                            <label for="cap">{{__('admin.Cap')}}</label>
                            <input name="cap" id="cap" type="text" placeholder="{{ __('admin.settings.zip_placeholder') }}">
                        </div>
                        <div class="dashboard-action-modal__field">
                            <label for="price">{{__('admin.Costo_extra_consegna')}}</label>
                            <input name="price" id="price" type="number" step="{{ \App\Support\Currency::inputStep() }}" placeholder="{{ __('admin.settings.extra_price_placeholder', ['symbol' => $appCurrency['symbol']]) }}">
                        </div>
                        <x-slot name="footer">
                            <button type="button" class="my_btn_1 d" data-bs-dismiss="modal">{{__('admin.Annulla')}}</button>
                            <button type="submit" class="my_btn_1 add">{{__('admin.Aggiungi_comune')}}</button>
                        </x-slot>
                    </x-dashboard.action-modal>
                </form>
            </div>
        </div>
    @endif

    @if ($subscription > 2)
        <div class="modal fade" id="staticBackdrop2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdrop2Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form action="{{ route('admin.settings.numbers')}}" method="POST" class="w-100">
                    @csrf
                    <x-dashboard.action-modal title-id="staticBackdrop2Label" class="settings-basic-modal"
                        title="{{__('admin.Modifica_wa')}}" eyebrow="WhatsApp" tone="mint"
                        description="{{ __('admin.settings.update_whatsapp_numbers_description') }}">
                        <div class="dashboard-action-modal__field">
                            <label for="numbers_primary">1# {{__('admin.Numero')}}</label>
                            <input name="numbers[]" id="numbers_primary" type="text" placeholder="39000111000">
                        </div>
                        @if ($subscription == 5)
                            <div class="dashboard-action-modal__field">
                                <label for="numbers_secondary">2# {{__('admin.Numero')}}</label>
                                <input name="numbers[]" id="numbers_secondary" type="text" placeholder="39000111000">
                            </div>
                        @endif
                        <x-slot name="footer">
                            <button type="button" class="my_btn_1 d" data-bs-dismiss="modal">{{__('admin.Annulla')}}</button>
                            <button type="submit" class="my_btn_1 add">{{__('admin.Modifica')}}</button>
                        </x-slot>
                    </x-dashboard.action-modal>
                </form>
            </div>
        </div>
    @endif

</div>{{-- /dash_page --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── CSRF & fetch helper — unica rotta PATCH
    const CSRF    = '{{ csrf_token() }}';
    const API_URL = '{{ route("admin.settings.quickUpdate") }}';
    const settingsTranslations = {
        error: @json(__('admin.common.error')),
        disabled: @json(__('admin.common.disabled')),
        saveError: @json(__('admin.settings.save_error')),
        phone: @json(__('admin.settings.phone_status')),
        online: @json(__('admin.settings.online_status')),
        active: @json(__('admin.settings.active_status_short')),
        operational: @json(__('admin.settings.operational_status_short')),
        holiday: @json(__('admin.settings.on_holiday')),
        promoActive: @json(__('admin.common.active_status')),
        restaurantClosedOrders: @json(__('admin.settings.restaurant_closed_orders')),
        restaurantOperational: @json(__('admin.settings.restaurant_operational')),
    };

    async function patch(field, value, el) {
        el?.classList.add('stt-loading');
        try {
            const res = await fetch(API_URL, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ field, value }),
            });
            if (!res.ok) {
                const json = await res.json().catch(() => ({}));
                throw new Error(json.error || settingsTranslations.error + ' ' + res.status);
            }
            el?.classList.add('stt-flash-ok');
            setTimeout(() => el?.classList.remove('stt-flash-ok'), 700);
            return true;
        } catch (err) {
            el?.classList.add('stt-flash-err');
            setTimeout(() => el?.classList.remove('stt-flash-err'), 1500);
            return err.message || settingsTranslations.error;
        } finally {
            el?.classList.remove('stt-loading');
        }
    }

    // ── Stato corrente
    const state = {
        tavoli:    {{ $tavoliStatus }},
        asporto:   {{ $asportoStatus }},
        domicilio: {{ $domicilioStatus }},
        ferie:     {{ $ferieStatus }},
        promo:     {{ $promoStatus }},
    };

    const stateMap = {
        tavoli:    { 0:{label:settingsTranslations.disabled,tone:'off'}, 1:{label:settingsTranslations.phone,tone:'warning'}, 2:{label:settingsTranslations.online,tone:'active'} },
        asporto:   { 0:{label:settingsTranslations.disabled,tone:'off'}, 1:{label:settingsTranslations.phone,tone:'warning'}, 2:{label:settingsTranslations.online,tone:'active'} },
        domicilio: { 0:{label:settingsTranslations.disabled,tone:'off'}, 1:{label:settingsTranslations.active,tone:'active'} },
        ferie:     { 0:{label:settingsTranslations.operational,tone:'active'}, 1:{label:settingsTranslations.holiday,tone:'warning'} },
        promo:     { 0:{label:settingsTranslations.disabled,tone:'off'}, 1:{label:settingsTranslations.promoActive,tone:'active'} },
    };

    const toneToClass = t => ({active:'stt-pill--active-border',warning:'stt-pill--warning-border',off:'stt-pill--off-border'}[t]||'');

    function syncVisuals(field, value) {
        const info = stateMap[field]?.[value];
        if (!info) return;

        // Badge text + color (solo per elementi con stt-state)
        document.querySelectorAll(`[data-stt-badge="${field}"]`).forEach(el => {
            el.textContent = info.label;
            if (el.classList.contains('stt-state')) {
                el.className = el.className.replace(/stt-state--\w+/g, '').trim();
                el.classList.add(`stt-state--${info.tone}`);
            }
        });

        // Border class su pill e service-toggle
        document.querySelectorAll(`[data-stt-toggle="${field}"]`).forEach(el => {
            el.classList.remove('stt-pill--active-border','stt-pill--warning-border','stt-pill--off-border');
            el.classList.add(toneToClass(info.tone));
        });

        // Toggle switch
        document.querySelectorAll(`[data-stt-sw="${field}"]`).forEach(inp => {
            inp.checked = value === 1;
        });

        // Ferie sublabel
        if (field === 'ferie') {
            const sub = document.getElementById('stt-ferie-sub');
            if (sub) sub.textContent = value === 1 ? settingsTranslations.restaurantClosedOrders : settingsTranslations.restaurantOperational;
        }
    }

    // ── Toggle pills (sezione 1 + service card header)
    document.querySelectorAll('[data-stt-toggle]').forEach(el => {
        el.addEventListener('click', async function () {
            const field = this.dataset.sttToggle;
            const max   = parseInt(this.dataset.sttMax || '1');
            const cur   = state[field];
            const next  = cur >= max ? 0 : cur + 1;

            state[field] = next;
            syncVisuals(field, next);

            const result = await patch(field, next, this);
            if (result !== true) {
                state[field] = cur;
                syncVisuals(field, cur);
            }
        });
    });

    // ── Toggle switch (ferie, promo)
    document.querySelectorAll('[data-stt-sw]').forEach(inp => {
        inp.addEventListener('change', async function () {
            const field = this.dataset.sttSw;
            const value = this.checked ? 1 : 0;
            const prev  = state[field];

            state[field] = value;
            syncVisuals(field, value);

            const sw = this.closest('.stt-sw');
            const result = await patch(field, value, sw);
            if (result !== true) {
                state[field] = prev;
                syncVisuals(field, prev);
            }
        });
    });

    // ── Language pills
    document.querySelectorAll('[data-stt-lang]').forEach(btn => {
        btn.addEventListener('click', async function () {
            const prev = document.querySelector('[data-stt-lang].active');
            document.querySelectorAll('[data-stt-lang]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const result = await patch('lang', this.dataset.sttLang, this);
            if (result !== true) {
                document.querySelectorAll('[data-stt-lang]').forEach(b => b.classList.remove('active'));
                prev?.classList.add('active');
            }
        });
    });

    // ── Currency
    const currSel = document.querySelector('[data-stt-currency]');
    const currErr = document.getElementById('stt-currency-err');
    if (currSel) {
        let prevCurr = currSel.value;
        currSel.addEventListener('change', async function () {
            if (currErr) currErr.style.display = 'none';
            const result = await patch('currency', this.value, this);
            if (result !== true) {
                if (currErr) { currErr.textContent = typeof result === 'string' ? result : settingsTranslations.error; currErr.style.display = 'block'; }
                this.value = prevCurr;
            } else {
                prevCurr = this.value;
            }
        });
    }

    // ── Blur-save inputs & textareas
    document.querySelectorAll('[data-stt-field]').forEach(inp => {
        const errEl = () => {
            const n = inp.nextElementSibling;
            return n?.classList.contains('stt-inline-err') ? n : null;
        };

        const save = async () => {
            const e = errEl();
            if (e) e.style.display = 'none';
            const result = await patch(inp.dataset.sttField, inp.value, inp);
            if (result !== true && e) {
                e.textContent = typeof result === 'string' ? result : settingsTranslations.saveError;
                e.style.display = 'block';
            }
        };

        inp.addEventListener('blur', save);
        if (inp.type === 'date' || inp.tagName === 'SELECT') inp.addEventListener('change', save);
    });

    // ── Payment method buttons
    document.querySelectorAll('[data-stt-pay]').forEach(group => {
        group.querySelectorAll('[data-stt-pay-val]').forEach(btn => {
            btn.addEventListener('click', async function () {
                const field = group.dataset.sttPay;
                const value = parseInt(this.dataset.sttPayVal);
                const prev  = group.querySelector('.active');

                group.querySelectorAll('[data-stt-pay-val]').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const result = await patch(field, value, this);
                if (result !== true) {
                    group.querySelectorAll('[data-stt-pay-val]').forEach(b => b.classList.remove('active'));
                    prev?.classList.add('active');
                }
            });
        });
    });

    // ── Tab switcher per "Dettagli del locale"
    const locTabs   = document.querySelectorAll('[data-loc-tab]');
    const locPanels = document.querySelectorAll('.loc-panel');
    const locSubmitBar = document.getElementById('loc-submit-bar');

    // Pannelli che NON hanno il submit (solo commons/whatsapp che usano modali)
    const noSubmitPanels = ['comuni', 'whatsapp'];

    locTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const target = this.dataset.locTab;

            locTabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            locPanels.forEach(p => p.classList.remove('active'));
            const panel = document.getElementById('loc-panel-' + target);
            if (panel) panel.classList.add('active');

            // Mostra/nascondi submit bar
            if (locSubmitBar) {
                locSubmitBar.style.display = noSubmitPanels.includes(target) ? 'none' : 'flex';
            }
        });
    });

});
</script>

@endsection
