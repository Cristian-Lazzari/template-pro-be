@php
    $navItems = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'route' => route('admin.dashboard'),
            'icon' => 'calendar2-check-fill',
            'active' => request()->routeIs('admin.dashboard'),
        ],
        // [
        //     'key' => 'requests',
        //     'label' => 'Richieste',
        //     'route' => route('admin.reservations.index'),
        //     'icon' => 'inboxes-fill',
        //     'active' => request()->routeIs('admin.reservations.*') || request()->routeIs('admin.orders.*') || request()->routeIs('admin.list'),
        // ],
        [
            'key' => 'menu',
            'label' => 'Menu',
            'route' => route('admin.menu'),
            'icon' => 'fork-knife',
            'active' => request()->routeIs('admin.menu') || request()->routeIs('admin.products.*') || request()->routeIs('admin.menus.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.ingredients.*') || request()->routeIs('admin.allergens.*'),
        ],
        [
            'key' => 'posts',
            'label' => 'Contenuti',
            'route' => route('admin.posts.index'),
            'icon' => 'images',
            'active' => request()->routeIs('admin.posts.*'),
        ],
    ];

    if (config('configurazione.subscription') > 1) {
        $navItems[] = [
            'key' => 'statistics',
            'label' => 'Statistiche',
            'route' => route('admin.statistics'),
            'icon' => 'graph-up-arrow',
            'active' => request()->routeIs('admin.statistics'),
        ];
    }

    if (config('configurazione.subscription') > 2) {
        $navItems[] = [
            'key' => 'mailer',
            'label' => 'Mailer',
            'route' => route('admin.mailer.index'),
            'icon' => 'envelope-arrow-up',
            'active' => request()->routeIs('admin.mailer.*') || request()->routeIs('admin.models.delete'),
        ];
    }

    $navItems[] = [
        'key' => 'customers',
        'label' => 'Clienti',
        'route' => route('admin.customers.index'),
        'icon' => 'people-fill',
        'active' => request()->routeIs('admin.customers.*'),
    ];

    $navItems[] = [
        'key' => 'settings',
        'label' => 'Impostazioni',
        'route' => route('admin.settings'),
        'icon' => 'gear-wide-connected',
        'active' => request()->routeIs('admin.settings.*') || request()->routeIs('admin.profile.*'),
    ];
@endphp

<nav class="admin-nav" aria-label="Navigazione dashboard">
    <div class="admin-nav__shell">
        <div class="admin-nav__group">
            @foreach ($navItems as $item)
                <a href="{{ $item['route'] }}" class="admin-nav__link admin-nav__link--{{ $item['key'] }} {{ $item['active'] ? 'is-active' : '' }}" aria-current="{{ $item['active'] ? 'page' : 'false' }}">
                    <span class="admin-nav__icon">
                        @if ($item['key'] === 'menu')
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16" aria-hidden="true">
                                <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
                            </svg>
                        @else
                            <x-icon :name="$item['icon']" />
                        @endif
                    </span>
                    <span class="admin-nav__label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</nav>
