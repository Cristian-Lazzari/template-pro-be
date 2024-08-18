<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\DatesTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\ProductsTableSeeder;
use Database\Seeders\SettingsTableSeeder;
use Database\Seeders\IngredientsTableSeeder;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
     

        $this->call([
            UsersTableSeeder::class,
            ProductsTableSeeder::class,
            CategoriesTableSeeder::class,
            SettingsTableSeeder::class,
        ]);
    }
}
