<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequiredRatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('required_ratings')->insert([
            'accreditation_status' => 'Candidate',
            'grand_mean' => 2.50,
            'area_mean' => 2.00,
        ]);
        DB::table('required_ratings')->insert([
            'accreditation_status' => 'Accredited Level I',
            'grand_mean' => 3.00,
            'area_mean' => 2.50,
        ]);
        DB::table('required_ratings')->insert([
            'accreditation_status' => 'Accredited Level II',
            'grand_mean' => 3.50,
            'area_mean' => 2.00,
        ]);
        DB::table('required_ratings')->insert([
            'accreditation_status' => 'Accredited Level III',
            'grand_mean' => 4.00,
            'area_mean' => 2.00,
        ]);
        DB::table('required_ratings')->insert([
            'accreditation_status' => 'Accredited Level VI',
            'grand_mean' => 4.50,
            'area_mean' => 2.00,
        ]);
    }
}
