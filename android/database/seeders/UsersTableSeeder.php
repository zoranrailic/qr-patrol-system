<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'administrator',
                'email' => 'administrator@gmail.com',
                'email_verified_at' => NULL,
                'password' => '$2y$10$ObHRjbnl3Tmuc/fCuDOVXe8z0KAQW7Kx0dQ/xQyhY3Br2pLG2jgDS',
                'remember_token' => 'SfwOGvEak2onUtMejQVG0dtbYqK7jYl4FakE2R7TOXtSBZK3e9ezIQXZt7GY',
                'image' => 'default-user.png',
                'role' => 1,
                'created_at' => '2020-03-25 00:37:36',
                'updated_at' => '2024-06-04 15:46:19',
            ),
        ));
        
        
    }
}