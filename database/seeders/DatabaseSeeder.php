<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
//use Database\Seeders\PostsTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\ProductsTableSeeder;
use Database\Seeders\SettingsTableSeeder;
use Database\Seeders\AllergensFromConfigSeeder;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
     

        $this->call([
            UsersTableSeeder::class,
            SettingsTableSeeder::class,
            AllergensFromConfigSeeder::class,
            
            //PostsTableSeeder::class,
            ProductsTableSeeder::class,
        ]);
    }
}
