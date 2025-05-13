<?php

use Illuminate\Database\Seeder;

use App\Models\product_default_variant;

class ProductDefaultVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/product_default_variant.json');
        $data = json_decode(file_get_contents($json), true);
        product_default_variant::insert($data);
    }
}