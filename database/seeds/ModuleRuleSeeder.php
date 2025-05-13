<?php

use Illuminate\Database\Seeder;

use App\Models\module_rule;

class ModuleRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/module_rules.json');
        $data = json_decode(file_get_contents($json), true);
        module_rule::insert($data);
    }
}
