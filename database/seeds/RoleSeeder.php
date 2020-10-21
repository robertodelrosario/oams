<?php

use Illuminate\Database\Seeder;

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
            'role' => 'support',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        DB::table('roles')->insert([
            'role' => 'QA',
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
            'role' => 'internal accreditor head',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        DB::table('roles')->insert([
            'role' => 'external accreditor head',
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
