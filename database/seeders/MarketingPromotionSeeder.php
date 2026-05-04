<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class MarketingPromotionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->promotions() as $promotionData) {
            $existing = Promotion::where('slug', $promotionData['slug'])->first();

            Promotion::updateOrCreate(
                ['slug' => $promotionData['slug']],
                $this->attributesFor($promotionData, $existing)
            );
        }
    }

    private function attributesFor(array $promotionData, ?Promotion $existing): array
    {
        $attributes = [
            'name' => $promotionData['name'],
            'status' => $existing?->status ?: 'draft',
            'case_use' => $promotionData['case_use'],
            'discount' => $promotionData['discount'],
            'type_discount' => $promotionData['type_discount'],
            'minimum_pretest' => $promotionData['minimum_pretest'],
            'cta' => $promotionData['cta'],
            'permanent' => $promotionData['permanent'],
            'schedule_at' => null,
            'expiring_at' => null,
            'metadata' => $this->mergeMetadata($existing?->metadata, $promotionData['metadata']),
        ];

        if (! $existing || $existing->total_activation === null) {
            $attributes['total_activation'] = 0;
        }

        if (! $existing || $existing->total_sent === null) {
            $attributes['total_sent'] = 0;
        }

        if (! $existing || $existing->total_used === null) {
            $attributes['total_used'] = 0;
        }

        return $attributes;
    }

    private function mergeMetadata(mixed $existingMetadata, array $seedMetadata): array
    {
        $existingMetadata = is_array($existingMetadata) ? $existingMetadata : [];

        return array_replace($existingMetadata, $seedMetadata);
    }

    private function promotions(): array
    {
        return [
            [
                'name' => 'PROMOTION 10T',
                'slug' => 'promotion-10t',
                'case_use' => 'table',
                'type_discount' => 'percentage',
                'discount' => 10,
                'minimum_pretest' => 3,
                'cta' => '/prenota',
                'permanent' => false,
                'metadata' => [
                    'reusable' => false,
                    'description' => '10% di sconto su prenotazione tavolo. Minimo 3 persone.',
                    'minimum_type' => 'people',
                    'seed_key' => 'PROMOTION_10T',
                ],
            ],
            [
                'name' => 'PROMOTION E5ASPO',
                'slug' => 'promotion-e5aspo',
                'case_use' => 'take_away',
                'type_discount' => 'fixed',
                'discount' => 5,
                'minimum_pretest' => 10,
                'cta' => '/asporto',
                'permanent' => false,
                'metadata' => [
                    'reusable' => true,
                    'description' => '5€ di sconto su asporto. Minima spesa 10€.',
                    'minimum_type' => 'cart_total',
                    'seed_key' => 'PROMOTION_E5ASPO',
                ],
            ],
            [
                'name' => 'PROMOTION 5MARG',
                'slug' => 'promotion-5marg',
                'case_use' => 'take_away',
                'type_discount' => 'percentage',
                'discount' => 5,
                'minimum_pretest' => 10,
                'cta' => '/asporto',
                'permanent' => true,
                'metadata' => [
                    'reusable' => true,
                    'description' => '5% di sconto su margherita da asporto. Minima spesa 10€.',
                    'minimum_type' => 'cart_total',
                    'product_link_required' => true,
                    'seed_key' => 'PROMOTION_5MARG',
                ],
            ],
            [
                'name' => 'PROMOTION 5ASPO',
                'slug' => 'promotion-5aspo',
                'case_use' => 'take_away',
                'type_discount' => 'percentage',
                'discount' => 5,
                'minimum_pretest' => 10,
                'cta' => '/asporto',
                'permanent' => false,
                'metadata' => [
                    'reusable' => false,
                    'description' => '5% di sconto su spesa asporto. Minima spesa 10€.',
                    'minimum_type' => 'cart_total',
                    'seed_key' => 'PROMOTION_5ASPO',
                ],
            ],
            [
                'name' => 'PROMOTION GIFTMARG',
                'slug' => 'promotion-giftmarg',
                'case_use' => 'gift',
                'type_discount' => 'gift',
                'discount' => null,
                'minimum_pretest' => 10,
                'cta' => '/asporto',
                'permanent' => true,
                'metadata' => [
                    'reusable' => false,
                    'description' => 'Margherita in regalo con minima spesa 10€.',
                    'minimum_type' => 'cart_total',
                    'product_link_required' => true,
                    'gift_type' => 'product',
                    'seed_key' => 'PROMOTION_GIFTMARG',
                ],
            ],
        ];
    }
}
