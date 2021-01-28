<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('offices')->insert([
            'office_name' => 'DTO',
            'contact' => '09111111111',
            'email' => 'dto@ustp.edu.ph',
            'campus_id' => 1
        ]);
    }
}
