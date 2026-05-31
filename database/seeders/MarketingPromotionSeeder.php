<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MarketingPromotionSeeder extends Seeder
{
    // Prodotto di riferimento usato in tutte le promozioni con target prodotto
    private const PRODUCT_NAME  = 'La Tricolore';
    private const CATEGORY_NAME = 'Tapas';

    public function run(): void
    {
        $product  = $this->findProduct();
        $category = $this->findCategory();

        foreach ($this->promotions($product, $category) as $data) {
            $targets = $data['targets'] ?? [];
            unset($data['targets']);

            $existing  = Promotion::where('slug', $data['slug'])->first();
            $promotion = Promotion::updateOrCreate(
                ['slug' => $data['slug']],
                $this->buildAttributes($data, $existing)
            );

            foreach ($targets as $target) {
                $this->upsertTarget($promotion, $target);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Lookup helpers
    // -------------------------------------------------------------------------

    private function findProduct(): ?Product
    {
        return ProductTranslation::where('lang', 'it')
            ->where('name', self::PRODUCT_NAME)
            ->first()
            ?->product;
    }

    private function findCategory(): ?Category
    {
        return CategoryTranslation::where('lang', 'it')
            ->where('name', self::CATEGORY_NAME)
            ->first()
            ?->category;
    }

    // -------------------------------------------------------------------------
    // Attribute builder
    // -------------------------------------------------------------------------

    private function buildAttributes(array $data, ?Promotion $existing): array
    {
        $attrs = [
            'name'            => $data['name'],
            'description'     => $data['description'] ?? null,
            'status'          => $existing?->status ?: 'active',
            'case_use'        => $data['case_use'],
            'type_discount'   => $data['type_discount'],
            'discount'        => $data['discount'],
            'minimum_pretest' => $data['minimum_pretest'],
            'cta'             => $data['cta'],
            'permanent'       => $data['permanent'],
            'valid_weekdays'  => $data['valid_weekdays'] ?? null,
            'valid_from_time' => $data['valid_from_time'] ?? null,
            'valid_to_time'   => $data['valid_to_time'] ?? null,
            // Preserva le date esistenti; se non esiste ancora usa quelle del seed
            'schedule_at'     => $existing?->schedule_at ?? $data['schedule_at'] ?? null,
            'expiring_at'     => $existing?->expiring_at ?? $data['expiring_at'] ?? null,
            'metadata'        => array_replace(
                is_array($existing?->metadata) ? $existing->metadata : [],
                $data['metadata']
            ),
        ];

        if (! $existing || $existing->total_activation === null) {
            $attrs['total_activation'] = 0;
        }
        if (! $existing || $existing->total_sent === null) {
            $attrs['total_sent'] = 0;
        }
        if (! $existing || $existing->total_used === null) {
            $attrs['total_used'] = 0;
        }

        return $attrs;
    }

    // -------------------------------------------------------------------------
    // Target upsert
    // -------------------------------------------------------------------------

    private function upsertTarget(Promotion $promotion, array $target): void
    {
        $targetId = $target['target_id'] ?? null;

        if ($targetId !== null) {
            PromotionTarget::firstOrCreate(
                [
                    'promotion_id' => $promotion->id,
                    'target_type'  => $target['target_type'],
                    'target_id'    => $targetId,
                ],
                [
                    'discount'      => $target['discount'] ?? null,
                    'type_discount' => $target['type_discount'] ?? null,
                    'metadata'      => $target['metadata'] ?? null,
                ]
            );
        } else {
            // Per i target generici (target_id NULL) evitiamo duplicati controllando manualmente
            $exists = PromotionTarget::where('promotion_id', $promotion->id)
                ->where('target_type', $target['target_type'])
                ->whereNull('target_id')
                ->exists();

            if (! $exists) {
                PromotionTarget::create([
                    'promotion_id'  => $promotion->id,
                    'target_type'   => $target['target_type'],
                    'target_id'     => null,
                    'discount'      => $target['discount'] ?? null,
                    'type_discount' => $target['type_discount'] ?? null,
                    'metadata'      => $target['metadata'] ?? null,
                ]);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Definizioni promozioni — tutti i tipi possibili
    // -------------------------------------------------------------------------

    private function promotions(?Product $product, ?Category $category): array
    {
        $productId  = $product?->id;
        $categoryId = $category?->id;

        $now = Carbon::now()->startOfDay();

        return [

            // ─── 1. ASPORTO · percentuale sul totale carrello ────────────────
            // Non permanente: valida 90 giorni dalla data del seed
            [
                'name'            => 'PROMO ASPORTO -10%',
                'slug'            => 'promo-asporto-10pct',
                'description'     => '10% di sconto sul totale carrello asporto. Minima spesa 15€.',
                'case_use'        => 'take_away',
                'type_discount'   => 'percentage',
                'discount'        => 10.00,
                'minimum_pretest' => 15.00,
                'cta'             => 'Ordina ora',
                'permanent'       => false,
                'schedule_at'     => $now->copy(),
                'expiring_at'     => $now->copy()->addDays(90),
                'metadata'        => [
                    'reusable'     => false,
                    'minimum_type' => 'cart_total',
                    'seed_key'     => 'PROMO_ASPORTO_10PCT',
                ],
            ],

            // ─── 2. ASPORTO · importo fisso sul totale carrello ──────────────
            // Non permanente: valida 60 giorni dalla data del seed
            [
                'name'            => 'PROMO ASPORTO -5€',
                'slug'            => 'promo-asporto-5e',
                'description'     => '5€ di sconto fisso sul carrello asporto. Minima spesa 20€.',
                'case_use'        => 'take_away',
                'type_discount'   => 'fixed',
                'discount'        => 5.00,
                'minimum_pretest' => 20.00,
                'cta'             => 'Ordina ora',
                'permanent'       => false,
                'schedule_at'     => $now->copy(),
                'expiring_at'     => $now->copy()->addDays(60),
                'metadata'        => [
                    'reusable'     => false,
                    'minimum_type' => 'cart_total',
                    'seed_key'     => 'PROMO_ASPORTO_5E',
                ],
            ],

            // ─── 3. PRODOTTO SPECIFICO · percentuale (target: prodotto) ──────
            // Non permanente: valida 30 giorni dalla data del seed
            [
                'name'            => 'PROMO -20% LA TRICOLORE',
                'slug'            => 'promo-tricolore-20pct',
                'description'     => '20% di sconto su "La Tricolore" in asporto.',
                'case_use'        => 'take_away',
                'type_discount'   => 'percentage',
                'discount'        => 20.00,
                'minimum_pretest' => null,
                'cta'             => 'Ordina ora',
                'permanent'       => false,
                'schedule_at'     => $now->copy(),
                'expiring_at'     => $now->copy()->addDays(30),
                'metadata'        => [
                    'reusable'           => true,
                    'minimum_type'       => null,
                    'product_link_required' => true,
                    'seed_key'           => 'PROMO_TRICOLORE_20PCT',
                ],
                'targets' => $productId ? [
                    [
                        'target_type'   => PromotionTarget::TYPE_PRODUCT,
                        'target_id'     => $productId,
                        'discount'      => 20.00,
                        'type_discount' => 'percentage',
                        'metadata'      => ['product_name' => self::PRODUCT_NAME],
                    ],
                ] : [],
            ],

            // ─── 4. OMAGGIO PRODOTTO SPECIFICO · gift (target: prodotto) ─────
            // Permanente: "La Tricolore" in regalo con spesa minima 25€
            [
                'name'            => 'PROMO REGALO LA TRICOLORE',
                'slug'            => 'promo-gift-tricolore',
                'description'     => '"La Tricolore" in omaggio con una spesa minima di 25€.',
                'case_use'        => 'take_away',
                'type_discount'   => 'gift',
                'discount'        => null,
                'minimum_pretest' => 25.00,
                'cta'             => 'Ordina ora',
                'permanent'       => true,
                'metadata'        => [
                    'reusable'              => false,
                    'minimum_type'          => 'cart_total',
                    'gift_type'             => 'product',
                    'product_link_required' => true,
                    'seed_key'              => 'PROMO_GIFT_TRICOLORE',
                ],
                'targets' => $productId ? [
                    [
                        'target_type'   => PromotionTarget::TYPE_PRODUCT,
                        'target_id'     => $productId,
                        'discount'      => null,
                        'type_discount' => 'gift',
                        'metadata'      => ['product_name' => self::PRODUCT_NAME],
                    ],
                ] : [],
            ],

            // ─── 5. CATEGORIA · percentuale su categoria (target: categoria) ─
            // Non permanente: valida 45 giorni dalla data del seed
            [
                'name'            => 'PROMO -15% TAPAS',
                'slug'            => 'promo-tapas-15pct',
                'description'     => '15% di sconto su tutti i prodotti della categoria Tapas.',
                'case_use'        => 'take_away',
                'type_discount'   => 'percentage',
                'discount'        => 15.00,
                'minimum_pretest' => null,
                'cta'             => 'Ordina ora',
                'permanent'       => false,
                'schedule_at'     => $now->copy(),
                'expiring_at'     => $now->copy()->addDays(45),
                'metadata'        => [
                    'reusable'     => true,
                    'minimum_type' => null,
                    'seed_key'     => 'PROMO_TAPAS_15PCT',
                ],
                'targets' => $categoryId ? [
                    [
                        'target_type'   => PromotionTarget::TYPE_CATEGORY,
                        'target_id'     => $categoryId,
                        'discount'      => 15.00,
                        'type_discount' => 'percentage',
                        'metadata'      => ['category_name' => self::CATEGORY_NAME],
                    ],
                ] : [],
            ],

            // ─── 6. TAVOLO · percentuale sul prezzo prenotazione ─────────────
            // Non permanente: valida 90 giorni dalla data del seed
            [
                'name'            => 'PROMO TAVOLO -15%',
                'slug'            => 'promo-tavolo-15pct',
                'description'     => '15% di sconto su prenotazione tavolo. Minimo 2 persone.',
                'case_use'        => 'table',
                'type_discount'   => 'percentage',
                'discount'        => 15.00,
                'minimum_pretest' => 2.00,
                'cta'             => 'Prenota ora',
                'permanent'       => false,
                'schedule_at'     => $now->copy(),
                'expiring_at'     => $now->copy()->addDays(90),
                'metadata'        => [
                    'reusable'     => false,
                    'minimum_type' => 'people',
                    'seed_key'     => 'PROMO_TAVOLO_15PCT',
                ],
            ],

            // ─── 7. TAVOLO · importo fisso sul prezzo prenotazione ───────────
            // Non permanente: valida 60 giorni dalla data del seed
            [
                'name'            => 'PROMO TAVOLO -10€',
                'slug'            => 'promo-tavolo-10e',
                'description'     => '10€ di sconto su prenotazione tavolo. Minimo 4 persone.',
                'case_use'        => 'table',
                'type_discount'   => 'fixed',
                'discount'        => 10.00,
                'minimum_pretest' => 4.00,
                'cta'             => 'Prenota ora',
                'permanent'       => false,
                'schedule_at'     => $now->copy(),
                'expiring_at'     => $now->copy()->addDays(60),
                'metadata'        => [
                    'reusable'     => false,
                    'minimum_type' => 'people',
                    'seed_key'     => 'PROMO_TAVOLO_10E',
                ],
            ],

            // ─── 8. WEEKEND · percentuale valida solo sabato e domenica ──────
            // Permanente: attiva ogni weekend (0 = domenica, 6 = sabato in date('w'))
            [
                'name'            => 'PROMO WEEKEND -10%',
                'slug'            => 'promo-weekend-10pct',
                'description'     => '10% di sconto asporto, valido solo sabato e domenica. Minima spesa 10€.',
                'case_use'        => 'take_away',
                'type_discount'   => 'percentage',
                'discount'        => 10.00,
                'minimum_pretest' => 10.00,
                'cta'             => 'Ordina ora',
                'permanent'       => true,
                'valid_weekdays'  => [0, 6],
                'metadata'        => [
                    'reusable'     => true,
                    'minimum_type' => 'cart_total',
                    'seed_key'     => 'PROMO_WEEKEND_10PCT',
                ],
            ],

            // ─── 9. HAPPY HOUR · percentuale in fascia oraria ────────────────
            // Permanente: attiva ogni giorno dalle 18 alle 20
            [
                'name'            => 'PROMO HAPPY HOUR TAVOLO',
                'slug'            => 'promo-happy-hour-tavolo',
                'description'     => '15% di sconto su prenotazione tavolo dalle 18:00 alle 20:00.',
                'case_use'        => 'table',
                'type_discount'   => 'percentage',
                'discount'        => 15.00,
                'minimum_pretest' => 1.00,
                'cta'             => 'Prenota ora',
                'permanent'       => true,
                'valid_from_time' => '18:00:00',
                'valid_to_time'   => '20:00:00',
                'metadata'        => [
                    'reusable'     => true,
                    'minimum_type' => 'people',
                    'seed_key'     => 'PROMO_HAPPY_HOUR_TAVOLO',
                ],
            ],

            // ─── 10. COMPLEANNO · sconto tavolo per festeggiare ──────────────
            // Permanente: 20% sulla prenotazione tavolo nel giorno/mese del compleanno
            [
                'name'            => 'PROMO COMPLEANNO TAVOLO -20%',
                'slug'            => 'promo-compleanno-tavolo-20pct',
                'description'     => '20% di sconto su prenotazione tavolo per festeggiare il compleanno. Minimo 2 persone.',
                'case_use'        => 'table',
                'type_discount'   => 'percentage',
                'discount'        => 20.00,
                'minimum_pretest' => 2.00,
                'cta'             => 'Prenota ora',
                'permanent'       => true,
                'metadata'        => [
                    'reusable'     => false,
                    'minimum_type' => 'people',
                    'seed_key'     => 'PROMO_COMPLEANNO_TAVOLO_20PCT',
                ],
            ],
        ];
    }
}
