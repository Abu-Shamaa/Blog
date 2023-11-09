<?php

namespace Database\Seeders;

use App\Models\Users\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user1 = User::factory()->create([
            'name' => 'Management',
            'email' => 'mgmt@elearning.com',
            'password' => Hash::make("password"),
        ]);
        $user1->assign('ar_mgmt');

        $user2 = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@elearning.com',
            'password' => Hash::make("password"),
        ]);
        $user2->assign('ar_admin');

        $user3 = User::factory()->create([
            'name' => 'Raton',
            'email' => 'raton@elearning.com',
            'password' => Hash::make("password"),
        ]);
        $user3->assign('ar_mgmt');

        $user4 = User::factory()->create([
            'name' => ' Asraf',
            'email' => 'asraf@elearning.com',
            'password' => Hash::make("password"),
        ]);
        $user4->assign('ar_mgmt');

     

        /*$user7 = User::factory()->create([
            'name' => 'Teacher Tow',
            'email' => 'teacher2@elearning.com',
            'password' => Hash::make("password"),
        ]);
        $user7->assign('ar_instructor');*/
    }
}
