<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('campuses')->insert([
            'institution_name' => 'University of Science and Technology of Southern Philippines',
            'campus_name' => 'CDO',
            'address' => 'Lapasan, CdO',
            'email' => 'ustp_cdo@ustp.edu.ph',
            'contact_no' => '09000000001',
        ]);
    }
}
