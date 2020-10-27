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
        DB::table('area_instruments')->insert([
            'intended_program' => 'IT',
            'area_number' => '1',
            'area_name' => 'area 1',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program' => 'IT',
            'area_number' => '2',
            'area_name' => 'area 2',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program' => 'IT',
            'area_number' => '3',
            'area_name' => 'area 3',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
    }
}
