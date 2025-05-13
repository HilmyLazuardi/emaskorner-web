<?php

use Illuminate\Database\Seeder;

use App\Models\sub_district;

class SubDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/kecamatan.json');
        $data = json_decode(file_get_contents($json), true);
        sub_district::insert($data);
    }
}
