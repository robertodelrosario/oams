<?php

use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('programs')->insert([
            'program_name' => 'IT',
            'accreditation_status' => 'level 3',
            'duration_of_validity' => '2020-10-10',
            'suc_id' => '1'
        ]);
        DB::table('programs')->insert([
            'program_name' => 'CpE',
            'accreditation_status' => 'level 3',
            'duration_of_validity' => '2020-10-10',
            'suc_id' => '1'
        ]);
        DB::table('programs')->insert([
            'program_name' => 'ComSci',
            'accreditation_status' => 'level 3',
            'duration_of_validity' => '2020-10-10',
            'suc_id' => '1'
        ]);
    }
}
