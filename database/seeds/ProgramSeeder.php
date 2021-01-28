<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            'latest_applied_level' => 'Level III, Phase 2',
            'accreditation_status' => 'Level III Re-accredited',
            'duration_of_validity' => '2020-10-10',
            'campus_id' => '1'
        ]);
        DB::table('programs')->insert([
            'program_name' => 'CpE',
            'latest_applied_level' => 'Level III, Phase 2',
            'accreditation_status' => 'Level III Re-accredited',
            'duration_of_validity' => '2020-10-10',
            'campus_id' => '1'
        ]);
        DB::table('programs')->insert([
            'program_name' => 'ComSci',
            'latest_applied_level' => 'Level III, Phase 2',
            'accreditation_status' => 'Level III Re-accredited',
            'duration_of_validity' => '2020-10-10',
            'campus_id' => '1'
        ]);
    }
}
