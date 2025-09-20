<?php
return [

    'STRIPE_SECRET'         => env('STRIPE_SECRET'),
    'STRIPE_WEBHOOK_SECRET' => env('STRIPE_WEBHOOK_SECRET'),
    'APP_URL'               => env('APP_URL'),
    'APP_NAME'              => env('APP_NAME'),
    
    'us'                  => env('MAIL_USERNAME'),
    'pw'                  => env('MAIL_PASSWORD'),
    'hs'                  => env('MAIL_HOST'),

    'db'                    => env('DB_DATABASE'),
    
    'WA_TO'                 => env('WA_TO'),
    'WA_ID'                 => env('WA_ID'),
    
    'domain'                => env('DOMAIN'),
    'subscription'          => env('SUBSCRIPTION'),
    
    'MSC_P'                 => env('MSC_P'),

    'allergens' => [
        1 => [
            'special' => 4,
            'img' => 'https://future-plus.it/allergens/gluten-free.png',
            'name' => 'senza glutine'
        ],
        2 => [
            'special' => 1,
            'img' => 'https://future-plus.it/allergens/spicy.png',
            'name' => 'piccante'
        ],
        3 => [
            'special' => 1,
            'img' => 'https://future-plus.it/allergens/veggy.png',
            'name' => 'vegano'
        ],
        4 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/gluten.png',
            'name' => 'glutine'   
        ],
        5 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/sesame.png',
            'name' => 'sesamo'
        ],
        6 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/peanut.png',
            'name' => 'arachidi'
        ],
        7 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/soy.png',
            'name' => 'soia'
        ],
        8 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/molluschi.png',
            'name' => 'molluschi'
        ],
        9 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/sedano.png',
            'name' => 'sedano'
        ],
        10 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/senape.png',
            'name' => 'senape'
        ],
        11 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/egg.png',
            'name' => 'uova'
        ],
        12 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/dairy.png',
            'name' => 'latticini'
        ],
        13 => [
           'special' => 0,
            'img' => 'https://future-plus.it/allergens/fish.png',
            'name' => 'pesce'
        ],
        14 => [
           'special' => 0,
            'img' => 'https://future-plus.it/allergens/crab.png',
            'name' => 'crostacei'
        ],
        15 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/peanut.png',
            'name' => 'frutta con guscio'
        ],
        16 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/sulphur.png',
            'name' => 'Anidride solforosa e solfiti'
        ],
        17 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/garlic.png',
            'name' => 'Aglio'
        ],
        18 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/lupins.png',
            'name' => 'Lupini'
        ],
        19 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergens/origano.png',
            'name' => 'Origano'
        ],
    ],
    
];
