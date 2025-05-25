<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

// LIBRARIES
use App\Libraries\Helper;
use App\Libraries\HelperWeb;

// MODELS
use App\Models\buyer;
use App\Models\buyer_address;
use App\Models\buyer_phone;
use App\Models\buyer_provider;
use App\Models\province;
use App\Models\district;
use App\Models\sub_district;
use App\Models\village;
use App\Models\wishlist;
use App\Models\product_item_variant;

class BuyerController extends Controller
{
    /**
     * GET PROFILE
     */
    public function profile(Request $request)
    {
        $data               = Session::get('buyer');
        $navigation_menu    = HelperWeb::get_nav_menu();
        $buyer_provider     = buyer_provider::where('user_id', Session::get('buyer')->id)->get();

        return view('web.buyer.profile', compact('data', 'navigation_menu', 'buyer_provider'));
    }

    /**
     * PROCESSING UPDATE PROFILE
     */
    public function profile_update(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.home');
        }

        $validation = [
            'fullname'      => 'required',
            'phone_number'  => 'required|numeric|digits_between:10,14',
            'gender'        => 'required',
            // 'email'         => 'required|email',
            // 'birth_date'    => 'required',
            'birth_day'     => 'required|numeric',
            'birth_month'   => 'required|numeric',
            'birth_year'    => 'required|numeric'
        ];

        if ($request->image) {
            $validation['photo'] = 'required|image|max:2048';
        }

        $message    = [
            // 'fullname.required' => 'Isi nama sesuai KTP',
            'required'          => ':attribute wajib diisi',
            'numeric'           => ':attribute harus menggunakan angka',
            'digits_between'    => ':attribute min. 10 digit',
            // 'email'             => 'Harus menggunakan format username@domain.com',
            // 'date_format'       => 'Harus menggunakan format dd/mm/YYYY',
            'image'             => ':attribute harus menggunakan gambar',
            'max'               => ':attribute maksimal ukuran gambar adalah 2MB',
        ];

        $names      = [
            'fullname'      => 'Nama Lengkap',
            'phone_number'  => 'Nomor Telepon',
            // 'email'         => 'Email',
            // 'birth_date'    => 'Tanggal lahir',
            'birth_day'     => 'Tanggal Lahir',
            'birth_month'   => 'Tanggal Lahir',
            'birth_year'    => 'Tanggal Lahir',
            'photo'         => 'Foto',
            'gender'        => 'Jenis Kelamin'
        ];

        $this->validate($request, $validation, $message, $names);

        // VALIDATE FROM SQL INJECTION
        $fullname = Helper::validate_input_text($request->fullname);
        if (!$fullname) {
            return back()->withInput()->with('error_fullname', 'Isi nama sesuai KTP');
        }

        $phone_number = Helper::validate_phone($request->phone_number);
        if (!$phone_number['status']) {
            return back()->withInput()->with('error_phone_number', $phone_number['message']);
        }

        $phone_numbers   = $this->validate_phone($phone_number['data']);

        // CHECK PHONE NUMBER TO DATABASE
        $check_phone_number = buyer::where('phone_number', $phone_numbers)->where('id', '!=', Session::get('buyer')->id)->first();
        if (!empty($check_phone_number)) {
            return back()->withInput()->with('error_phone_number', 'Nomor telepon sudah dipakai, silahkan coba dengan nomer lain');
        }

        // GENDER
        $gender = Helper::validate_input_text($request->gender);
        $allowed_gender = ['laki-laki', 'perempuan'];
        if (!in_array($gender, $allowed_gender)) {
            return back()->withInput()->with('error_gender', 'Jenis kelamin tidak valid, silahkan muat ulang halaman Anda dan coba kembali');
        }

        // $birth_date = Helper::validate_input_text($request->birth_date);
        // if (!$birth_date) {
        //     return back()->withInput()->with('error_birth_date', 'Tanggal lahir tidak valid');
        // }

        if (!$request->birth_day || !$request->birth_month || !$request->birth_year) {
            return back()->withInput()->with('error_birth_date', 'Tanggal lahir harus diisi');
        }

        $birth_day      = (int) $request->birth_day;
        $birth_month    = (int) $request->birth_month;
        $birth_year     = (int) $request->birth_year;

        if ($birth_year < 1900 || $birth_year > date('Y')) {
            return back()->withInput()->with('error_birth_date', 'Tanggal lahir tidak valid');
        }

        if (!checkdate($birth_month, $birth_day, $birth_year)) {
            return back()->withInput()->with('error_birth_date', 'Tanggal lahir tidak valid');
        }

        // CHECK BUYER DATA BY ID
        $buyer = buyer::where('id', Session::get('buyer')->id)->first();
        if (empty($buyer)) {
            Session::forget('buyer');
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login untuk melanjutkan.');
        }

        // CHECK PHONE NUMBER HISTORY
        $check_phone_number_history = buyer_phone::where('value', $phone_numbers)->where('user_id', Session::get('buyer')->id)->first();
        if (!empty($check_phone_number_history)) {
            return back()->withInput()->with('error_phone_number', 'Nomor telepon pernah terpakai, silahkan coba dengan nomer lain');
        }

        // LAST PHONE NUMBER BUYER
        $last_phone_number = $buyer->phone_number;

        if ($request->file('photo')) {
            // UPLOAD IMAGE
            $dir_path           = 'uploads/buyer_photo_profile/';
            $image_file         = $request->file('photo');
            $format_image_name  = Helper::unique_string();
            $allowed_extensions = ['jpeg', 'jpg', 'png'];
            $generate_thumbnail = true;
            $thumbnail_width    = 200;
            $thumbnail_height   = 200;
            $thumbnail_quality_percentage = 80;
            $image              = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions, $generate_thumbnail, $thumbnail_width, $thumbnail_height, $thumbnail_quality_percentage);

            if ($image['status'] != 'true') {
                # FAILED TO UPLOAD IMAGE
                return back()
                    ->withInput()
                    ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
            }

            $buyer->avatar = $dir_path . $image['data'];
            // $data->image_thumb = $dir_path . $image['thumbnail'];
        }

        $buyer->fullname        = $fullname;
        $buyer->phone_number    = $phone_numbers;
        $buyer->birth_date      = $birth_year . '-' . $birth_month . '-' . $birth_day;
        $buyer->gender          = $gender;

        // SAVE THE DATA
        if ($buyer->save()) {
            // SUCCESS - PUT NEW DATA TO SESSION
            unset($buyer->password);
            unset($buyer->token);
            unset($buyer->updated_at);
            unset($buyer->deleted_at);

            // SET SESSION
            Session::put('buyer', $buyer);

            // INSERT HISTORY BANNER
            if ($last_phone_number != $phone_numbers) {
                $buyer_phone = new buyer_phone();
                $buyer_phone->user_id = $buyer->id;
                $buyer_phone->value = $last_phone_number;
                $buyer_phone->save();
            }

            if (Session::has('from_page')) {
                // SAVE IT TO VARIABLE
                $redirect_to_page = Session::get('from_page');

                // THEN FORGET SESSION
                Session::forget('from_page');

                // AND THE LAST IS REDIRECT TO THIS PAGE
                return redirect($redirect_to_page);
            } else {
                return redirect()->route('web.buyer.profile')->with([
                    'success' => 'Profil kamu sudah diperbarui.',
                    'header' => '<b>Berhasil!</b>'
                ]);
            }
        }

        // FAILED
        return redirect()->route('web.buyer.profile')->with('error_avatar', 'Oops, terjadi kesalahan, silahkan coba lagi.');
    }

    /**
     * LIST ADDRESS
     */
    public function list_address()
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login untuk melanjutkan');
        }

        $navigation_menu    = HelperWeb::get_nav_menu();

        $buyer_id   = (int) Session::get('buyer')->id;
        $data       = buyer_address::select(
            'buyer_address.*',
            'id_provinces.name as province_name',
            'id_cities.name as city_name',
            'id_sub_districts.name as sub_district_name',
            'id_villages.name as village_name'
        )
            ->leftJoin('id_provinces', 'buyer_address.province_code', 'id_provinces.code')
            ->leftJoin('id_cities', 'buyer_address.district_code', 'id_cities.full_code')
            ->leftJoin('id_sub_districts', 'buyer_address.sub_district_code', 'id_sub_districts.full_code')
            ->leftJoin('id_villages', 'buyer_address.village_code', 'id_villages.full_code')
            ->where('buyer_address.user_id', $buyer_id)
            ->get();

        if (isset($data[0])) {
            foreach ($data as $item) {
                $item->raw_id = Helper::generate_token($item->id);
            }
        }

        return view('web.buyer.address', compact('data', 'navigation_menu'));
    }

    /**
     * ADD NEW ADDRESS
     */
    public function add_address(Request $request)
    {
        // IF HAVEN'T ALREADY LOGED IN
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login untuk melanjutkan');
        }

        $navigation_menu    = HelperWeb::get_nav_menu();

        if ($request->from && $request->from == 'process-order') {
            Session::put('from_page', route('web.order.process'));
        } else {
        }

        $provinces = province::orderBy('name')->get();

        return view('web.buyer.address_form', compact('provinces', 'navigation_menu'));
    }

    /**
     * PROCESS NEW ADDRESS
     */
    public function store_address(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.home');
        }

        $validation = [
            'fullname'          => 'required',
            'phone_number'      => 'required|numeric|digits_between:10,14',
            'name'              => 'required',
            'province'          => 'required|integer',
            'district'          => 'required|integer',
            'sub_district'      => 'required|integer',
            'village'           => 'required|integer',
            'postal_code'       => 'required|integer',
            'address_details'   => 'required|string|min:10|max:255'
        ];

        $message    = [
            'required'          => ':attribute wajib diisi',
            'numeric'           => ':attribute harus menggunakan angka',
            'integer'           => 'Harus menggunakan angka',
            'string'            => 'Harus berupa text',
            'min'               => 'Minimal 10 karakter',
            'max'               => 'Maksimal 255 karakter'
        ];

        $names      = [
            'fullname'          => 'Nama Lengkap',
            'phone_number'      => 'Nomor Telepon',
            'name'              => 'Label alamat',
            'province'          => 'Provinsi',
            'district'          => 'Kota/Kabupaten',
            'sub_district'      => 'Kecamatan',
            'village'           => 'Desa/Kelurahan',
            'postal_code'       => 'Kode Pos',
            'address_details'   => 'Detail alamat'
        ];

        $this->validate($request, $validation, $message, $names);

        // VALIDATE FROM SQL INJECTION
        $fullname = Helper::validate_input_text($request->fullname);
        if (!$fullname) {
            return back()->withInput()->with('error_fullname', 'Isi nama sesuai KTP');
        }

        $phone_number = Helper::validate_phone($request->phone_number);
        if (!$phone_number['status']) {
            return back()->withInput()->with('error_phone_number', $phone_number['message']);
        }

        $phone_numbers   = $this->validate_phone($phone_number['data']);

        // CHECK PHONE NUMBER TO DATABASE
        $check_phone_number = buyer::where('phone_number', $phone_number)->where('id', '!=', Session::get('buyer')->id)->first();
        if (!empty($check_phone_number)) {
            return back()->withInput()->with('error_phone_number', 'Nomor telepon sudah dipakai, silahkan coba dengan nomer lain');
        }


        // LABEL ALAMAT
        $name = Helper::validate_input_text($request->name);
        if (!$name) {
            return back()->withInput()->with('error_name', 'Label Alamat tidak valid');
        }

        // PROVINCE
        $province = (int) $request->province;
        $check_province = province::where('code', $province)->first();
        if (empty($check_province)) {
            return back()->withInput()->with('error_province', 'Provinsi tidak valid');
        }

        // DISTRICT
        $district = (int) $request->district;
        $check_district = district::where('provinsi_id', $check_province->id)->where('full_code', $district)->first();
        if (empty($check_district)) {
            return back()->withInput()->with('error_district', 'Kota/Kabupaten tidak valid');
        }

        // SUB DISTRICT
        $sub_district = (int) $request->sub_district;
        $check_sub_district = sub_district::where('kabupaten_id', $check_district->id)->where('full_code', $sub_district)->first();
        if (empty($check_sub_district)) {
            return back()->withInput()->with('error_sub_district', 'Kecamatan tidak valid');
        }

        // VILLAGE
        $village = (int) $request->village;
        $check_village = village::where('kecamatan_id', $check_sub_district->id)->where('full_code', $village)->first();
        if (empty($check_village)) {
            return back()->withInput()->with('error_village', 'Desa/Kelurahan tidak valid');
        }

        // POSTAL CODE
        if ($request->postal_code) {
            $input_postal_code = $request->postal_code;

            // CHECK ALLOWED POSTAL CODE
            if (isset($check_village->pos_code)) {
                $allowed_postal_code = explode(',', $check_village->pos_code);
                if (in_array($input_postal_code, $allowed_postal_code)) {
                    $postal_code = $input_postal_code;
                } else {
                    $postal_code = 0;
                }
            } else {
                $postal_code = 0;
            }
        } else {
            $postal_code = 0;
        }

        $address_details = Helper::validate_input_text($request->address_details);
        if (!$address_details) {
            return back()->withInput()->with('error_address_details', 'Detail Alamat tidak valid');
        }

        if (strlen($address_details) < 10 && strlen($address_details) > 255) {
            return back()->withInput()->with('error_address_details', 'Detail Alamat minimal 10 karakter dan maksimal 255 karakter');
        }

        $remarks = Helper::validate_input_text($request->remarks);

        $buyer = Session::get('buyer');

        $buyer_address                      = new buyer_address();
        $buyer_address->user_id             = (int) $buyer->id;
        $buyer_address->fullname            = $fullname;
        $buyer_address->phone_number        = $phone_numbers;
        $buyer_address->name                = $name;
        $buyer_address->province_code       = $province;
        $buyer_address->district_code       = $district;
        $buyer_address->sub_district_code   = $sub_district;
        $buyer_address->village_code        = $village;
        $buyer_address->postal_code         = $postal_code;
        $buyer_address->address_details     = $address_details;
        $buyer_address->remarks             = $remarks;

        // jika belum ada alamat sebelumnya, maka alamat ini menjadi alamat utama
        $check_input_address = buyer_address::where('user_id', $buyer->id)->count();
        if ($check_input_address == 0) {
            $buyer_address->is_default = 1;
        }

        // SAVE THE DATA
        if ($buyer_address->save()) {
            if (Session::has('from_page')) {
                // SAVE IT TO VARIABLE
                $redirect_to_page = Session::get('from_page');

                // THEN FORGET SESSION
                Session::forget('from_page');

                // AND THE LAST IS REDIRECT TO THIS PAGE
                header("Location: " . $redirect_to_page);
                die();
            } elseif ($request->from == 'process-order') {
                return redirect()->route('web.order.process', 'process-order');
            } else {
                return redirect()->route('web.buyer.list_address')->with([
                    'success' => 'Alamat baru berhasil ditambahkan.',
                    'header' => '<b>Hore!</b>'
                ]);
            }
        }

        // FAILED
        return redirect()->route('web.buyer.list_address')->with('error_name', 'Oops, terjadi kesalahan, silahkan coba lagi.');
    }

    /**
     * EDIT ADDRESS
     */
    public function edit_address($id)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login untuk melanjutkan');
        }

        $raw_id     = $id;
        $address_id = Helper::validate_token($id);
        $buyer_id   = (int) Session::get('buyer')->id;
        $provinces  = province::orderBy('name')->get();
        $data       = buyer_address::select(
            'buyer_address.*',
            'id_provinces.name as province_name',
            'id_cities.name as city_name',
            'id_sub_districts.name as sub_district_name',
            'id_villages.name as village_name'
        )
            ->leftJoin('id_provinces', 'buyer_address.province_code', 'id_provinces.code')
            ->leftJoin('id_cities', 'buyer_address.district_code', 'id_cities.full_code')
            ->leftJoin('id_sub_districts', 'buyer_address.sub_district_code', 'id_sub_districts.full_code')
            ->leftJoin('id_villages', 'buyer_address.village_code', 'id_villages.full_code')
            ->where('buyer_address.user_id', $buyer_id)
            ->where('buyer_address.id', $address_id)
            ->first();

        if (empty($data)) {
            return redirect()->route('web.buyer.list_address')->with('error', 'Link bermasalah');
        }

        return view('web.buyer.address_form', compact('provinces', 'data', 'raw_id'));
    }

    /**
     * PROCESS UPDATE ADDRESS
     */
    public function update_address(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.home');
        }

        $validation = [
            'id'                => 'required',
            'fullname'          => 'required',
            'phone_number'      => 'required|numeric|digits_between:10,14',
            'name'              => 'required',
            'province'          => 'required|integer',
            'district'          => 'required|integer',
            'sub_district'      => 'required|integer',
            'village'           => 'required|integer',
            'postal_code'       => 'required|integer',
            'address_details'   => 'required'
        ];

        $message    = [
            'required'          => ':attribute ' . 'tidak boleh kosong',
            'numeric'           => ':attribute harus menggunakan angka',
            'integer'           => ':attribute ' . 'harus menggunakan angka'
        ];

        $names      = [
            'id'                => 'ID',
            'fullname'          => 'Nama Lengkap',
            'phone_number'      => 'Nomor Telepon',
            'name'              => 'Label alamat',
            'province'          => 'Provinsi',
            'district'          => 'Kota/Kabupaten',
            'sub_district'      => 'Kecamatan',
            'village'           => 'Desa/Kelurahan',
            'postal_code'       => 'Kode Pos',
            'address_details'   => 'Detail alamat'
        ];

        $this->validate($request, $validation, $message, $names);

        // CHECK ADDRESS
        $buyer_id   = (int) Session::get('buyer')->id;
        $raw_id     = $request->id;
        $address_id = Helper::validate_token($request->id);

        $data       = buyer_address::where('id', $address_id)->where('user_id', $buyer_id)->first();
        if (empty($data)) {
            return redirect()->route('web.buyer.list_address')->with('error', 'Link bermasalah');
        }

        // VALIDATE FROM SQL INJECTION
        $fullname = Helper::validate_input_text($request->fullname);
        if (!$fullname) {
            return back()->withInput()->with('error_fullname', 'Isi nama sesuai KTP');
        }

        $phone_number = Helper::validate_phone($request->phone_number);
        if (!$phone_number['status']) {
            return back()->withInput()->with('error_phone_number', $phone_number['message']);
        }

        $phone_numbers   = $this->validate_phone($phone_number['data']);

        // CHECK PHONE NUMBER TO DATABASE
        $check_phone_number = buyer::where('phone_number', $phone_number)->where('id', '!=', Session::get('buyer')->id)->first();
        if (!empty($check_phone_number)) {
            return back()->withInput()->with('error_phone_number', 'Nomor telepon sudah dipakai, silahkan coba dengan nomer lain');
        }


        // LABEL ALAMAT
        $name = Helper::validate_input_text($request->name);
        if (!$name) {
            return back()->withInput()->with('error_name', 'Label alamat tidak valid');
        }

        // PROVINCE
        $province = (int) $request->province;
        $check_province = province::where('code', $province)->first();
        if (empty($check_province)) {
            return back()->withInput()->with('error_province', 'Provinsi tidak valid');
        }

        // DISTRICT
        $district = (int) $request->district;
        $check_district = district::where('provinsi_id', $check_province->id)->where('full_code', $district)->first();
        if (empty($check_district)) {
            return back()->withInput()->with('error_district', 'Kota/Kabupaten tidak valid');
        }

        // SUB DISTRICT
        $sub_district = (int) $request->sub_district;
        $check_sub_district = sub_district::where('kabupaten_id', $check_district->id)->where('full_code', $sub_district)->first();
        if (empty($check_sub_district)) {
            return back()->withInput()->with('error_sub_district', 'Kecamatan tidak valid');
        }

        // VILLAGE
        $village = (int) $request->village;
        $check_village = village::where('kecamatan_id', $check_sub_district->id)->where('full_code', $village)->first();
        if (empty($check_village)) {
            return back()->withInput()->with('error_village', 'Desa/Kelurahan tidak valid');
        }

        // POSTAL CODE
        if ($request->postal_code) {
            $input_postal_code = $request->postal_code;

            // CHECK ALLOWED POSTAL CODE
            if (isset($check_village->pos_code)) {
                $allowed_postal_code = explode(',', $check_village->pos_code);
                if (in_array($input_postal_code, $allowed_postal_code)) {
                    $postal_code = $input_postal_code;
                } else {
                    $postal_code = 0;
                }
            } else {
                $postal_code = 0;
            }
        } else {
            $postal_code = 0;
        }

        $address_details = Helper::validate_input_text($request->address_details);
        if (!$address_details) {
            return back()->withInput()->with('error_address_details', 'Detail alamat tidak valid');
        }

        $remarks = Helper::validate_input_text($request->remarks);

        $data->fullname            = $fullname;
        $data->phone_number        = $phone_numbers;
        $data->name                = $name;
        $data->province_code       = $province;
        $data->district_code       = $district;
        $data->sub_district_code   = $sub_district;
        $data->village_code        = $village;
        $data->postal_code         = $postal_code;
        $data->address_details     = $address_details;
        $data->remarks = $remarks;

        // SAVE THE DATA
        if ($data->save()) {
            return redirect()->route('web.buyer.list_address')->with('success_address', 'Alamat berhasil diubah.');
        }

        // FAILED
        return redirect()->route('web.buyer.list_address')->with('error', 'Oops, terjadi kesalahan, silahkan coba lagi.');
    }

    private function validate_phone($phone)
    {
        $first_letter = substr($phone, 0, 1);
        $two_letter = substr($phone, 0, 2);
        if ($first_letter == "0") {
            $remove_first_letter = "0" . substr($phone, 1);
            return $remove_first_letter;
        } elseif ($two_letter == "62") {
            $remove_two_letter = "0" . substr($phone, 2);
            return $remove_two_letter;
        } else {
            $add_zero = "0" . $phone;
            return $add_zero;
        }
    }

    public function wishlist()
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login untuk melanjutkan');
        }

        $data               = Session::get('buyer');
        $navigation_menu    = HelperWeb::get_nav_menu();
        $count_wishlist     = wishlist::select(
            'product_item.id',
            'product_item.name',
            'product_item.slug',
            'product_item.image',
            'product_item.global_stock',
            'product_item.qty',

            'product_item_variant.id',
            'product_item_variant.name as variant_name',
            'product_item_variant.slug',
            'product_item_variant.variant_image',
            'product_item_variant.price',
            'product_item_variant.qty as variant_qty',
            'product_item_variant.price as variant_price',

            'seller.store_name as seller_name',
        )
            ->leftJoin('product_item_variant', function ($join) {
                $join->on('wishlist.product_item_variant_id', '=', 'product_item_variant.id')
                    ->where('product_item_variant.status', 1)
                    ->whereNull('product_item_variant.deleted_at');
            })
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')

            ->where('wishlist.buyer_id', $data->id)
            ->where('product_item.published_status', 1)
            ->where('product_item.approval_status', 1)
            ->whereNull('product_item.deleted_at')

            ->orderBy('wishlist.created_at', 'desc')
            ->count();

        return view('web.buyer.wishlist', compact('data', 'navigation_menu', 'count_wishlist'));
    }

    public function wishlist_ajax(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login untuk melanjutkan');
        }

        $data       = Session::get('buyer');
        $perpage    = 12;
        $page       = 1;

        if ((int) $request->page) {
            $page = (int) $request->page;
        }

        $skipdata   = ($page - 1) * $perpage;

        // SORT
        $sort               = 'newest';

        if ($request->sort) {
            $allowed_sorting    = ['newest', 'oldest', 'highest_price', 'lowest_price'];
            if (!in_array($request->sort, $allowed_sorting)) {
                $response = [
                    'status'        => 'failed',
                    'message'       => 'Invalid filter, please try again'
                ];

                return response()->json($response, 200);
            }
            $sort = $request->sort;
        }

        $wishlist       = wishlist::select(
            'wishlist.id as wishlist_id',

            'product_item.id',
            'product_item.name',
            'product_item.image',
            'product_item.global_stock',
            'product_item.qty',
            // 'product_item.campaign_start',
            // 'product_item.campaign_end',

            'product_item_variant.id as variant_id',
            'product_item_variant.name as variant_name',
            'product_item_variant.slug',
            'product_item_variant.variant_image',
            'product_item_variant.qty as variant_qty',
            'product_item_variant.price as variant_price',

            'seller.store_name as seller_name',
        )

            ->leftJoin('product_item_variant', function ($join) {
                $join->on('wishlist.product_item_variant_id', '=', 'product_item_variant.id')
                    ->where('product_item_variant.status', 1)
                    ->whereNull('product_item_variant.deleted_at');
            })
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')

            ->where('wishlist.buyer_id', $data->id)

            ->where('product_item.published_status', 1)
            ->where('product_item.approval_status', 1)
            ->whereNull('product_item.deleted_at');

        switch ($sort) {
            case 'oldest':
                $wishlist->orderBy('wishlist.created_at', 'asc');
                break;

            case 'highest_price':
                $wishlist->orderBy('product_item_variant.price', 'desc');
                break;

            case 'lowest_price':
                $wishlist->orderBy('product_item_variant.price', 'asc');
                break;

            default:
                $wishlist->orderBy('wishlist.created_at', 'desc');
                break;
        }

        $count_product  = $wishlist->count();
        $data_wishlist  = $wishlist->take($perpage)->skip($skipdata)->get();

        // GENERATE DATA HTML CODE
        $html       = '';
        $pagination = '';
        if (isset($data_wishlist[0])) {
            foreach ($data_wishlist as $item) {
                // ENCRIPT WISHLIST ID
                $object_id = $item->wishlist_id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($item->wishlist_id);
                }

                $flag_id = date('Ymd') . $item->wishlist_id . date('His');

                // ENCRIPT VARIANT ID
                $variant_id = $item->variant_id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $variant_id = Helper::generate_token($item->variant_id);
                }

                // Jika global stock, ambil dari product.qty
                if ($item->global_stock) {
                    // Ribbon sold out dan campaign end
                    if ($item->qty < 1) {
                        $item->flag_soldout         = true;
                        $item->flag_campaign_end    = false;
                    // } else if ($item->qty > 0 && (date('Y-m-d H:i:s') > $item->campaign_end)) {
                    //     $item->flag_soldout         = false;
                    //     $item->flag_campaign_end    = true;
                    } else {
                        $item->flag_soldout         = false;
                        $item->flag_campaign_end    = false;
                    }
                } else { // Jika bukan global stock, ambil total stock seluruh variant
                    // 0 sebagai default value, jadi tidak menghitung data null
                    $variant_qty = 0;
                    if ($item->variant_qty > 0) {
                        $variant_qty = $item->variant_qty;
                    }

                    // Ribbon sold out dan campaign end
                    if ($variant_qty < 1) {
                        $item->flag_soldout         = true;
                        $item->flag_campaign_end    = false;
                    // } else if ($variant_qty > 0 && (date('Y-m-d H:i:s') > $item->campaign_end)) {
                    //     $item->flag_soldout         = false;
                    //     $item->flag_campaign_end    = true;
                    } else {
                        $item->flag_soldout         = false;
                        $item->flag_campaign_end    = false;
                    }
                }

                // SET IMAGE
                $item->set_image = $item->image;
                if (!is_null($item->variant_image)) {
                    $item->set_image = $item->variant_image;
                }

                // SET PRICE
                if (isset($item->variant_price)) {
                    $harga = 'Rp' . number_format($item->variant_price, 0, ',', '.');
                }

                // SET NAME
                $item->set_name = $item->name . ' - ' . $item->variant_name;

                if ($item->flag_soldout) {
                    $html .=    '<div class="product_list_box wishlist soldout">';
                } else if ($item->flag_campaign_end) {
                    $html .=    '<div class="product_list_box wishlist finished">';
                } else {
                    $html .=    '<div class="product_list_box wishlist">';
                }

                $html .=            '<div class="product_list">';
                $html .=                '<div class="checklist_box"><input type="checkbox" id="' . $flag_id . '" value="' . $object_id . '" onclick="check_item(`' . $flag_id . '`)"><span></span></div>';

                $html .=                '<div class="pl_img"><a href="' . route('web.product.detail', $item->slug) . '"><img src="' . $item->set_image . '"></a></div>';
                $html .=                '<div class="pl_info">';
                $html .=                    '<div class="pl_name"><h4><a href="' . route('web.product.detail', $item->slug) . '">' . $item->set_name . '</a></h4></div>';
                $html .=                    '<div class="pl_owner"><h4><a href="' . route('web.product.detail', $item->slug) . '">' . $item->seller_name . '</a></h4></div>';
                $html .=                    '<div class="pl_price">' . $harga . '</div>';
                $html .=                    '<a href="javascript:void(0);" class="cart_btn" id="add_to_cart-' . $variant_id . '" onclick="add_cart(`' . $variant_id . '`, this)">+ Keranjang</a>';
                $html .=                '</div>';
                $html .=            '</div>';
                $html .=        '</div>';
            }

            // GENERATE PAGINATION HTML CODE
            if (isset($data_wishlist[0])) {
                $set_pagination = Helper::set_pagination($count_product, $page, $perpage);
                $pages          = (int) $set_pagination->pages;

                // GET LENGTH OF SELECTED PAGE
                $length         = strlen($page);

                // GET THE LAST NUMBER OF SELECTED PAGE
                $last           = substr($page, $length - 1, $length);
                if ($last > 0) {
                    // GET FIRST NUMBERS (-LAST) THEN +10
                    $first      = substr($page, 0, $length - 1);
                    $starting   = $first . '0' . +10;
                } else {
                    $starting = $page;
                }

                $pagination .= '<div class="paging_box black_arrow"><ul>';
                if ($page > 1) {
                    // GO TO PREVIOUS PAGE
                    $this_page      = $page - 1;
                    $pagination    .= '<li onclick="open_page(' . $this_page . ')"><a class="prev_btn"></a></li>';
                }
                if ($page > 10) {
                    // GENERATE PAGE(-2) SELECTION AS A SIGNAL TO GENERATES SELECTION OF 10 PREVIOUS PAGE
                    $this_page = $starting - 11;
                    $pagination .= '<li onclick="open_page(' . $this_page . ')"><a>' . $this_page . '</a></li><li>...</li>';
                }

                // GENERATE 10 PAGE SELECTIONS
                if ($pages > 0 && $page < $pages) {
                    if ($starting + 1 < $pages) {
                        $endOptPage = $starting + 1;
                    } else {
                        $endOptPage = $pages;
                    }
                } else {
                    $page = $pages;
                    $endOptPage = $pages;
                }
                for ($i = $starting - 9; $i <= $endOptPage; $i++) {
                    $selected = '';
                    if ($i == $page) {
                        $selected = "selected";
                    }
                    $pagination .= '<li onclick="open_page(' . $i . ')"><a class="' . $selected . '">' . $i . '</a></li>';
                }

                $ceil_pages = ceil($pages / 10);
                $ceil_page  = ceil($page / 10);
                if ($ceil_page < $ceil_pages) {
                    $pagination .= '<li>...</li>';
                }

                if ($page < $pages) {
                    // GO TO NEXT PAGE
                    $this_page      = $page + 1;
                    $pagination    .= '<li onclick="open_page(' . $this_page . ')"><a class="next_btn"></a></li>';
                }
                $pagination .= '</ul></div>';
            }
        } else {
            $html .= '<b><strong>Belum ada barang di wishlistmu, isi sekarang!</strong></b>';
        }

        // SUCCESSFULLY GET DATA
        $response = [
            'status'        => 'success',
            'message'       => 'SUCCESSFULLY GET DATA',
            'html'          => $html,
            'pagination'    => $pagination,
            'product'       => $data_wishlist,
            'total_product' => $count_product
        ];

        return response()->json($response, 200);
    }

    public function delete_wishlist_ajax(Request $request)
    {
        if (!Session::get('buyer')) {
            // FAILED GET DATA
            $response = [
                'status'        => 'failed',
                'message'       => 'Silahkan login untuk melanjutkan'
            ];

            return response()->json($response, 200);
        }

        $data = Session::get('buyer');

        // GET ITEMS
        $items = explode(',', $request->params);

        // CONVERT TO ID
        $real_items_id = [];
        if (isset($items[0])) {
            foreach ($items as $value) {
                if ($value != '') {
                    $real_items_id[] = Helper::validate_token($value);
                }
            }
        }

        DB::beginTransaction();
        try {
            // PROCESS DELETE FROM WISHLIST BY BUYER ID
            wishlist::where('wishlist.buyer_id', $data->id)->whereIn('wishlist.id', $real_items_id)->delete();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id);

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return response()->json([
                'status'    => 'false',
                'message'   => $error_msg
            ]);
        }

        # SUCCESS
        return response()->json([
            'status'    => 'success',
            'message'   => 'Barang telah dihapus dari wishlist'
        ]);
    }

    public function add_wishlist_ajax(Request $request)
    {
        if (!Session::get('buyer')) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Silahkan login untuk melanjutkan',
                'need_auth' => true
            ];

            if ($request->from && $request->from == 'product-detail') {
                $prev_url = Session::get('_previous');
                Session::put('from_page', $prev_url['url']);
            }

            return response()->json($response, 200);
        }

        $data = Session::get('buyer');

        // CONVERT TO ID
        $real_item_id = Helper::validate_token($request->variant);

        // CHECK ITEM PRODUCT VARIANT
        $item = product_item_variant::select(
            'product_item_variant.id as variant_id',
            'product_item.id as item_id',
            'product_item_variant.slug'
        )
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->where('product_item_variant.id', (int) $real_item_id)
            ->first();

        if (!$item) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Data varian tidak ditemukan'
            ];

            return response()->json($response, 200);
        }

        if (is_null($item->item_id)) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Data produk tidak ditemukan'
            ];

            return response()->json($response, 200);
        }

        DB::beginTransaction();
        try {
            // CHECK EXIST DATA
            $exist_data = wishlist::where('buyer_id', (int) $data->id)->where('product_item_variant_id', (int) $real_item_id)->first();
            if (!$exist_data) {
                // PROCESS ADD DATA TO WISHLIST BY BUYER ID
                $insert_wishlist                            = new wishlist();
                $insert_wishlist->buyer_id                  = (int) $data->id;
                $insert_wishlist->product_item_variant_id   = (int) $real_item_id;
                $insert_wishlist->created_at                = date('Y-m-d H:i:s');
                $insert_wishlist->updated_at                = date('Y-m-d H:i:s');
                $insert_wishlist->save();

                $flag = 'add';
                $success_msg = 'Barang telah ditambahkan dalam wishlist';
            } else {
                $exist_data->delete();

                $flag = 'remove';
                $success_msg = 'Barang telah dihapus dari wishlist';
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id);

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return response()->json([
                'status'    => 'false',
                'message'   => $error_msg
            ]);
        }

        # SUCCESS
        return response()->json([
            'status'    => 'success',
            'message'   => $success_msg,
            'flag'      => $flag
        ]);
    }

    public function cart()
    {
        return view('web.buyer.cart');
    }

    public function checkout()
    {
        return view('web.buyer.checkout');
    }

    public function set_default_address(Request $request)
    {
        if (!Session::get('buyer')) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Silahkan login untuk melanjutkan'
            ];

            return response()->json($response, 200);
        }


        $target = $request->target;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $target = Helper::validate_token($request->target);
        }

        // GET TARGET ADDRESS
        if (!$target || (int) $target < 1) {
            // FAILED
            $response = [
                'status'    => 'failed',
                'message'   => 'Data alamat tidak valid, silahkan muat ulang halaman Anda'
            ];

            return response()->json($response, 200);
        }

        // CHECK EXIST TARGET ADDRESS
        $buyer_id   = (int) Session::get('buyer')->id;
        $exist      = buyer_address::where('id', (int) $target)->where('user_id', $buyer_id)->first();
        if (!$exist) {
            // FAILED
            $response = [
                'status'    => 'failed',
                'message'   => 'Data alamat tidak valid, silahkan muat ulang halaman Anda'
            ];

            return response()->json($response, 200);
        }

        DB::beginTransaction();
        try {
            // UPDATE ALL ADDRESS BY BUYER ID, SET IS_DEFAULT TO 0
            buyer_address::where('user_id', $buyer_id)->update(['is_default' => 0]);

            // THEN UPDATE ADDRESS TARGET, SET IS_DEFAULT TO 1
            buyer_address::where('id', (int) $target)->where('user_id', $buyer_id)->update(['is_default' => 1]);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id);

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return response()->json([
                'status'    => 'false',
                'message'   => $error_msg
            ]);
        }

        # SUCCESS
        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah alamat utama'
        ]);
    }

    public function detail_buyer_address(Request $request)
    {
        if (!Session::get('buyer')) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Silahkan login untuk melanjutkan',
                'need_auth' => true
            ];

            if ($request->from && $request->from == 'cart.checkout') {
                $prev_url = Session::get('_previous');
                Session::put('from_page', $prev_url['url']);
            }

            return response()->json($response, 200);
        }

        // GET LIST BUYER ADDRESS
        $address_id = (int) $request->address_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $address_id = Helper::validate_token($request->address_id);
        }

        $data = buyer_address::select(
            'buyer_address.*',
            'id_provinces.name as province_name',
            'id_cities.name as city_name',
            'id_sub_districts.name as sub_district_name',
            'id_villages.name as village_name'
        )
            ->leftJoin('id_provinces', 'buyer_address.province_code', 'id_provinces.code')
            ->leftJoin('id_cities', 'buyer_address.district_code', 'id_cities.full_code')
            ->leftJoin('id_sub_districts', 'buyer_address.sub_district_code', 'id_sub_districts.full_code')
            ->leftJoin('id_villages', 'buyer_address.village_code', 'id_villages.ful_code')
            ->where('buyer_address.id', (int) $address_id)
            ->orderBy('buyer_address.name')
            ->first();

        // ENCRYPT ADDRESS ID
        if (!$data) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Alamat tidak ditemukan'
            ];

            return response()->json($response, 200);
        }

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $data->object_id = Helper::generate_token($data->id);
        }

        # SUCCESS
        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapat alamat',
            'data'      => $data
        ]);
    }
}
