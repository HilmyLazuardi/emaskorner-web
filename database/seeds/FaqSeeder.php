<?php

use Illuminate\Database\Seeder;

use App\Models\faq;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/faq.json');
        $data = json_decode(file_get_contents($json), true);
        faq::insert($data);
    }
}
