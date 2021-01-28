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
            'contact_no' => '09123456789',
            'status' => 'active',
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        for($x=1; $x<=10; $x++){
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
