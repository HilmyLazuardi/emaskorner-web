<?php

use Illuminate\Database\Seeder;

use App\Models\country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/countries.json');
        $data = json_decode(file_get_contents($json), true);
        country::insert($data);
    }
}
