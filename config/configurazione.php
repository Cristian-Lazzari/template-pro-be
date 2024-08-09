<?php
return [

    'typeOfOrdering' => false,
    'pack' => 3,
    'maxdayres' => 45, //giorni massimi in cui si vuole ricevere le prentazioni
    'domain' => 'http://localhost:5174',

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
        1 => ['time' => '11:00', 'set' => ''] ,
        2 => ['time' => '11:20', 'set' => ''] ,
        3 => ['time' => '11:40', 'set' => ''] ,
        4 => ['time' => '12:00', 'set' => ''] ,
        5 => ['time' => '12:20', 'set' => ''] ,
        6 => ['time' => '12:40', 'set' => ''] ,
        7 => ['time' => '13:00', 'set' => ''] ,
        8 => ['time' => '13:20', 'set' => ''] ,
        9 => ['time' => '13:40', 'set' => ''] ,
        10 => ['time' => '14:00', 'set' => ''] ,
        11 => ['time' => '14:20', 'set' => ''] ,
        12 => ['time' => '14:40', 'set' => ''] ,
        13 => ['time' => '15:00', 'set' => ''] ,
        14 => ['time' => '15:20', 'set' => ''] ,
        15 => ['time' => '15:40', 'set' => ''] ,
        16 => ['time' => '16:00', 'set' => ''] ,
        17 => ['time' => '16:20', 'set' => ''] ,
        18 => ['time' => '16:40', 'set' => ''] ,
        19 => ['time' => '17:00', 'set' => ''] ,
        20 => ['time' => '17:20', 'set' => ''] ,
        21 => ['time' => '17:40', 'set' => ''] ,
        22 => ['time' => '18:00', 'set' => ''] ,
        23 => ['time' => '18:20', 'set' => ''] ,
        24 => ['time' => '18:40', 'set' => ''] ,
        25 => ['time' => '19:00', 'set' => ''] ,
        26 => ['time' => '19:20', 'set' => ''] ,
        27 => ['time' => '19:40', 'set' => ''] ,
        28 => ['time' => '20:00', 'set' => ''] ,
        29 => ['time' => '20:20', 'set' => ''] ,
        30 => ['time' => '20:40', 'set' => ''] ,
        31 => ['time' => '21:00', 'set' => ''] ,
        32 => ['time' => '21:20', 'set' => ''] ,
        33 => ['time' => '21:40', 'set' => ''] ,
        34 => ['time' => '22:00', 'set' => ''] ,
        35 => ['time' => '22:20', 'set' => ''] ,
        36 => ['time' => '22:40', 'set' => ''] ,
        37 => ['time' => '23:00', 'set' => ''] ,
        38 => ['time' => '23:20', 'set' => ''] ,
        39 => ['time' => '23:40', 'set' => ''] ,
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
    'set_time_2' => [
        'tavoli', 
        'asporto',
        'domicilio',
    ],
    
];
