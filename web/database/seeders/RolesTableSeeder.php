<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('roles')->delete();
        
        \DB::table('roles')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'administrator',
                'display_name' => 'Administrator',
                'description' => 'Administrator role',
                'created_at' => '2020-03-25 10:56:42',
                'updated_at' => '2020-03-25 10:56:45',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Admin role',
                'created_at' => '2020-03-25 10:56:53',
                'updated_at' => '2020-03-25 10:56:53',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'staff',
                'display_name' => 'Staff',
                'description' => 'Staff role',
                'created_at' => '2020-03-25 10:57:16',
                'updated_at' => '2020-03-25 10:57:16',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'guest',
                'display_name' => 'Guest',
                'description' => 'Guest role',
                'created_at' => '2020-03-25 10:57:52',
                'updated_at' => '2020-03-25 10:57:52',
            ),
        ));
        
        
    }
}