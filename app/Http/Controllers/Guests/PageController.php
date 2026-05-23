<?php

namespace App\Http\Controllers\Guests;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function home()
    {
        return view('guests.home', [
            'docPages' => $this->translatedDocumentationList(),
        ]);
    }

    public function documentation()
    {
        return view('guests.documentation', [
            'onboarding' => $this->translatedOnboardingGuide(),
            'docPages' => $this->translatedDocumentationList(),
            'quickActions' => $this->translatedQuickActions(),
        ]);
    }

    public function documentationTopic(string $page)
    {
        $pages = $this->translatedDocumentationPages();

        abort_unless(isset($pages[$page]), 404);

        $topic = $pages[$page];
        $topic['related_pages'] = $this->relatedPages($topic['related'] ?? []);

        return view('guests.docs.show', [
            'page' => $topic,
            'docPages' => $this->translatedDocumentationList(),
        ]);
    }

    public function updates()
    {
        return view('guests.updates', [
            'updates' => $this->translatedReleaseNotes(),
        ]);
    }

    private function relatedPages(array $slugs): array
    {
        $pages = $this->translatedDocumentationPages();

        return array_values(array_filter(array_map(
            static fn (string $slug) => $pages[$slug] ?? null,
            $slugs
        )));
    }

    private function translatedDocumentationList(): array
    {
        return array_values($this->translatedDocumentationPages());
    }

    private function translatedQuickActions(): array
    {
        return [
            [
                'icon' => 'calendar-check',
                'title' => __('admin.public.quick_actions.reservation.title'),
                'description' => __('admin.public.quick_actions.reservation.description'),
                'page' => 'prenotazioni',
                'cta' => __('admin.public.quick_actions.reservation.cta'),
            ],
            [
                'icon' => 'bag-check',
                'title' => __('admin.public.quick_actions.order.title'),
                'description' => __('admin.public.quick_actions.order.description'),
                'page' => 'ordini',
                'cta' => __('admin.public.quick_actions.order.cta'),
            ],
            [
                'icon' => 'journal-richtext',
                'title' => __('admin.public.quick_actions.menu_products.title'),
                'description' => __('admin.public.quick_actions.menu_products.description'),
                'page' => 'menu-prodotti',
                'cta' => __('admin.public.quick_actions.menu_products.cta'),
            ],
            [
                'icon' => 'envelope-paper',
                'title' => __('admin.public.quick_actions.communications.title'),
                'description' => __('admin.public.quick_actions.communications.description'),
                'page' => 'comunicazioni',
                'cta' => __('admin.public.quick_actions.communications.cta'),
            ],
        ];
    }

    private function translatedDocumentationPages(): array
    {
        $pages = [
            'onboarding' => [
                'translation' => 'onboarding',
                'icon' => 'rocket-takeoff',
                'related' => ['configurazione', 'prenotazioni'],
                'focus_icons' => [
                    'venue_data' => 'shop',
                    'availability' => 'calendar3',
                    'access' => 'shield-check',
                ],
                'flow_icons' => [
                    'base_data' => '1-circle',
                    'calendar' => '2-circle',
                    'menu' => '3-circle',
                    'operations' => '4-circle',
                ],
                'notification' => ['tone' => 'info', 'icon' => 'person-workspace'],
            ],
            'configurazione' => [
                'translation' => 'configuration',
                'icon' => 'sliders',
                'related' => ['onboarding', 'comunicazioni'],
                'focus_icons' => [
                    'venue_identity' => 'shop-window',
                    'services_payments' => 'credit-card-2-back',
                    'service_rules' => 'calendar-range',
                ],
                'flow_icons' => [
                    'open_section' => '1-circle',
                    'update_needed' => '2-circle',
                    'check_impact' => '3-circle',
                    'notify_team' => '4-circle',
                ],
                'notification' => ['tone' => 'warning', 'icon' => 'exclamation-triangle'],
            ],
            'menu-prodotti' => [
                'translation' => 'menu_products',
                'icon' => 'journal-richtext',
                'related' => ['ordini', 'comunicazioni'],
                'focus_icons' => [
                    'categories' => 'grid-1x2',
                    'product_sheet' => 'fork-knife',
                    'formulas' => 'card-checklist',
                ],
                'flow_icons' => [
                    'visibility' => '1-circle',
                    'fill_sheet' => '2-circle',
                    'ingredients' => '3-circle',
                    'final_check' => '4-circle',
                ],
                'notification' => ['tone' => 'danger', 'icon' => 'slash-circle'],
            ],
            'prenotazioni' => [
                'translation' => 'reservations',
                'icon' => 'calendar-check',
                'related' => ['ordini', 'configurazione'],
                'focus_icons' => [
                    'shared_list' => 'list-task',
                    'calendar_slots' => 'calendar-week',
                    'detail_notifications' => 'window-stack',
                ],
                'flow_icons' => [
                    'api_request' => '1-circle',
                    'dashboard_update' => '2-circle',
                    'notifications' => '3-circle',
                    'confirm_or_cancel' => '4-circle',
                ],
                'notification' => ['tone' => 'warning', 'icon' => 'bell'],
            ],
            'ordini' => [
                'translation' => 'orders',
                'icon' => 'bag-check',
                'related' => ['prenotazioni', 'menu-prodotti'],
                'focus_icons' => [
                    'open_from_list' => 'list-task',
                    'decide_from_detail' => 'cash-coin',
                    'move_time' => 'clock-history',
                ],
                'flow_icons' => [
                    'open_request' => '1-circle',
                    'read_detail' => '2-circle',
                    'confirm_cancel' => '3-circle',
                    'postpone' => '4-circle',
                ],
                'notification' => ['tone' => 'warning', 'icon' => 'bag-check'],
            ],
            'comunicazioni' => [
                'translation' => 'communications',
                'icon' => 'envelope-paper',
                'related' => ['menu-prodotti', 'configurazione'],
                'focus_icons' => [
                    'templates' => 'file-earmark-text',
                    'lists' => 'people',
                    'final_check' => 'send-check',
                ],
                'flow_icons' => [
                    'model' => '1-circle',
                    'recipients' => '2-circle',
                    'content' => '3-circle',
                    'send' => '4-circle',
                ],
                'notification' => ['tone' => 'success', 'icon' => 'check2-circle'],
            ],
        ];

        return collect($pages)
            ->mapWithKeys(fn (array $meta, string $slug) => [$slug => $this->translatedDocumentationPage($slug, $meta)])
            ->all();
    }

    private function translatedDocumentationPage(string $slug, array $meta): array
    {
        $page = trans('admin.public.docs.' . $meta['translation']);
        $page = is_array($page) ? $page : [];

        $page['slug'] = $slug;
        $page['icon'] = $meta['icon'];
        $page['related'] = $meta['related'];
        $page['focus_cards'] = $this->translatedCards($page['focus_cards'] ?? [], $meta['focus_icons']);
        $page['flow_steps'] = $this->translatedCards($page['flow_steps'] ?? [], $meta['flow_icons']);
        $page['notification'] = array_merge(
            $meta['notification'],
            $page['notification'] ?? []
        );

        return $page;
    }

    private function translatedOnboardingGuide(): array
    {
        $guide = trans('admin.public.onboarding_guide');
        $guide = is_array($guide) ? $guide : [];
        $guide['steps'] = $this->translatedCards($guide['steps'] ?? [], [
            'onboarding' => '1-circle',
            'right_page' => '2-circle',
            'visual_flow' => '3-circle',
            'checklist' => '4-circle',
        ]);

        return $guide;
    }

    private function translatedReleaseNotes(): array
    {
        $notes = trans('admin.public.release_notes');

        return is_array($notes) ? array_values($notes) : [];
    }

    private function translatedCards(array $cards, array $icons): array
    {
        return collect($cards)
            ->map(fn (array $card, $key) => array_merge(['icon' => $icons[$key] ?? 'circle'], $card))
            ->values()
            ->all();
    }

}
