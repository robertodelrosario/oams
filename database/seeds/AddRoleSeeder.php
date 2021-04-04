<?php

use Illuminate\Database\Seeder;

class AddRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'role' => 'accreditation task force head coordinator',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
    }
}
