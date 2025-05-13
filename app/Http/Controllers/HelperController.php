<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

// Libraries
use App\Libraries\Helper;

class HelperController extends Controller
{
    public function filter_district(Request $request)
    {
        $validation = [
            'parent' => 'required'
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
        ];
        $names = [
            'parent' => ucwords(lang('parent', $this->translations))
        ];
        $validator = Validator::make($request->all(), $validation, $message, $names);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'false',
                'message' => implode(', ', $validator->errors()->all()),
                'data' => $validator->errors()->messages(),
            ]);
        }

        $parent = (int) $request->parent;

        $province = DB::table('id_provinces')->select('id')->where('code', $parent)->first();
        $province_id = (int) $province->id;

        $data = DB::table('id_cities')
            ->select(
                'full_code AS kode',
                'name AS nama'
            )
            ->where('provinsi_id', $province_id)
            ->orderBy('name')
            ->get();

        $response = [
            'status' => 'true',
            'message' => 'Successfully get data',
            'data' => $data
        ];
        return response()->json($response, 200);
    }

    public function filter_sub_district(Request $request)
    {
        $parent = (int) $request->parent;

        $city = DB::table('id_cities')->select('id')->where('full_code', $parent)->first();
        $city_id = (int) $city->id;

        $data = DB::table('id_sub_districts')
            ->select(
                'full_code AS kode',
                'name AS nama'
            )
            ->where('kabupaten_id', $city_id)
            ->orderBy('name')
            ->get();

        $response = [
            'status' => 'true',
            'message' => 'Successfully get data',
            'data' => $data
        ];
        return response()->json($response, 200);
    }

    public function filter_village(Request $request)
    {
        $parent = (int) $request->parent;

        $sub_district = DB::table('id_sub_districts')->select('id')->where('full_code', $parent)->first();
        $sub_district_id = (int) $sub_district->id;

        $data = DB::table('id_villages')
            ->select(
                'full_code AS kode',
                'name AS nama'
            )
            ->where('kecamatan_id', $sub_district_id)
            ->orderBy('name')
            ->get();

        $response = [
            'status' => 'true',
            'message' => 'Successfully get data',
            'data' => $data
        ];
        return response()->json($response, 200);
    }

    public function filter_postal_code(Request $request)
    {
        $parent = (int) $request->parent;

        $village = DB::table('id_villages')->select('id')->where('full_code', $parent)->first();
        $village_id = (int) $village->id;

        $data = [];

        // get postal codes from village
        $query = DB::table('id_villages')->find($village_id);

        if (!empty($query)) {
            // if postal codes from village are exist
            if (!empty($query->full_code)) {
                $array = explode(',', $query->pos_code);
                if (isset($array[0])) {
                    foreach ($array as $item) {
                        $obj = new \stdClass();
                        $obj->kode = $item;
                        $obj->nama = $item;
                        $data[] = $obj;
                    }
                }
            } else {
                // get postal codes from sub_district
                $query_sub_district = DB::table('id_sub_districts')
                    ->where('sub_district_code', $query->village_sub_district_code)
                    ->first();
                if (!empty($query_sub_district)) {
                    // if postal codes from sub_district are exist
                    if (!empty($query_sub_district->sub_district_postal_codes)) {
                        $array = explode(',', $query_sub_district->sub_district_postal_codes);
                        if (isset($array[0])) {
                            foreach ($array as $item) {
                                $obj = new \stdClass();
                                $obj->kode = $item;
                                $obj->nama = $item;
                                $data[] = $obj;
                            }
                        }
                    } else {
                        // get postal codes from city
                        $query_city = DB::table('id_cities')
                            ->where('city_code', $query_sub_district->sub_district_city_code)
                            ->first();
                        if (!empty($query_city)) {
                            // if postal codes from city are exist
                            if (!empty($query_city->city_postal_codes)) {
                                $array = explode(',', $query_city->city_postal_codes);
                                if (isset($array[0])) {
                                    foreach ($array as $item) {
                                        $obj = new \stdClass();
                                        $obj->kode = $item;
                                        $obj->nama = $item;
                                        $data[] = $obj;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $response = [
            'status' => 'true',
            'message' => 'Successfully get data',
            'data' => $data
        ];
        return response()->json($response, 200);
    }
}
