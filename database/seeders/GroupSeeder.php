<?php

namespace Database\Seeders;

use App\Models\Groups\Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $group = Group::factory()->create([
            'name' => 'Default',
        ]);
    }
}
