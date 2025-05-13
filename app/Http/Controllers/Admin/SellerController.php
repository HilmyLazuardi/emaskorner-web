<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\SellerExportView;

// Libraries
use App\Libraries\Helper;
use App\Libraries\HelperWeb;

// Models
use App\Models\seller;
use App\Models\province;
use App\Models\seller_token;
use App\Models\jne_branches;

class SellerController extends Controller
{
    // SET THIS MODULE
    private $module     = 'Seller';
    private $module_id  = 26;

    // SET THIS OBJECT/ITEM NAME
    private $item       = 'seller';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View List');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        return view('admin.seller.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = seller::select(
            'seller.*',
            'id_provinces.name as province_name',
            'id_cities.name as city_name',
            'id_sub_districts.name as sub_district_name',
            'id_villages.name as village_name'
        )
            ->leftJoin('id_provinces', 'seller.province_code', 'id_provinces.code')
            ->leftJoin('id_cities', 'seller.district_code', 'id_cities.full_code')
            ->leftJoin('id_sub_districts', 'seller.sub_district_code', 'id_sub_districts.full_code')
            ->leftJoin('id_villages', 'seller.village_code', 'id_villages.full_code');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('edit', $this->translations));
                $html = '<a href="' . route('admin.seller.edit', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $wording_delete = ucwords(lang('delete', $this->translations));
                $html .= '<form action="' . route('admin.seller.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                return $html;
            })
            ->addColumn('status', function ($data) {
                if ($data->status != 1) {
                    return '<span class="label label-danger"><i>' . ucwords(lang('inactive', $this->translations)) . '</i></span>';
                }
                return '<span class="label label-success">' . ucwords(lang('active', $this->translations)) . '</span>';
            })
            ->addColumn('approval', function ($data) {
                if ($data->approval_status != 1) {
                    return '<span class="label label-danger"><i>' . ucwords(lang('no', $this->translations)) . '</i></span>';
                }
                return '<span class="label label-success">' . ucwords(lang('yes', $this->translations)) . '</span>';
            })
            ->editColumn('updated_at', function ($data) {
                return Helper::time_ago(strtotime($data->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->rawColumns(['action', 'status', 'approval'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $provinces      = province::orderBy('name')->get();
        $districts      = null;
        $sub_districts  = null;
        $villages       = null;
        $postal_codes   = null;

        $jne_branches = jne_branches::all();
        $defined_jne_branches = [];

        if (!empty($jne_branches)) {
            foreach ($jne_branches as $jne) {
                $opt_select2_1              = new \stdClass();
                $opt_select2_1->id          = $jne->code;
                $opt_select2_1->name        = $jne->name;
                $defined_jne_branches[]     = $opt_select2_1;
            }
        }

        return view('admin.seller.form', compact('provinces', 'districts', 'sub_districts', 'villages', 'postal_codes', 'jne_branches', 'defined_jne_branches'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item     = ucwords(lang($this->item, $this->translations));

        // get table name
        $module_table   = (new seller())->getTable();

        // LARAVEL VALIDATION
        $validation = [
            'fullname'          => 'required',
            'store_name'        => 'required',
            'email'             => 'required|unique:' . $module_table . ',email',
            'phone_number'      => 'required|numeric',
            'province_code'     => 'required|numeric',
            'district_code'     => 'required|numeric',
            'sub_district_code' => 'required|numeric',
            'village_code'      => 'required|numeric',
            'postal_code'       => 'required|numeric',
            'address_details'   => 'required|string|min:10|max:255',
            'jne_branch'        => 'nullable'
        ];

        if ($request->file('avatar')) {
            $validation['avatar'] = 'image|max:2048';
        }
        if ($request->file('identity_image')) {
            $validation['identity_image'] = 'image|max:2048';
        }

        $message = [
            'required'          => ':attribute ' . lang('should not be empty', $this->translations),
            'unique'            => ':attribute ' . lang('has already been taken, please input another data', $this->translations),
            'confirmed'         => ':attribute ' . lang('does not match', $this->translations),
            'min'               => ':attribute ' . lang('must have a minimum of 6 characters', $this->translations),
            'numeric'           => ':attribute ' . lang('must be a numeric', $this->translations),
            'string'            => ':attribute ' . lang('must be a string', $this->translations),
            'min'               => ':attribute ' . lang('minimal 10 characters', $this->translations),
            // 'max'               => ':attribute ' . lang('maximal 255 characters', $this->translations),
            'image' => ':attribute ' . lang('must be an image', $this->translations),
            'max' => ':attribute ' . lang('may not be greater than #item', $this->translations, ['#item' => '2MB']),
        ];

        $names = [
            'fullname'          => ucwords(lang('fullname', $this->translations)),
            'store_name'        => ucwords(lang('store name', $this->translations)),
            'phone_number'      => ucwords(lang('phone number', $this->translations)),
            'email'             => ucwords(lang('email', $this->translations)),
            'password'          => ucwords(lang('password', $this->translations)),
            'birth_date'        => ucwords(lang('birth date', $this->translations)),
            'identity_number'   => ucwords(lang('identity number', $this->translations)),
            'province_code'     => ucwords(lang('province', $this->translations)),
            'district_code'     => ucwords(lang('district', $this->translations)),
            'sub_district_code' => ucwords(lang('sub district', $this->translations)),
            'village_code'      => ucwords(lang('village', $this->translations)),
            'postal_code'       => ucwords(lang('postal code', $this->translations)),
            'address_details'   => ucwords(lang('address details', $this->translations)),
            'jne_branch'        => ucwords(lang('JNE code', $this->translations)),
            'avatar'            => ucwords(lang('avatar', $this->translations)),
            'identity_image'    => ucwords(lang('identity image', $this->translations)),
        ];

        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // INSERT THE DATA
            $data = new seller();

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $fullname = Helper::validate_input_text($request->fullname);
            if (!$fullname) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('full name', $this->translations))]));
            }
            $data->fullname = $fullname;

            $store_name = Helper::validate_input_text($request->store_name);
            if (!$store_name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('store name', $this->translations))]));
            }
            $data->store_name = $store_name;

            if ($request->description) {
                $description = Helper::validate_input_text($request->description);
                if (!$description) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('description', $this->translations))]));
                }
                $data->description = $description;
            }

            $email = Helper::validate_input_email($request->email);
            if (!$email) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('email', $this->translations))]));
            }
            $data->email = $email;

            $phone_number = Helper::validate_input_text($request->phone_number);

            $data->phone_number = $phone_number;

            if ($request->birth_date) {
                $birth_date = Helper::convert_datepicker($request->birth_date);
                $data->birth_date = $birth_date;
            }

            $data->identity_number = Helper::validate_input_text($request->identity_number);

            if ($request->file('identity_image')) {
                $dir_path           = 'uploads/seller_identity/';
                $image_file         = $request->file('identity_image');
                $format_image_name  = Helper::unique_string();
                $image              = Helper::upload_image($dir_path, $image_file, true, $format_image_name);

                if ($image['status'] != 'true') {
                    return back()
                        ->withInput()
                        ->with('error', lang('Something wrong with #item', $this->translations, ['#item' => ucwords(lang('identity image', $this->translations))]));
                }

                // GET THE UPLOADED IMAGE RESULT
                $data->identity_image = $dir_path . $image['data'];
            }

            if ($request->file('avatar')) {
                $dir_path           = 'uploads/seller_avatar/';
                $image_file         = $request->file('avatar');
                $format_image_name  = Helper::unique_string();
                $image              = Helper::upload_image($dir_path, $image_file, true, $format_image_name);

                if ($image['status'] != 'true') {
                    return back()
                        ->withInput()
                        ->with('error', lang('Something wrong with the #item', $this->translations, ['#item' => ucwords(lang('avatar', $this->translations))]));
                }

                // GET THE UPLOADED IMAGE RESULT
                $data->avatar = $dir_path . $image['data'];
            }

            $data->npwp_number = Helper::validate_input_text($request->npwp_number);

            $province_code = Helper::validate_input_text($request->province_code);
            if (!is_numeric($province_code)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid input for #item', $this->translations, ['#item' => ucwords(lang('province', $this->translations))]));
            }
            $data->province_code = $province_code;

            $district_code = Helper::validate_input_text($request->district_code);
            if (!is_numeric($district_code)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid input for #item', $this->translations, ['#item' => ucwords(lang('district', $this->translations))]));
            }
            $data->district_code = $district_code;

            $sub_district_code = Helper::validate_input_text($request->sub_district_code);
            if (!is_numeric($sub_district_code)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid input for #item', $this->translations, ['#item' => ucwords(lang('sub district', $this->translations))]));
            }
            $data->sub_district_code = $sub_district_code;

            $village_code = Helper::validate_input_text($request->village_code);
            if (!is_numeric($village_code)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid input for #item', $this->translations, ['#item' => ucwords(lang('village', $this->translations))]));
            }
            $data->village_code = $village_code;

            $postal_code = Helper::validate_input_text($request->postal_code);
            if (!is_numeric($postal_code)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid input for #item', $this->translations, ['#item' => ucwords(lang('postal code', $this->translations))]));
            }
            $data->postal_code = $postal_code;

            $address_details = Helper::validate_input_text($request->address_details);
            if (!$address_details) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('address details', $this->translations))]));
            }

            if (strlen($address_details) < 10 && strlen($address_details) > 255) {
                return back()
                    ->withInput()
                    ->with('error', lang('#item minimal 10 characters and maximal is 255 characters', $this->translations, ['#item' => ucwords(lang('address details', $this->translations))]));
            }

            $data->address_details = $address_details;

            // $jne_branch = Helper::validate_input_text($request->jne_branch);
            $jne_branch = '123232';
            // $jne_branches = jne_branches::where('code', $jne_branch)->first();
            // if (empty($jne_branches)) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('JNE code', $this->translations))]));
            // }
            $data->jne_branch = $jne_branch;

            $data->approval_status = (int) $request->approval_status;
            $data->status = (int) $request->status;
            $data->save();

            // IF APPROVAL STATUS == 0, GENERATE TOKEN THEN SEND EMAIL TO SELLER
            if (!$data->approval_status) {
                // SETUP TIME FOR EXPIRED SELLER SESSION
                $time = env('SELLER_EMAIL_EXPIRED', 86400);

                // GENERATE TOKEN
                $token = Helper::unique_string();
                $new_token              = new seller_token();
                $new_token->purpose     = 'agreement';
                $new_token->user_id     = $data->id;
                $new_token->token       = $token;
                $new_token->expired_at  = date('Y-m-d H:i:s', time() + $time); // SECONDS;
                $new_token->status      = 0;
                $new_token->save();

                // SET EMAIL CONTENT
                $expired_plus_seven     = env('SELLER_EMAIL_EXPIRED', 86400) + 25200; // PLUS 7
                $expired_at             = date('Y-m-d H:i:s', time() + $expired_plus_seven);
                $expired_date           = Helper::convert_date_to_indonesian(date('Y-m-d', strtotime($expired_at))) . ' Pukul ' . date('H:i', strtotime($expired_at)) . ' WIB';

                $email_template         = 'emails.seller_account_create_agreement';
                $this_subject           = 'Cek Perjanjian Penjual Kamu di Sini';

                $content                    = [];
                $content['title']           = 'Cek Perjanjian Penjual Kamu di Sini';
                $content['email']           = $email;
                $content['link']            = env('APP_SELLER_URL') . 'agreement/' .  $token;
                $content['expired_date']    = $expired_date; // Kamis, 14 Okt 2021 pukup 14:29 WIB

                $company_info = HelperWeb::get_company_info();
                $content['wa_number']       = env('COUNTRY_CODE') . $company_info->wa_phone;
                $content['wa_link']         = 'https://wa.me/62' . $company_info->wa_phone;

                // SEND EMAIL TO SELLER
                Mail::send($email_template, ['data' => $content], function ($message) use ($email, $this_subject) {
                    if (env('APP_MODE', 'STAGING') == 'STAGING') {
                        $this_subject = '[STAGING] ' . $this_subject;
                    }

                    $message->subject($this_subject);
                    $message->to($email);
                });
            }

            // logging
            $log_detail_id  = 5; // add new
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $fullname . '"';
            $value_before   = null;
            $value_after    = $data;
            $ip_address     = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id);

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return back()->withInput()->with('error', $error_msg);
        }

        // SET REDIRECT URL
        $redirect_url = 'admin.seller';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.seller.create';
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url)
            ->with('success', lang('Successfully added a new #item : #name', $this->translations, ['#item' => $this->item, '#name' => $fullname]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View Details');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // CHECK OBJECT ID
        if ((int) $id < 1) {
            // INVALID OBJECT ID
            return redirect()
                ->route('admin.seller')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = seller::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.seller')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        $provinces = province::orderBy('name')->get();

        // $province_id = $provinces->where('code', $data->province_code)->first();
        // $province_id = (int) $province_id->id;

        $districts = DB::table('id_cities')
            ->where('full_code', 'like', $data->province_code . '%')
            ->orderBy('name')
            ->get();

        $sub_districts = DB::table('id_sub_districts')
            ->where('full_code', 'like', $data->district_code . '%')
            ->orderBy('name')
            ->get();

        $villages = DB::table('id_villages')
            ->where('full_code', 'like', $data->sub_district_code . '%')
            ->orderBy('name')
            ->get();

        $query = DB::table('id_villages')->where('full_code', 'like', $data->village_code . '%')->first();

        $postal_codes = [];

        if ($query) {
            $array = explode(',', $query->pos_code);
            if (isset($array[0])) {
                foreach ($array as $item) {
                    $obj                    = new \stdClass();
                    $obj->postal_code       = $item;
                    $obj->postal_code_label = $item;
                    $postal_codes[]         = $obj;
                }
            }
        }

        $jne_branches = jne_branches::all();
        $defined_jne_branches = [];

        if (!empty($jne_branches)) {
            foreach ($jne_branches as $jne) {
                $opt_select2_1              = new \stdClass();
                $opt_select2_1->id          = $jne->code;
                $opt_select2_1->name        = $jne->name;
                $defined_jne_branches[]     = $opt_select2_1;
            }
        }

        return view('admin.seller.form', compact('data', 'raw_id', 'provinces', 'districts', 'sub_districts', 'villages', 'postal_codes', 'jne_branches', 'defined_jne_branches'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = seller::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        // LARAVEL VALIDATION
        $validation = [
            'fullname'          => 'required',
            'store_name'        => 'required',
            'email'             => 'required',
            'phone_number'      => 'required|numeric',
            'province_code'     => 'required|numeric',
            'district_code'     => 'required|numeric',
            'sub_district_code' => 'required|numeric',
            'village_code'      => 'required|numeric',
            'postal_code'       => 'required|numeric',
            'address_details'   => 'required|string|min:10|max:255',
            'jne_branch'        => 'nullable',
        ];

        if ($request->file('avatar')) {
            $validation['avatar'] = 'image|max:2048';
        }
        if ($request->file('identity_image')) {
            $validation['identity_image'] = 'image|max:2048';
        }

        $message = [
            'required'          => ':attribute ' . lang('should not be empty', $this->translations),
            'unique'            => ':attribute ' . lang('has already been taken, please input another data', $this->translations),
            'confirmed'         => ':attribute ' . lang('does not match', $this->translations),
            'min'               => ':attribute ' . lang('must have a minimum of 6 characters', $this->translations),
            'numeric'           => ':attribute ' . lang('must be a numeric', $this->translations),
            'string'            => ':attribute ' . lang('must be a string', $this->translations),
            'min'               => ':attribute ' . lang('minimal 10 characters', $this->translations),
            // 'max'               => ':attribute ' . lang('maximal 255 characters', $this->translations),
            'image' => ':attribute ' . lang('must be an image', $this->translations),
            'max' => ':attribute ' . lang('may not be greater than #item', $this->translations, ['#item' => '2MB']),
        ];

        $names = [
            'fullname'          => ucwords(lang('fullname', $this->translations)),
            'store_name'        => ucwords(lang('store name', $this->translations)),
            'phone_number'      => ucwords(lang('phone number', $this->translations)),
            'email'             => ucwords(lang('email', $this->translations)),
            'password'          => ucwords(lang('password', $this->translations)),
            'birth_date'        => ucwords(lang('birth date', $this->translations)),
            'identity_number'   => ucwords(lang('identity number', $this->translations)),
            'province_code'     => ucwords(lang('province', $this->translations)),
            'district_code'     => ucwords(lang('district', $this->translations)),
            'sub_district_code' => ucwords(lang('sub district', $this->translations)),
            'village_code'      => ucwords(lang('village', $this->translations)),
            'postal_code'       => ucwords(lang('postal code', $this->translations)),
            'address_details'   => ucwords(lang('address details', $this->translations)),
            'jne_branch'        => ucwords(lang('JNE code', $this->translations)),
            'avatar'            => ucwords(lang('avatar', $this->translations)),
            'identity_image'    => ucwords(lang('identity image', $this->translations)),
        ];

        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $fullname = Helper::validate_input_text($request->fullname);
            if (!$fullname) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('full name', $this->translations))]));
            }
            $data->fullname = $fullname;

            $store_name = Helper::validate_input_text($request->store_name);
            if (!$store_name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('store name', $this->translations))]));
            }
            $data->store_name = $store_name;

            if ($request->description) {
                $description = Helper::validate_input_text($request->description);
                if (!$description) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('description', $this->translations))]));
                }
                $data->description = $description;
            }

            $email = Helper::validate_input_email($request->email);
            if (!$email) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('email', $this->translations))]));
            }

            // CHECK EMAIL
            if ($data->email != $email) {
                $check_email = seller::where('email', $email)->where('id', '!=', $id)->first();
                if (!empty($check_email)) {
                    return back()
                        ->withInput()
                        ->with('error', lang('#item already exist, please input another one', $this->translations, ['#item' => ucwords(lang('email', $this->translations))]));
                }

                // change email
                $data->email = $email;
            }

            $phone_number = Helper::validate_input_text($request->phone_number);

            $data->phone_number = $phone_number;

            if ($request->birth_date) {
                $birth_date = Helper::convert_datepicker($request->birth_date);
                $data->birth_date = $birth_date;
            }

            $data->identity_number = Helper::validate_input_text($request->identity_number);

            // IF DELETE EXISTING IMAGE
            if ($request->identity_image_delete == 'yes') {
                $data->identity_image = null;
            }

            if ($request->file('identity_image')) {
                $dir_path           = 'uploads/seller_identity/';
                $image_file         = $request->file('identity_image');
                $format_image_name  = Helper::unique_string();
                $image              = Helper::upload_image($dir_path, $image_file, true, $format_image_name);

                if ($image['status'] != 'true') {
                    return back()
                        ->withInput()
                        ->with('error', lang('Something wrong with #item', $this->translations, ['#item' => ucwords(lang('identity image', $this->translations))]));
                }

                // GET THE UPLOADED IMAGE RESULT
                $data->identity_image = $dir_path . $image['data'];
            }

            if ($request->file('avatar')) {
                $dir_path           = 'uploads/seller_avatar/';
                $image_file         = $request->file('avatar');
                $format_image_name  = Helper::unique_string();
                $image              = Helper::upload_image($dir_path, $image_file, true, $format_image_name);

                if ($image['status'] != 'true') {
                    return back()
                        ->withInput()
                        ->with('error', lang('Something wrong with the #item', $this->translations, ['#item' => ucwords(lang('avatar', $this->translations))]));
                }

                // GET THE UPLOADED IMAGE RESULT
                $data->avatar = $dir_path . $image['data'];
            }

            $data->npwp_number = Helper::validate_input_text($request->npwp_number);

            $province_code = Helper::validate_input_text($request->province_code);
            if (!is_numeric($province_code)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid input for #item', $this->translations, ['#item' => ucwords(lang('province', $this->translations))]));
            }
            $data->province_code = $province_code;

            $district_code = Helper::validate_input_text($request->district_code);
            if (!is_numeric($district_code)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid input for #item', $this->translations, ['#item' => ucwords(lang('district', $this->translations))]));
            }
            $data->district_code = $district_code;

            $sub_district_code = Helper::validate_input_text($request->sub_district_code);
            if (!is_numeric($sub_district_code)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid input for #item', $this->translations, ['#item' => ucwords(lang('sub district', $this->translations))]));
            }
            $data->sub_district_code = $sub_district_code;

            $village_code = Helper::validate_input_text($request->village_code);
            if (!is_numeric($village_code)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid input for #item', $this->translations, ['#item' => ucwords(lang('village', $this->translations))]));
            }
            $data->village_code = $village_code;

            $postal_code = Helper::validate_input_text($request->postal_code);
            if (!is_numeric($postal_code)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid input for #item', $this->translations, ['#item' => ucwords(lang('postal code', $this->translations))]));
            }
            $data->postal_code = $postal_code;

            $address_details = Helper::validate_input_text($request->address_details);
            if (!$address_details) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('address details', $this->translations))]));
            }

            if (strlen($address_details) < 10 && strlen($address_details) > 255) {
                return back()
                    ->withInput()
                    ->with('error', lang('#item minimal 10 characters and maximal is 255 characters', $this->translations, ['#item' => ucwords(lang('address details', $this->translations))]));
            }

            $data->address_details = $address_details;

            // $jne_branch = Helper::validate_input_text($request->jne_branch);
            $jne_branch = Helper::validate_input_text($request->jne_branch);
            // $jne_branches = jne_branches::where('code', $jne_branch)->first();
            // if (empty($jne_branches)) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('JNE code', $this->translations))]));
            // }
            $data->jne_branch = $jne_branch;

            $data->approval_status = (int) $request->approval_status;
            $data->status = (int) $request->status;

            $data->save();

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id  = 7; // update
                $module_id      = $this->module_id;
                $target_id      = $data->id;
                $note           = '"' . $fullname . '"';
                $ip_address     = $request->ip();
                Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id, $id);

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return back()
                ->withInput()
                ->with('error', $error_msg);
        }

        # SUCCESS
        $success_message = lang('Successfully updated #item : #name', $this->translations, ['#item' => $this->item, '#name' => $fullname]);
        if ($request->stay_on_page) {
            return redirect()
                ->route('admin.seller.edit', $raw_id)
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.seller')
                ->with('success', $success_message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Delete');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $request->id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($raw_id);
        }

        // GET DATA BY ID
        $data = seller::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        // DELETE THE DATA
        if ($data->delete()) {
            // logging
            $log_detail_id  = 8; // delete
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $data->fullname . '"';
            $value_after    = null;
            $ip_address     = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.seller')
                ->with('success', lang('Successfully deleted #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()->with('error', lang('Oops, failed to delete #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }

    /**
     * Display a listing of the deleted resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleted_data()
    {
        $deleted_data = true;

        return view('admin.seller.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        $query = seller::onlyTrashed();

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.seller.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-success" title="' . $wording_restore . '"><i class="fa fa-check"></i>&nbsp; ' . $wording_restore . '</button></form>';
            })
            ->editColumn('deleted_at', function ($data) {
                return Helper::time_ago(strtotime($data->deleted_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * Restore the specified deleted resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Restore');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $request->id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($raw_id);
        }

        // GET DATA BY ID
        $data = seller::onlyTrashed()->find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // RESTORE THE DATA
        if ($data->restore()) {
            // logging
            $log_detail_id  = 9; // restore
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $data->fullname . '"';
            $value_before   = null;
            $value_after    = $data->toJson();
            $ip_address     = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.seller.deleted_data')
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend_email(Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $id = $request->id;
        $raw_id = $id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($raw_id);
        }

        // GET DATA BY ID
        $data = seller::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        $email = $data->email;

        // CHECK IS APPROVAL STATUS = 0?
        // IF APPROVAL STATUS == 0, THEN SEND EMAIL
        if (!$data->approval_status) {
            // SETUP TIME FOR EXPIRED SELLER SESSION
            $time = env('SELLER_EMAIL_EXPIRED', 86400);

            // CHECK, IS ALREADY SEND EMAIL?
            $seller_token = seller_token::where('user_id', $data->id)
                ->where('status', 0)
                ->where('purpose', 'agreement')
                ->first();

            // SET TIME PLUS 7
            $expired_plus_seven     = env('SELLER_EMAIL_EXPIRED', 86400) + 25200; // PLUS 7

            // IF NOT EMPTY, CHECK EXPIRED
            if (!empty($seller_token) && $seller_token->expired_at > date('Y-m-d H:i:s')) {
                // IF NOT EXPIRED, UPDATE EXPIRED TIME
                $seller_token->expired_at = date('Y-m-d H:i:s', time() + $time); // SECONDS;
                $seller_token->save();

                // $expired_at = explode(' ', $seller_token->expired_at);
                $expired_at = date('Y-m-d H:i:s', time() + $expired_plus_seven);
                $token = $seller_token->token;
            } else {
                // IF ALREADY EXPIRED OR THERE IS NO TOKEN, GENERATE NEW TOKEN
                $token = Helper::unique_string();

                $new_token              = new seller_token();
                $new_token->purpose     = 'agreement';
                $new_token->user_id     = $data->id;
                $new_token->token       = $token;
                $new_token->expired_at  = date('Y-m-d H:i:s', time() + $time); // SECONDS;
                $new_token->status      = 0;
                $new_token->save();

                // $expired_at = explode(' ', $new_token->expired_at);
                $expired_at = date('Y-m-d H:i:s', time() + $expired_plus_seven);
            }

            $expired_date           = Helper::convert_date_to_indonesian(date('Y-m-d', strtotime($expired_at))) . ' Pukul ' . date('H:i', strtotime($expired_at)) . ' WIB';

            // SET EMAIL CONTENT
            $email_template = 'emails.seller_account_create_agreement';
            $this_subject = 'Cek Perjanjian Penjual Kamu di Sini';
            $content = [];
            $content['title'] = 'Cek Perjanjian Penjual Kamu di Sini';
            $content['email'] = $email;
            $content['link'] = env('APP_SELLER_URL') . 'agreement/' .  $token;
            // $content['expired_date'] = $expired_at[0];
            // $content['expired_hour'] = $expired_at[1];
            $content['expired_date']    = $expired_date; // Kamis, 14 Okt 2021 pukup 14:29 WIB

            $company_info = HelperWeb::get_company_info();
            $content['wa_number']       = env('COUNTRY_CODE') . $company_info->wa_phone;
            $content['wa_link']         = 'https://wa.me/62' . $company_info->wa_phone;

            // SEND EMAIL TO SELLER
            Mail::send($email_template, ['data' => $content], function ($message) use ($email, $this_subject) {
                if (env('APP_MODE', 'STAGING') == 'STAGING') {
                    $this_subject = '[STAGING] ' . $this_subject;
                }

                $message->subject($this_subject);
                $message->to($email);
            });

            # SUCCESS
            return redirect()
                ->route('admin.seller.edit', $raw_id)
                ->with('success', lang('Successfully resend email agreement to this #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()->with('error', lang('Oops, this #item is already approved.', $this->translations, ['#item' => $this->item]));
    }

    private function validate_phone($phone)
    {
        $first_letter = substr($phone, 0, 1);
        $two_letter = substr($phone, 0, 2);
        if ($first_letter == "0") {
            $remove_first_letter = substr($phone, 1);
            return $remove_first_letter;
        } else {
            if ($two_letter == "62") {
                $remove_two_letter = substr($phone, 2);
                return $remove_two_letter;
            }
        }
    }

    public function export(Request $request)
    {
        $query = seller::select(
            'seller.*',
            'id_provinces.province_name',
            'id_cities.city_name',
            'id_sub_districts.sub_district_name',
            'id_villages.village_name'
        )
            ->leftJoin('id_provinces', 'seller.province_code', 'id_provinces.province_code')
            ->leftJoin('id_cities', 'seller.district_code', 'id_cities.city_code')
            ->leftJoin('id_sub_districts', 'seller.sub_district_code', 'id_sub_districts.sub_district_code')
            ->leftJoin('id_villages', 'seller.village_code', 'id_villages.village_code');

        $data = $query->get();

        foreach ($data as $item) {
            $item->seller_status = 'inactive';
            if ($item->status == 1) {
                $item->seller_status = 'active';
            }

            $item->seller_approval_status = 'no';
            if ($item->approval_status == 1) {
                $item->seller_approval_status = 'yes';
            }
        }

        $export_data = new \stdClass();
        $export_data->data = $data;

        // SET FILE NAME
        $filename = $this->global_config->app_name . '-export-seller';

        return Excel::download(new SellerExportView($export_data), $filename . '.xlsx');
    }
}
