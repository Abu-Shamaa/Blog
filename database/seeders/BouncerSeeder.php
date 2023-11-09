<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class BouncerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bouncer::allow('ar_mgmt')->to(['aa_mgmt', 'aa_admin', 'aa_staff1', 'aa_staff2', 'access_elearning']);
        Bouncer::allow('ar_admin')->to(['aa_admin', 'aa_staff1', 'aa_staff2', 'access_elearning']);
        Bouncer::allow('ar_staff1')->to(['aa_staff1', 'aa_staff2', 'access_elearning']);
        Bouncer::allow('ar_staff2')->to(['aa_staff2', 'access_elearning']);
        Bouncer::allow('ar_instructor')->to(['aa_instructor', 'access_elearning']);
    }
}
