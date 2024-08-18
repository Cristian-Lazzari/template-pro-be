<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name'          => 'Tutti',
                'icon'          => '',
            ],
            [
                'name'          => 'Primi',
                'icon'          => '',
            ],
            [
                'name'          => 'Secondi',
                'icon'          => '',
            ],
            [
                'name'          => 'Contorni',
                'icon'          => '',
            ],
            [
                'name'          => 'Fritture',
                'icon'          => '',     
            ],
            [
                'name'          => 'Hosomaki',
                'icon'          => '',
            ],
            [
                'name'          => 'Hosomaki fritto',
                'icon'          => '',
            ],
            [
                'name'          => 'Piatti Speciali',
                'icon'          => '',
            ],
            [
                'name'          => 'Gunkan',
                'icon'          => '',
            ],
            [
                'name'          => 'Antipasti Cucina',
                'icon'          => '',
            ],
            [
                'name'          => 'Antipasti Freddi',
                'icon'          => '',
            ],
            [
                'name'          => 'Piastra',
                'icon'          => '',
            ],
            [
                'name'          => 'Nigiri',
                'icon'          => '',
            ],
            [
                'name'          => 'Flambè',
                'icon'          => '',
            ],
            [
                'name'          => 'Sashimi',
                'icon'          => '',
            ],
            [
                'name'          => 'Carpacci',
                'icon'          => '',
            ],
            [
                'name'          => 'Temaki',
                'icon'          => '',
            ],
            [
                'name'          => 'Uramaki',
                'icon'          => '',
            ],

            [
                'name'          => 'Vaporiera',
                'icon'          => '',
            ],
            [
                'name'          => 'Menu-mix Sushi',
                'icon'          => '',
            ],

            [
                'name'          => 'Bevande',
                'icon'          => '',
            ],
            [
                'name'          => 'Birre',
                'icon'          => '',
            ],
            [
                'name'          => 'Amari',
                'icon'          => '',
            ],
            [
                'name'          => 'Dessert',
                'icon'          => '',
            ],
            [
                'name'          => 'Caffetteria',
                'icon'          => '',
            ],
            [
                'name'          => 'Grappe',
                'icon'          => '',
            ],
            [
                'name'          => 'Vino Bianco',
                'icon'          => '',
            ],
            [
                'name'          => 'Vino Rosè',
                'icon'          => '',
            ],
            [
                'name'          => 'Spina',
                'icon'          => '',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
