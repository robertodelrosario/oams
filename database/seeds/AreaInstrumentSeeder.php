<?php

use Illuminate\Database\Seeder;

class AreaInstrumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('programs_instruments')->insert([
            'intended_program' => 'BS Information Technology',
            'type_of_instrument' => 'OBE Instrument of IT',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program_id' => '1',
            'area_number' => '1',
            'area_name' => 'AREA 1: VISION, MISSION, GOALS AND OBJECTIVES',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program_id' => '1',
            'area_number' => '2',
            'area_name' => 'AREA II: FACULTY',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program_id' => '1',
            'area_number' => '3',
            'area_name' => 'AREA III: CURRICULUM AND INSTRUCTION',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program_id' => '1',
            'area_number' => '4',
            'area_name' => 'AREA IV: SUPPORT TO STUDENTS',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program_id' => '1',
            'area_number' => '5',
            'area_name' => 'AREA V: RESEARCH',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program_id' => '1',
            'area_number' => '6',
            'area_name' => 'AREA VI: EXTENSION AND COMMUNITY INVOLVEMENT',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program_id' => '1',
            'area_number' => '7',
            'area_name' => 'AREA VII: LIBRARY',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program_id' => '1',
            'area_number' => '8',
            'area_name' => 'AREA VIII: PHYSICAL PLANT AND FACILITIES',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);DB::table('area_instruments')->insert([
        'intended_program_id' => '1',
        'area_number' => '9',
        'area_name' => 'AREA IX: LABORATORIES',
        'version' => '1',
        'created_at' => new DateTime,
        'updated_at' => new DateTime,
    ]);
        DB::table('area_instruments')->insert([
            'intended_program_id' => '1',
            'area_number' => '10',
            'area_name' => 'AREA X: ADMINISTRATION',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

    }
}
