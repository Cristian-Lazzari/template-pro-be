<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Model as EmailModel;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class MarketingBenvenutoSeeder extends Seeder
{
    private const MODEL_NAME    = 'MOD-OFFERTA-BENVENUTO';
    private const CAMPAIGN_NAME = 'CAMP-OFFERTA-BENVENUTO';

    public function run(): void
    {
        [$promoAsporto, $promoTavolo] = $this->seedPromotions();
        $model    = $this->seedModel();
        $campaign = $this->seedCampaign($model->id);

        $campaign->promotions()->syncWithoutDetaching([
            $promoAsporto->id => ['total_activation' => 0, 'total_sent' => 0],
            $promoTavolo->id  => ['total_activation' => 0, 'total_sent' => 0],
        ]);
    }

    private function seedPromotions(): array
    {
        $definitions = [
            [
                'slug'            => 'promo-asporto-5e',
                'name'            => 'PROMO ASPORTO -5€',
                'case_use'        => 'take_away',
                'type_discount'   => 'fixed',
                'discount'        => 5.00,
                'minimum_pretest' => 15.00,
                'cta'             => '/asporto',
                'permanent'       => false,
                'metadata'        => [
                    'reusable'        => false,
                    'description'     => '5€ di sconto generico sul carrello asporto. Minima spesa 15€.',
                    'minimum_type'    => 'cart_total',
                    'seed_key'        => 'PROMO_ASPORTO_5E',
                ],
            ],
            [
                'slug'            => 'promo-tavolo-benvenuto',
                'name'            => 'PROMO TAVOLO BENVENUTO',
                'case_use'        => 'table',
                'type_discount'   => 'percentage',
                'discount'        => 10.00,
                'minimum_pretest' => 2.00,
                'cta'             => '/prenota',
                'permanent'       => false,
                'metadata'        => [
                    'reusable'        => false,
                    'description'     => '10% di sconto prenotazione tavolo. Minimo 2 persone.',
                    'minimum_type'    => 'people',
                    'seed_key'        => 'PROMO_TAVOLO_BENVENUTO',
                ],
            ],
        ];

        $promotions = [];

        foreach ($definitions as $def) {
            $existing = Promotion::where('slug', $def['slug'])->first();

            $attrs = [
                'name'            => $def['name'],
                'status'          => $existing?->status ?: 'draft',
                'case_use'        => $def['case_use'],
                'type_discount'   => $def['type_discount'],
                'discount'        => $def['discount'],
                'minimum_pretest' => $def['minimum_pretest'],
                'cta'             => $def['cta'],
                'permanent'       => $def['permanent'],
                'schedule_at'     => null,
                'expiring_at'     => null,
                'metadata'        => array_replace(
                    is_array($existing?->metadata) ? $existing->metadata : [],
                    $def['metadata']
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

            $promotions[] = Promotion::updateOrCreate(['slug' => $def['slug']], $attrs);
        }

        return $promotions;
    }

    private function seedModel(): EmailModel
    {
        return EmailModel::firstOrCreate(
            ['name' => self::MODEL_NAME],
            [
                'object'  => 'Un\'offerta esclusiva solo per te 🎁',
                'heading' => 'La tua offerta personale',
                'body'    => implode("\n\n", [
                    'Ciao {{nome}},',
                    'abbiamo riservato per te una doppia offerta speciale:',
                    '🛍️  Asporto: -5€ sul carrello con una spesa minima di 15€.',
                    '🍽️  Prenotazione tavolo: -10% con almeno 2 persone.',
                    'Usa la tua offerta entro la scadenza {{scadenza}}.',
                    'Clicca qui per approfittarne subito: {{link_offerta}}',
                ]),
                'ending'   => "Non vediamo l'ora di rivederti!\n{{nome_ristorante}}",
                'sender'   => '{{nome_ristorante}}',
                'type'     => 'marketing',
                'channel'  => Campaign::CHANNEL_EMAIL,
                'status'   => 'draft',
                'variables' => ['nome', 'nome_ristorante', 'link_offerta', 'scadenza'],
                'preview_data' => [
                    'nome'            => 'Mario',
                    'nome_ristorante' => 'Il Ristorante',
                    'link_offerta'    => '#',
                    'scadenza'        => '31/12/2026',
                ],
            ]
        );
    }

    private function seedCampaign(int $modelId): Campaign
    {
        return Campaign::firstOrCreate(
            ['name' => self::CAMPAIGN_NAME],
            [
                'status'           => 'draft',
                'campaign_type'    => Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING,
                'channel'          => Campaign::CHANNEL_EMAIL,
                'consent_basis'    => Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING,
                'segment'          => null,
                'model_id'         => $modelId,
                'total_activation' => 0,
                'total_sent'       => 0,
                'metadata'         => [
                    'seed_key'    => self::CAMPAIGN_NAME,
                    'description' => 'Campagna benvenuto con doppia offerta: -5€ asporto + -10% prenotazione tavolo.',
                ],
            ]
        );
    }
}
