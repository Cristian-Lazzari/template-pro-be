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

    $navItems[] = [
        'key' => 'customers',
        'label' => 'Clienti',
        'route' => route('admin.customers.index'),
        'icon' => 'people-fill',
        'active' => request()->routeIs('admin.customers.*'),
    ];

    $navItems[] = [
        'key' => 'marketing',
        'label' => 'Marketing',
        'route' => route('admin.campaigns.index'),
        'icon' => 'megaphone-fill',
        'active' => request()->routeIs('admin.promotions.*') || request()->routeIs('admin.campaigns.*') || request()->routeIs('admin.automations.*'),
    ];

    $navItems[] = [
        'key' => 'settings',
        'label' => 'Impostazioni',
        'route' => route('admin.settings'),
        'icon' => 'gear-wide-connected',
        'active' => request()->routeIs('admin.settings') || request()->routeIs('admin.profile.*'),
    ];
@endphp

<nav class="admin-nav" aria-label="Navigazione dashboard">
    <div class="admin-nav__shell">
        <div class="admin-nav__group">
            @foreach ($navItems as $item)
                <a href="{{ $item['route'] }}" class="admin-nav__link admin-nav__link--{{ $item['key'] }} {{ $item['active'] ? 'is-active' : '' }}" aria-current="{{ $item['active'] ? 'page' : 'false' }}">
                    <span class="admin-nav__icon">
                        @if ($item['key'] === 'menu')
                            <i class="bi bi-fork-knife" aria-hidden="true"></i>
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
