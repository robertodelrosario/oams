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
            'suc_id' => 1,
            'campus_name' => 'USTP-CDO',
            'address' => 'Lapasan, CdO',
            'region' => 'region X',
            'email' => 'ustp_cdo@ustp.edu.ph',
            'contact_no' => '09000000001',
        ]);

        DB::table('campuses')->insert([
            'suc_id' => 1,
            'campus_name' => 'USTP-JASAAN',
            'address' => 'Jasaan',
            'region' => 'region 10',
            'email' => 'ustp_cdo@ustp.edu.ph',
            'contact_no' => '09000000001',
        ]);
    }
}
