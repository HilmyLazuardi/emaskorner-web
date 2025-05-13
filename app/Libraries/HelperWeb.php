<?php

namespace App\Libraries;

// Models
use App\Models\nav_menu;
use App\Models\office;
use App\Models\social_media;

class HelperWeb
{
    public static function get_nav_menu()
    {
        $data = nav_menu::where('status', 1)
            ->orderBy('position')
            ->orderBy('level')
            ->orderBy('parent_id')
            ->orderBy('ordinal')
            ->get();

        if (!isset($data[0])) {
            // NO DATA
            return;
        }

        $array_object = $data;
        $params_child = [
            'id',
            'name',
            'link_type',
            'link_external',
            'link_internal',
            'link_target',
            'level',
            'parent_id',
            'position'
        ];
        $parent = 'position';
        $data_per_position = Helper::generate_parent_child_data_array($array_object, $parent, $params_child);
        // dd($data_per_position);

        $navigation_menus = [];
        if (!empty($data_per_position)) {
            foreach ($data_per_position as $position => $menu_in_position) {
                $parent = 'level';
                $data_per_level = Helper::generate_parent_child_data_array($menu_in_position, $parent, $params_child);

                $arr = [];
                foreach ($data_per_level as $level => $menulist) {
                    foreach ($menulist as $menu) {
                        // level_id : lvl1_id2
                        $var_name = 'lvl' . $level . '_id' . $menu['id'];

                        $parent_level = $menu['level'] - 1;
                        $var_name_parent = 'lvl' . $parent_level . '_id' . $menu['parent_id'];

                        // convert array to object
                        $obj = new \stdClass();
                        foreach ($menu as $key => $value) {
                            $obj->$key = $value;
                        }
                        // dd($menu, $obj);

                        if (isset($arr[$var_name_parent])) {
                            $var_name_sub = 'level_' . $menu['level'];
                            $arr[$var_name_parent]->$var_name_sub[] = $obj;
                        }
                        $arr[$var_name] = $obj;
                    }
                }
                // dd($arr);

                $navigation_menu = [];
                foreach ($arr as $key => $value) {
                    if (Helper::is_contains('lvl1', $key)) {
                        $navigation_menu[] = $value;
                    }
                }
                // dd($navigation_menu);

                $navigation_menus[$position] = $navigation_menu;
            }
            // dd($navigation_menus);
        }

        return $navigation_menus;
    }

    public static function get_company_info()
    {
        $data = office::where('status', 1)->orderBy('ordinal')->first();
        return $data;
    }

    public static function get_social_media()
    {
        $data = social_media::where('status', 1)->orderBy('ordinal')->get();
        return $data;
    }
}