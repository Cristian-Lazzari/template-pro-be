<?php
return [

    'STRIPE_SECRET'         => env('STRIPE_SECRET'),
    'STRIPE_WEBHOOK_SECRET' => env('STRIPE_WEBHOOK_SECRET'),
    'APP_URL'               => env('APP_URL'),
    'APP_NAME'              => env('APP_NAME'),
    'GOOGLE_TRASLATE_KEY'   => env('GOOGLE_TRASLATE_KEY'),
    
    'us'                  => env('MAIL_USERNAME'),
    'pw'                  => env('MAIL_PASSWORD'),
    'hs'                  => env('MAIL_HOST'),
    'mf'                  => env('MAIL_FROM_ADDRESS'),

    'db'                    => env('DB_DATABASE'),
    
    'WA_TO'                 => env('WA_TO'),
    'WA_ID'                 => env('WA_ID'),
    
    'domain'                => env('DOMAIN'),
    'subscription'          => env('SUBSCRIPTION'),
    
    'MSC_P'                 => env('MSC_P'),
    'default_lang'          => env('DEFAULT_LANG'),
   

    'allergens' => [
        1 => [
            'special' => 4,
            'img' => 'https://future-plus.it/allergens/gluten-free.png',
            'name' => [
                'it' => 'senza glutine',
                'en' => 'gluten free',
                'es' => 'sin gluten',
                'fr' => 'sans gluten',
                'de' => 'glutenfrei',
                'ja' => 'グルテンフリー',
                'ro' => 'fără gluten',
            ]
        ],
        2 => [
            'special' => 1,
            'img' => 'https://future-plus.it/allergens/spicy.png',
            'name' => [
                'it' => 'piccante',
                'en' => 'spicy',
                'es' => 'picante',
                'fr' => 'épicé',
                'de' => 'scharf',
                'ja' => '辛い',
                'ro' => 'picant',
            ]
        ],
        3 => [
            'special' => 1,
            'img' => 'https://future-plus.it/allergens/veggy.png',
            'name' => [
                'it' => 'vegano',
                'en' => 'vegan',
                'es' => 'vegano',
                'fr' => 'végétalien',
                'de' => 'vegan',
                'ja' => 'ヴィーガン',
                'ro' => 'vegan',
            ]
        ],
        4 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/gluten.png',
            'name' => [
                'it' => 'glutine',
                'en' => 'gluten',
                'es' => 'gluten',
                'fr' => 'gluten',
                'de' => 'gluten',
                'ja' => 'グルテン',
                'ro' => 'gluten',
            ]
        ],
        5 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/sesame.png',
            'name' => [
                'it' => 'sesamo',
                'en' => 'sesame',
                'es' => 'sésamo',
                'fr' => 'sésame',
                'de' => 'sesam',
                'ja' => 'ごま',
                'ro' => 'susan',
            ]
        ],
        6 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/peanut.png',
            'name' => [
                'it' => 'arachidi',
                'en' => 'peanuts',
                'es' => 'cacahuetes',
                'fr' => 'arachides',
                'de' => 'erdnüsse',
                'ja' => 'ピーナッツ',
                'ro' => 'arahide',
            ]
        ],
        7 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/soy.png',
            'name' => [
                'it' => 'soia',
                'en' => 'soy',
                'es' => 'soja',
                'fr' => 'soja',
                'de' => 'soja',
                'ja' => '大豆',
                'ro' => 'soia',
            ]
        ],
        8 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/molluschi.png',
            'name' => [
                'it' => 'molluschi',
                'en' => 'molluscs',
                'es' => 'moluscos',
                'fr' => 'mollusques',
                'de' => 'weichtiere',
                'ja' => '軟体動物',
                'ro' => 'moluste',
            ]
        ],
        9 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/sedano.png',
            'name' => [
                'it' => 'sedano',
                'en' => 'celery',
                'es' => 'apio',
                'fr' => 'céleri',
                'de' => 'sellerie',
                'ja' => 'セロリ',
                'ro' => 'țelină',
            ]
        ],
        10 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/senape.png',
            'name' => [
                'it' => 'senape',
                'en' => 'mustard',
                'es' => 'mostaza',
                'fr' => 'moutarde',
                'de' => 'senf',
                'ja' => 'マスタード',
                'ro' => 'muștar',
            ]
        ],
        11 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/egg.png',
            'name' => [
                'it' => 'uova',
                'en' => 'eggs',
                'es' => 'huevos',
                'fr' => 'œufs',
                'de' => 'eier',
                'ja' => '卵',
                'ro' => 'ouă',
            ]
        ],
        12 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/dairy.png',
            'name' => [
                'it' => 'latticini',
                'en' => 'dairy',
                'es' => 'lácteos',
                'fr' => 'produits laitiers',
                'de' => 'milchprodukte',
                'ja' => '乳製品',
                'ro' => 'lactate',
            ]
        ],
        13 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/fish.png',
            'name' => [
                'it' => 'pesce',
                'en' => 'fish',
                'es' => 'pescado',
                'fr' => 'poisson',
                'de' => 'fisch',
                'ja' => '魚',
                'ro' => 'pește',
            ]
        ],
        14 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/crab.png',
            'name' => [
                'it' => 'crostacei',
                'en' => 'crustaceans',
                'es' => 'crustáceos',
                'fr' => 'crustacés',
                'de' => 'krebstiere',
                'ja' => '甲殻類',
                'ro' => 'crustacee',
            ]
        ],
        15 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/peanut.png',
            'name' => [
                'it' => 'frutta con guscio',
                'en' => 'tree nuts',
                'es' => 'frutos secos',
                'fr' => 'fruits à coque',
                'de' => 'schalenfrüchte',
                'ja' => 'ナッツ類',
                'ro' => 'fructe cu coajă',
            ]
        ],
        16 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/sulphur.png',
            'name' => [
                'it' => 'anidride solforosa e solfiti',
                'en' => 'sulphur dioxide and sulphites',
                'es' => 'dióxido de azufre y sulfitos',
                'fr' => 'anhydride sulfureuse et sulfites',
                'de' => 'schwefeldioxid und sulfite',
                'ja' => '亜硫酸塩',
                'ro' => 'dioxid de sulf și sulfiți',
            ]
        ],
        17 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/garlic.png',
            'name' => [
                'it' => 'aglio',
                'en' => 'garlic',
                'es' => 'ajo',
                'fr' => 'ail',
                'de' => 'knoblauch',
                'ja' => 'にんにく',
                'ro' => 'usturoi',
            ]
        ],
        18 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/lupins.png',
            'name' => [
                'it' => 'lupini',
                'en' => 'lupins',
                'es' => 'altramuces',
                'fr' => 'lupins',
                'de' => 'lupinen',
                'ja' => 'ルピナス',
                'ro' => 'lupin',
            ]
        ],
        19 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/origano.png',
            'name' => [
                'it' => 'origano',
                'en' => 'oregano',
                'es' => 'orégano',
                'fr' => 'origan',
                'de' => 'oregano',
                'ja' => 'オレガノ',
                'ro' => 'oregano',
            ]
        ],
    ],
    
];
