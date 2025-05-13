<?php

use Illuminate\Database\Seeder;

use App\Models\district;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/kabupaten.json');
        $data = json_decode(file_get_contents($json), true);
        district::insert($data);
    }
}
