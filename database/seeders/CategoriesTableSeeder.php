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
                'icon'          => 'https://db.kojo-sushi.it/public/image/or.png'
            ],
            [
                'name'          => 'Pizze Speciali',
                'icon'          => 'https://db.kojo-sushi.it/public/image/or.png'
            ],
            [
                'name'          => 'Pizze Rosse',
                'icon'          => 'https://db.kojo-sushi.it/public/image/or.png'     
            ],
            [
                'name'          => 'Pizze Bianche',
                'icon'          => 'https://db.kojo-sushi.it/public/image/or.png'
            ],
            [
                'name'          => 'Dolci',
                'icon'          => 'https://db.kojo-sushi.it/public/image/or.png'
            ],
            [
                'name'          => 'Bibite',
                'icon'          => 'https://db.kojo-sushi.it/public/image/or.png'
            ],
            [
                'name'          => 'Pezzi al taglio',
                'icon'          => 'https://db.kojo-sushi.it/public/image/or.png'
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
