<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('settings')->delete();
        
        \DB::table('settings')->insert(array (
            0 => 
            array (
                'id' => 1,
                'start_time' => '07:00:00',
                'out_time' => '09:00:00',
                'key_app' => '3k3u2oW2zX13xyPJiyBQwSE2QyFRvF0Cf2FbovqG',
                'timezone' => 'Asia/Makassar',
                'created_at' => '2021-04-08 20:48:26',
                'updated_at' => '2021-04-11 23:06:52',
            ),
        ));
        
        
    }
}