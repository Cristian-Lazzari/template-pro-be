<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        DB::table('categories')->insert([
            [
                'name' => 'Collaborazioni nostalgia', 
            ],
            [
                'name' => 'Mondo del cibo', 
            ],
            [
                'name' => 'Video Games', 
            ],
            [
                'name' => 'Cinema', 
            ],
            [
                'name' => 'Arte e Musei', 
            ],
        ]);
    }
}
