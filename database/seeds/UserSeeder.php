<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'first_name' => 'test',
            'last_name' => 'test',
            'email' => 'test@gmail.com',
            'password' => Hash::make('password'),
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        DB::table('users')->insert([
            'first_name' => 'Rob',
            'last_name' => 'del Rosario',
            'email' => 'rob@gmail.com',
            'password' => Hash::make('password'),
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        DB::table('users_roles')->insert([
            'user_id' => 2,
            'role_id' => 8
        ]);

        DB::table('users')->insert([
            'first_name' => 'Cristal',
            'last_name' => 'Senara',
            'email' => 'cristal@gmail.com',
            'password' => Hash::make('password'),
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        DB::table('users_roles')->insert([
            'user_id' => 3,
            'role_id' => 8
        ]);

        DB::table('users')->insert([
            'first_name' => 'Fred',
            'last_name' => 'Lagamon',
            'email' => 'fred@gmail.com',
            'password' => Hash::make('password'),
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        DB::table('users_roles')->insert([
            'user_id' => 4,
            'role_id' => 8
        ]);

        DB::table('users')->insert([
            'first_name' => 'Tal',
            'last_name' => 'Senara',
            'email' => 'tal@gmail.com',
            'password' => Hash::make('password'),
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        DB::table('users_roles')->insert([
            'user_id' => 5,
            'role_id' => 1
        ]);

        DB::table('campuses_users')->insert([
            'campus_id' => 1,
            'user_id' => 5,
        ]);

        DB::table('users')->insert([
            'first_name' => 'Alfred',
            'last_name' => 'Lagamon',
            'email' => 'alfred@gmail.com',
            'password' => Hash::make('password'),
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        DB::table('users_roles')->insert([
            'user_id' => 6,
            'role_id' => 2
        ]);

        DB::table('campuses_users')->insert([
            'campus_id' => 1,
            'user_id' => 6,
        ]);

        DB::table('users')->insert([
            'first_name' => 'Joy',
            'last_name' => 'Barbosa',
            'email' => 'joy@gmail.com',
            'password' => Hash::make('password'),
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        DB::table('users_roles')->insert([
            'user_id' => 7,
            'role_id' => 5
        ]);

        DB::table('campuses_users')->insert([
            'campus_id' => 1,
            'user_id' => 7,
        ]);

        DB::table('users')->insert([
            'first_name' => 'Joon',
            'last_name' => 'Quinito',
            'email' => 'joon@gmail.com',
            'password' => Hash::make('password'),
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        DB::table('users_roles')->insert([
            'user_id' => 8,
            'role_id' => 12
        ]);

        for($x=1; $x<=12; $x++){
            DB::table('users_roles')->insert([
                'user_id' => 1,
                'role_id' => $x
            ]);
        }
        DB::table('campuses_users')->insert([
            'campus_id' => 1,
            'user_id' => 1,
        ]);



    }
}
