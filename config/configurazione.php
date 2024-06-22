<?php
return [

    'typeOfOrdering' => true,
    'pack' => 4,

    'allergiens' => [
        1 => [
            'special' => 4,
            'img' => 'https://future-plus.it/allergiens/gluten-free.png',
            'name' => 'senza glutine'
        ],
        2 => [
            'special' => 1,
            'img' => 'https://future-plus.it/allergiens/spicy.png',
            'name' => 'piccante'
        ],
        3 => [
            'special' => 1,
            'img' => 'https://future-plus.it/allergiens/veggy.png',
            'name' => 'vegano'
        ],
        4 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergiens/gluten.png',
            'name' => 'glutine'   
        ],
        5 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergiens/sesame.png',
            'name' => 'sesamo'
        ],
        6 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergiens/peanut.png',
            'name' => 'arachidi'
        ],
        7 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergiens/soy.png',
            'name' => 'soia'
        ],
        8 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergiens/molluschi.png',
            'name' => 'molluschi'
        ],
        9 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergiens/sedano.png',
            'name' => 'sedano'
        ],
        10 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergiens/senape.png',
            'name' => 'senape'
        ],
        11 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergiens/egg.png',
            'name' => 'uova'
        ],
        12 => [
            'special' => 0,
            'img' => 'https://future-plus.it/allergiens/dairy.png',
            'name' => 'latticini'
        ],
        13 => [
           'special' => 0,
            'img' => 'https://future-plus.it/allergiens/fish.png',
            'name' => 'pesce'
        ],
        14 => [
           'special' => 0,
            'img' => 'https://future-plus.it/allergiens/crab.png',
            'name' => 'crostacei'
        ],
    ],
    'times' => [
        1 => ['time' => '19:00', 'set' => ''] ,
        2 => ['time' => '19:15', 'set' => ''] ,
        3 => ['time' => '19:30', 'set' => ''] ,
        4 => ['time' => '19:45', 'set' => ''] ,
        5 => ['time' => '20:00', 'set' => ''] ,
        6 => ['time' => '20:15', 'set' => ''] ,
        7 => ['time' => '20:30', 'set' => ''] ,
    ],
    'days' => [1, 2, 3, 4, 5, 6, 7],
    'mesi' => ['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'],
    'days_name' => [' ','lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'],
    'set_time' => [
        'tavoli', 
        'pezzi al taglio',
        'pizzze al piatto',
        'consegna a domicilio',
    ],
    
];
