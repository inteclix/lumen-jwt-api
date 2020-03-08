<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'username' => 'admin',
            'firstname' => 'admin',
            'lastname' => 'admin',
            'password' => 'admin',
            'tel' => '0540055010',
            'role' => 'admin',
            'is_active' => true,
            'profile' => "",
        ]);

    }
}
