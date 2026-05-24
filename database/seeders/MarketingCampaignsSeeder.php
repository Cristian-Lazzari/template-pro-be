<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Model as EmailModel;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class MarketingCampaignsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->definitions() as $def) {
            $campaigns = $def['campaigns'] ?? [];
            unset($def['campaigns']);

            $model = EmailModel::firstOrCreate(
                ['name' => $def['name']],
                $def
            );

            foreach ($campaigns as $campDef) {
                $promotionSlugs = $campDef['promotion_slugs'] ?? [];
                unset($campDef['promotion_slugs']);

                $campaign = Campaign::firstOrCreate(
                    ['name' => $campDef['name']],
                    array_merge($campDef, ['model_id' => $model->id])
                );

                foreach ($promotionSlugs as $slug) {
                    $promo = Promotion::where('slug', $slug)->first();

                    if ($promo) {
                        $campaign->promotions()->syncWithoutDetaching([
                            $promo->id => ['total_activation' => 0, 'total_sent' => 0],
                        ]);
                    }
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Definizioni: 5 modelli email + 5 campagne
    // -------------------------------------------------------------------------

    private function definitions(): array
    {
        return [

            // ═══════════════════════════════════════════════════════════════
            // MODEL 1 · MOD-BENVENUTO
            // Tipo: explicit email marketing | Campagna: benvenuto nuovi clienti
            // Promozioni: asporto -10% + tavolo -15%
            // ═══════════════════════════════════════════════════════════════
            [
                'name'          => 'MOD-BENVENUTO',
                'object'        => "Benvenuto! Un'offerta esclusiva solo per te 🎁",
                'heading'       => 'Siamo felici di averti con noi',
                'body'          => implode("\n\n", [
                    'Ciao @customer_first_name,',
                    "grazie per esserti registrato/a. Per festeggiare il tuo arrivo abbiamo preparato una doppia offerta speciale:",
                    "🛍️  Asporto: -10% sul carrello con una spesa minima di 15€.",
                    "🍽️  Prenotazione tavolo: -15% con almeno 2 persone.",
                    "Usa la tua offerta prima che scada!",
                ]),
                'ending'        => "Non vediamo l'ora di vederti!",
                'sender'        => 'Il Ristorante',
                'type'          => 'marketing',
                'channel'       => Campaign::CHANNEL_EMAIL,
                'status'        => 'draft',
                'has_promotion' => true,
                'cta_label'     => "Scopri l'offerta",
                'variables'     => ['customer_first_name'],
                'preview_data'  => [
                    'customer_first_name' => 'Mario',
                ],
                'campaigns' => [
                    [
                        'name'             => 'CAMP-BENVENUTO',
                        'status'           => 'draft',
                        'campaign_type'    => Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING,
                        'channel'          => Campaign::CHANNEL_EMAIL,
                        'consent_basis'    => Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING,
                        'segment'          => null,
                        'total_activation' => 0,
                        'total_sent'       => 0,
                        'metadata'         => [
                            'seed_key'    => 'CAMP_BENVENUTO',
                            'description' => 'Campagna benvenuto nuovi clienti: -10% asporto + -15% prenotazione tavolo.',
                        ],
                        'promotion_slugs' => ['promo-asporto-10pct', 'promo-tavolo-15pct'],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MODEL 2 · MOD-COMPLEANNO
            // Tipo: explicit email marketing | Campagna: regalo compleanno
            // Promozione: antipasto in omaggio (gift generico)
            // ═══════════════════════════════════════════════════════════════
            [
                'name'          => 'MOD-COMPLEANNO',
                'object'        => 'Buon compleanno @customer_first_name! 🎂 -20% sul tavolo per festeggiare',
                'heading'       => 'Tanti auguri @customer_first_name!',
                'body'          => implode("\n\n", [
                    'Ciao @customer_first_name,',
                    'oggi è il tuo giorno speciale e vogliamo festeggiarlo insieme a te!',
                    '🎂 Per celebrare il tuo compleanno ti regaliamo il 20% di sconto sulla prenotazione tavolo con almeno 2 persone.',
                    'Vieni a trovarci e mostra questa email al nostro staff.',
                ]),
                'ending'        => 'Auguri di cuore!',
                'sender'        => 'Il Ristorante',
                'type'          => 'marketing',
                'channel'       => Campaign::CHANNEL_EMAIL,
                'status'        => 'draft',
                'has_promotion' => true,
                'cta_label'     => 'Prenota il tuo tavolo',
                'variables'     => ['customer_first_name'],
                'preview_data'  => [
                    'customer_first_name' => 'Laura',
                ],
                'campaigns' => [
                    [
                        'name'             => 'CAMP-COMPLEANNO',
                        'status'           => 'draft',
                        'campaign_type'    => Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING,
                        'channel'          => Campaign::CHANNEL_EMAIL,
                        'consent_basis'    => Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING,
                        'segment'          => 'compleanno',
                        'total_activation' => 0,
                        'total_sent'       => 0,
                        'metadata'         => [
                            'seed_key'    => 'CAMP_COMPLEANNO',
                            'description' => 'Campagna compleanno: -20% sulla prenotazione tavolo per i clienti nel mese del compleanno.',
                        ],
                        'promotion_slugs' => ['promo-compleanno-tavolo-20pct'],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MODEL 3 · MOD-WEEKEND-PROMO
            // Tipo: soft marketing | Campagna: promo weekend
            // Promozione: -10% asporto solo sabato/domenica
            // ═══════════════════════════════════════════════════════════════
            [
                'name'          => 'MOD-WEEKEND-PROMO',
                'object'        => "Questo weekend ordina e risparmia il 10% 🛍️",
                'heading'       => 'Offerta speciale solo questo weekend',
                'body'          => implode("\n\n", [
                    'Ciao @customer_first_name,',
                    'questo fine settimana abbiamo una sorpresa per te!',
                    "🎉 -10% su tutto l'asporto sabato e domenica con una spesa minima di 10€.",
                    "L'offerta è attiva automaticamente: non hai bisogno di codici.",
                ]),
                'ending'        => 'Buon weekend!',
                'sender'        => 'Il Ristorante',
                'type'          => 'marketing',
                'channel'       => Campaign::CHANNEL_EMAIL,
                'status'        => 'draft',
                'has_promotion' => true,
                'cta_label'     => 'Ordina ora',
                'variables'     => ['customer_first_name'],
                'preview_data'  => [
                    'customer_first_name' => 'Giulia',
                ],
                'campaigns' => [
                    [
                        'name'             => 'CAMP-WEEKEND-PROMO',
                        'status'           => 'draft',
                        'campaign_type'    => Campaign::CAMPAIGN_TYPE_SOFT_MARKETING,
                        'channel'          => Campaign::CHANNEL_EMAIL,
                        'consent_basis'    => Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING,
                        'segment'          => null,
                        'total_activation' => 0,
                        'total_sent'       => 0,
                        'metadata'         => [
                            'seed_key'    => 'CAMP_WEEKEND_PROMO',
                            'description' => "Campagna soft weekend: -10% asporto valido sabato e domenica.",
                        ],
                        'promotion_slugs' => ['promo-weekend-10pct'],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MODEL 4 · MOD-RIATTIVAZIONE
            // Tipo: explicit email marketing | Campagna: clienti inattivi
            // Promozione: -5€ fisso asporto
            // ═══════════════════════════════════════════════════════════════
            [
                'name'          => 'MOD-RIATTIVAZIONE',
                'object'        => 'Ci manchi @customer_first_name! 😊 Ecco qualcosa per te',
                'heading'       => "È da un po' che non ti vediamo...",
                'body'          => implode("\n\n", [
                    'Ciao @customer_first_name,',
                    "è passato un po' di tempo dalla tua ultima visita e ci hai mancato!",
                    'Per salutarti come si deve ti abbiamo riservato uno sconto speciale:',
                    "🛍️  -5€ sul tuo prossimo ordine asporto con una spesa minima di 20€.",
                ]),
                'ending'        => 'A presto!',
                'sender'        => 'Il Ristorante',
                'type'          => 'marketing',
                'channel'       => Campaign::CHANNEL_EMAIL,
                'status'        => 'draft',
                'has_promotion' => true,
                'cta_label'     => 'Torna a trovarci',
                'variables'     => ['customer_first_name'],
                'preview_data'  => [
                    'customer_first_name' => 'Marco',
                ],
                'campaigns' => [
                    [
                        'name'             => 'CAMP-RIATTIVAZIONE',
                        'status'           => 'draft',
                        'campaign_type'    => Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING,
                        'channel'          => Campaign::CHANNEL_EMAIL,
                        'consent_basis'    => Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING,
                        'segment'          => 'inattivi',
                        'total_activation' => 0,
                        'total_sent'       => 0,
                        'metadata'         => [
                            'seed_key'    => 'CAMP_RIATTIVAZIONE',
                            'description' => 'Campagna riattivazione clienti inattivi: -5€ sul prossimo ordine asporto.',
                        ],
                        'promotion_slugs' => ['promo-asporto-5e'],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════════
            // MODEL 5 · MOD-HAPPY-HOUR
            // Tipo: soft marketing | Campagna: aperitivo happy hour
            // Promozione: -15% tavolo dalle 18 alle 20
            // ═══════════════════════════════════════════════════════════════
            [
                'name'          => 'MOD-HAPPY-HOUR',
                'object'        => 'Aperitivo speciale? -15% prenotando ora 🍷',
                'heading'       => 'Happy Hour: prenota tra le 18:00 e le 20:00',
                'body'          => implode("\n\n", [
                    'Ciao @customer_first_name,',
                    "stai cercando dove passare l'aperitivo perfetto?",
                    "🍷 -15% sulla prenotazione tavolo per tutte le serate dalle 18:00 alle 20:00.",
                    "Approfitta dell'offerta e prenota subito il tuo posto!",
                ]),
                'ending'        => 'Ti aspettiamo!',
                'sender'        => 'Il Ristorante',
                'type'          => 'marketing',
                'channel'       => Campaign::CHANNEL_EMAIL,
                'status'        => 'draft',
                'has_promotion' => true,
                'cta_label'     => 'Prenota il tuo tavolo',
                'variables'     => ['customer_first_name'],
                'preview_data'  => [
                    'customer_first_name' => 'Sara',
                ],
                'campaigns' => [
                    [
                        'name'             => 'CAMP-HAPPY-HOUR',
                        'status'           => 'draft',
                        'campaign_type'    => Campaign::CAMPAIGN_TYPE_SOFT_MARKETING,
                        'channel'          => Campaign::CHANNEL_EMAIL,
                        'consent_basis'    => Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING,
                        'segment'          => null,
                        'total_activation' => 0,
                        'total_sent'       => 0,
                        'metadata'         => [
                            'seed_key'    => 'CAMP_HAPPY_HOUR',
                            'description' => 'Campagna soft happy hour: -15% prenotazione tavolo nella fascia 18–20.',
                        ],
                        'promotion_slugs' => ['promo-happy-hour-tavolo'],
                    ],
                ],
            ],
        ];
    }
}
