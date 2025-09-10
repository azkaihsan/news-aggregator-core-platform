<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$path = 'database/seeders/countries.sql';
        $sql = file_get_contents($path);
        DB::unprepared($sql);
    }
}
