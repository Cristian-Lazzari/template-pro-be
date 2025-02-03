<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\DatesTableSeeder;

use Database\Seeders\UsersTableSeeder;

use Database\Seeders\CategoriesTableSeeder;



class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            PostsTableSeeder::class,
            CategoriesTableSeeder::class,
        ]);
    }
}
