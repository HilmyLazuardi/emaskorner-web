<?php

use Illuminate\Database\Seeder;

use App\Models\province;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/provinsi.json');
        $data = json_decode(file_get_contents($json), true);
        province::insert($data);
    }
}
