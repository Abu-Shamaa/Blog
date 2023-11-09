<?php

namespace Database\Seeders;

use App\Models\Articles\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = Category::factory()->create([
            'name' => 'Default',
            'description' => 'Default Category',
            'slug' => 'default',
        ]);
    }
}