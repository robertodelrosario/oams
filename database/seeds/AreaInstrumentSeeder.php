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
        DB::table('area_instruments')->insert([
            'intended_program' => 'IT',
            'area_number' => '4',
            'area_name' => 'area 4',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program' => 'IT',
            'area_number' => '5',
            'area_name' => 'area 5',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program' => 'IT',
            'area_number' => '6',
            'area_name' => 'area 6',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program' => 'IT',
            'area_number' => '7',
            'area_name' => 'area 7',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('area_instruments')->insert([
            'intended_program' => 'IT',
            'area_number' => '8',
            'area_name' => 'area 8',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);DB::table('area_instruments')->insert([
        'intended_program' => 'IT',
        'area_number' => '9',
        'area_name' => 'area 9',
        'version' => '1',
        'created_at' => new DateTime,
        'updated_at' => new DateTime,
    ]);
        DB::table('area_instruments')->insert([
            'intended_program' => 'IT',
            'area_number' => '10',
            'area_name' => 'area 10',
            'version' => '1',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

    }
}
