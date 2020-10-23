<?php

use Illuminate\Database\Seeder;

class SUCSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sucs')->insert([
            'institution_name' => 'University of Science and Technology of Southern Philippines',
            'address' => 'Lapasan, CdO',
            'email' => 'ustp_cdo@ustp.edu.ph',
            'contact_no' => '09000000001',
        ]);
    }
}
