<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            SUCSeeder::class,
            CampusSeeder::class,
            ProgramSeeder::class,
            AreaInstrumentSeeder::class,
            RoleSeeder::class,
        ]);
    }
}
