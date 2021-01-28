<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'role' => 'accreditation task force',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('roles')->insert([
            'role' => 'accreditation task force head',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('roles')->insert([
            'role' => 'support head',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('roles')->insert([
            'role' => 'support staff',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('roles')->insert([
            'role' => 'QA director',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('roles')->insert([
            'role' => 'QA staff',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('roles')->insert([
            'role' => 'internal accreditor',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('roles')->insert([
            'role' => 'external accreditor',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('roles')->insert([
            'role' => 'aaccup staff',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('roles')->insert([
            'role' => 'aaccup boardmember',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
    }
}
