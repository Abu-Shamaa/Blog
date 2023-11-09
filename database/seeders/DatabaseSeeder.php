<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Articles\Article;
use App\Models\Users\User;
use Illuminate\Database\Seeder;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call([
            BouncerSeeder::class,
            UserSeeder::class,
            GroupSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
