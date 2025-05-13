<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\product_item;
use App\Models\product_item_variant;

class ProductVariantController extends Controller
{
    // SET THIS MODULE
    private $module     = 'Product Variant';
    private $module_id  = 29;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'product variant';

    /**
     * Display a listing of the resource.
     *
     * @param  integer  $product_item_id
     * @return \Illuminate\Http\Response
     */
    public function index($product_item_id)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View List');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        // validate product item id
        $raw_product_item_id = $product_item_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        // cek apakah produk item exist
        $product_item = product_item::find($product_item_id);
        if (!$product_item) {
            # ERROR
            return back()
                ->with('error', lang(
                    '#item not found, please check your link again',
                    $this->translations,
                    ['#item' => lang('product item', $this->translations)]
                ));
        }

        // get data product variant list
        $data = product_item_variant::where('product_item_id', $product_item->id)->orderBy('ordinal')->get();
        foreach ($data as $item) {
            $item->global_stock = $product_item->global_stock;
            $item->global_stock_value = $product_item->qty;
        }

        return view('admin.product_variant.form', compact('raw_product_item_id', 'product_item', 'data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param   String    $product_item_id
     * @param   \Illuminate\Http\Request    $request
     * @return \Illuminate\Http\Response
     */
    public function store($product_item_id, Request $request)
    {
        $process_ajax = false;
        if ($request->ajax()) {
            $process_ajax = true;
        }

        # AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            # ERROR
            $error_message = $authorize['message'];
            if ($process_ajax) {
                return response()->json([
                    'status' => 'false',
                    'message' => $error_message
                ]);
            }
            return back()->with('error', $error_message);
        }

        // validate product item id
        $raw_product_item_id = $product_item_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        // cek apakah produk item exist
        $product_item = product_item::find($product_item_id);
        if (!$product_item) {
            # ERROR
            $error_message = lang(
                '#item not found, please check your link again',
                $this->translations,
                ['#item' => lang('product item', $this->translations)]
            );
            if ($process_ajax) {
                return response()->json([
                    'status' => 'false',
                    'message' => $error_message
                ]);
            }
            return back()
                ->with('error', $error_message);
        }

        # SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        # LARAVEL VALIDATION
        $validation_rules = [
            'variant_1'  => 'required',
            'price.*'   => 'required',
            'weight.*'   => 'required',
            'status.*'  => 'required'
        ];

        $global_stock = (int) $request->global_stock;
        if ($global_stock) {
            $validation_rules['global_stock_value'] = 'required';
        } else {
            $validation_rules['stock.*'] = 'required';
        }

        $validation_messages = [
            'required'  => ':attribute ' . lang('should not be empty', $this->translations),
        ];

        $validation_custom_attributes = [
            'variant_1'          => ucwords(lang('variant 1', $this->translations)),
            'variant_2'          => ucwords(lang('variant 2', $this->translations)),
            'price.*'            => ucwords(lang('variant price', $this->translations)),
            'stock.*'            => ucwords(lang('variant stock', $this->translations)),
            'weight.*'            => ucwords(lang('variant weight', $this->translations)),
            'status.*'           => ucwords(lang('variant status', $this->translations)),
            'global_stock_value' => ucwords(lang('global stock', $this->translations)),
        ];

        if ($process_ajax) {
            $validator = Validator::make($request->all(), $validation_rules, $validation_messages, $validation_custom_attributes);
            if ($validator->fails()) {
                # ERROR
                return response()->json([
                    'status' => 'false',
                    'message' => 'Validation Error: ' . implode(', ', $validator->errors()->all()),
                    'data' => $validator->errors()->messages()
                ]);
            }
        } else {
            $this->validate($request, $validation_rules, $validation_messages, $validation_custom_attributes);
        }


        DB::beginTransaction();
        try {
            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $variant_1_name = Helper::validate_input_text($request->variant_1, true);
            if (!$variant_1_name) {
                # ERROR
                $error_message = lang(
                    'Invalid format for #item',
                    $this->translations,
                    ['#item' => $validation_custom_attributes['variant_1']]
                );
                if ($process_ajax) {
                    return response()->json([
                        'status' => 'false',
                        'message' => $error_message
                    ]);
                }
                return back()
                    ->withInput()
                    ->with('error', $error_message);
            }

            // store to product item data
            $product_item->variant_1 = $variant_1_name;

            # use global stock
            $product_item->global_stock = (int) $global_stock;
            if ($global_stock) {
                // validate the value
                $global_stock_value = (int) str_replace(',', '', $request->global_stock_value);
                if ($global_stock_value < 1) {
                    # ERROR
                    $error_message = lang(
                        'Invalid value for #item #description',
                        $this->translations,
                        [
                            '#item' => $validation_custom_attributes['global_stock_value'],
                            '#description' => ' (Minimal 1)'
                        ]
                    );
                    if ($process_ajax) {
                        return response()->json([
                            'status' => 'false',
                            'message' => $error_message
                        ]);
                    }
                    return back()
                        ->withInput()
                        ->with('error', $error_message);
                }

                // then store to product item data
                $product_item->qty = $global_stock_value;
            }

            # VALIDATE VARIANT 1 LIST
            $variant_1_list = $request->variant_1_list; // Array
            if (empty($variant_1_list)) {
                # ERROR
                $error_message = 'Pilihan varian 1 harus diisi minimal 1';
                if ($process_ajax) {
                    return response()->json([
                        'status' => 'false',
                        'message' => $error_message
                    ]);
                }
                return back()->withInput()->with('error', $error_message);
            }
            $variant_1_list_array = [];
            foreach ($variant_1_list as $key => $value) {
                $variant_1_list_array[] = $value;
            }

            // store to product item data
            $product_item->variant_1_list = json_encode($variant_1_list_array); // JSON Array

            // get variant data
            $variant_prices = $request->price;
            $variant_stocks = $request->stock;
            $variant_weights = $request->weight;
            $variant_images = $request->file('image_variant');
            $variant_status = $request->status;

            # VALIDATE VARIANT 2
            if ($request->variant_2) {
                $variant_2_name = Helper::validate_input_text($request->variant_2, true);
                if (!$variant_2_name) {
                    # ERROR
                    $error_message = lang(
                        'Invalid format for #item',
                        $this->translations,
                        ['#item' => $validation_custom_attributes['variant_2']]
                    );
                    if ($process_ajax) {
                        return response()->json([
                            'status' => 'false',
                            'message' => $error_message
                        ]);
                    }
                    return back()
                        ->withInput()
                        ->with('error', $error_message);
                }

                // store to product item data
                $product_item->variant_2 = $variant_2_name;

                $variant_2_list = $request->variant_2_list; // Array
                if (empty($variant_2_list)) {
                    # ERROR
                    $error_message = 'Pilihan varian 2 harus diisi minimal 1';
                    if ($process_ajax) {
                        return response()->json([
                            'status' => 'false',
                            'message' => $error_message
                        ]);
                    }
                    return back()
                        ->withInput()
                        ->with('error', $error_message);
                }
                $variant_2_list_array = [];
                foreach ($variant_2_list as $key => $value) {
                    $variant_2_list_array[] = $value;
                }

                // store to product item data
                $product_item->variant_2_list = json_encode($variant_2_list_array); // JSON Array

                # STORE PRODUCT ITEM VARIANT DATA
                $variant_list = [];
                $key_mixed = 0;
                $set_default = false;
                foreach ($variant_1_list as $var_1) {
                    foreach ($variant_2_list as $var_2) {
                        $variant_name = $var_1 . ' - ' . $var_2;
                        if (!in_array($variant_name, $variant_list)) {
                            $variant_list[] = $variant_name;

                            $product_item_variant = new product_item_variant();
                            $product_item_variant->product_item_id = $product_item->id;
                            $product_item_variant->variant_1 = $var_1;
                            $product_item_variant->variant_2 = $var_2;
                            $product_item_variant->name = $variant_name;

                            // generate slug for product_item_variant
                            $slug = $product_item->slug . '-' . Helper::generate_slug($product_item_variant->name);
                            $product_item_variant->slug = Helper::check_unique('product_item_variant', $slug, 'slug');

                            // generate SKU for product_item_variant
                            $sku = 'EK-' . date('Ymd') . '-' . $product_item->id . ((int) substr(time(), 0, 3) + $key_mixed);
                            $product_item_variant->sku_id = Helper::check_unique('product_item_variant', $sku, 'sku_id');

                            // processing variant image
                            if (isset($variant_images[$key_mixed])) {
                                # PROCESSING IMAGE FILE
                                $dir_path = 'uploads/product_item_variant/';
                                $image_file = $variant_images[$key_mixed];
                                $format_image_name = $product_item->slug . '-' . $product_item_variant->slug; // cth: sepatu-kulit-hitam-42
                                $uploaded_image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);

                                if ($uploaded_image['status'] != 'true') {
                                    # ERROR
                                    $error_message = lang($uploaded_image['message'], $this->translation, $uploaded_image['dynamic_objects']);
                                    if ($process_ajax) {
                                        return response()->json([
                                            'status' => 'false',
                                            'message' => $error_message
                                        ]);
                                    }
                                    return back()->withInput()->with('error', $error_message);
                                }

                                // GET THE UPLOADED IMAGE RESULT
                                $product_item_variant->variant_image = $dir_path . $uploaded_image['data'];
                            }

                            $product_item_variant->price = (int) str_replace(',', '', $variant_prices[$key_mixed]);
                            $product_item_variant->weight = (int) str_replace(',', '', $variant_weights[$key_mixed]);

                            # IF NOT USE GLOBAL STOCK
                            if (!$global_stock) {
                                # set variant stock
                                $item_variant_stocks = (int) str_replace(',', '', $variant_stocks[$key_mixed]);

                                if ($item_variant_stocks < 1) {
                                    # ERROR
                                    $error_message = lang(
                                        'Invalid value for #item #description',
                                        $this->translations,
                                        [
                                            '#item' => ucwords(lang('variant stock', $this->translations)),
                                            '#description' => ' (Minimal 1)'
                                        ]
                                    );
                                    if ($process_ajax) {
                                        return response()->json([
                                            'status' => 'false',
                                            'message' => $error_message
                                        ]);
                                    }
                                    return back()
                                        ->withInput()
                                        ->with('error', $error_message);
                                }

                                $product_item_variant->qty = $item_variant_stocks;
                                $product_item_variant->qty_booked = 0;
                                $product_item_variant->qty_sold = 0;
                            }

                            $product_item_variant->status = (int) $variant_status[$key_mixed];

                            # SET DEFAULT VARIANT
                            if (!$set_default && $product_item_variant->status) {
                                $set_default = true;
                                $product_item_variant->is_default = 1;
                            }

                            $product_item_variant->save();

                            $key_mixed++;
                        }
                    }
                }
            } else {
                # VARIANT 1 ONLY

                # STORE PRODUCT ITEM VARIANT DATA
                $variant_list = [];
                $key_mixed = 0;
                $set_default = false;
                foreach ($variant_1_list as $var_1) {
                    $variant_name = $var_1;

                    $product_item_variant = new product_item_variant();
                    $product_item_variant->product_item_id = $product_item->id;
                    $product_item_variant->variant_1 = $var_1;
                    $product_item_variant->name = $variant_name;

                    // generate slug for product_item_variant
                    $slug = $product_item->slug . '-' . Helper::generate_slug($product_item_variant->name);
                    $product_item_variant->slug = Helper::check_unique('product_item_variant', $slug, 'slug');

                    // generate SKU for product_item_variant
                    $sku = 'EK-' . date('Ymd') . '-' . $product_item->id . ((int) substr(time(), 0, 3) + $key_mixed);
                    $product_item_variant->sku_id = Helper::check_unique('product_item_variant', $sku, 'sku_id');

                    // processing variant image
                    if (isset($variant_images[$key_mixed])) {
                        # PROCESSING IMAGE FILE
                        $dir_path = 'uploads/product_item_variant/';
                        $image_file = $variant_images[$key_mixed];
                        $format_image_name = $product_item->slug . '-' . $product_item_variant->slug; // cth: tas-anyaman-merah
                        $uploaded_image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);

                        if ($uploaded_image['status'] != 'true') {
                            # ERROR
                            $error_message = lang($uploaded_image['message'], $this->translation, $uploaded_image['dynamic_objects']);
                            if ($process_ajax) {
                                return response()->json([
                                    'status' => 'false',
                                    'message' => $error_message
                                ]);
                            }
                            return back()->withInput()->with('error', $error_message);
                        }

                        // GET THE UPLOADED IMAGE RESULT
                        $product_item_variant->variant_image = $dir_path . $uploaded_image['data'];
                    }

                    $product_item_variant->price = (int) str_replace(',', '', $variant_prices[$key_mixed]);
                    $product_item_variant->weight = (int) str_replace(',', '', $variant_weights[$key_mixed]);

                    # IF NOT USE GLOBAL STOCK
                    if (!$global_stock) {
                        # set variant stock
                        $item_variant_stocks = (int) str_replace(',', '', $variant_stocks[$key_mixed]);

                        if ($item_variant_stocks < 1) {
                            # ERROR
                            $error_message = lang(
                                'Invalid value for #item #description',
                                $this->translations,
                                [
                                    '#item' => ucwords(lang('variant stock', $this->translations)),
                                    '#description' => ' (Minimal 1)'
                                ]
                            );
                            if ($process_ajax) {
                                return response()->json([
                                    'status' => 'false',
                                    'message' => $error_message
                                ]);
                            }
                            return back()
                                ->withInput()
                                ->with('error', $error_message);
                        }

                        $product_item_variant->qty = $item_variant_stocks;
                        $product_item_variant->qty_booked = 0;
                        $product_item_variant->qty_sold = 0;
                    }

                    $product_item_variant->status = (int) $variant_status[$key_mixed];

                    # SET DEFAULT VARIANT
                    if (!$set_default && $product_item_variant->status) {
                        $set_default = true;
                        $product_item_variant->is_default = 1;
                    }

                    $product_item_variant->save();

                    $key_mixed++;
                }
            }

            $product_item->save();

            # Logging
            $log_detail_id  = 5; // add new
            $module_id      = $this->module_id;
            $target_id      = $product_item->id;
            $note           = null;
            $value_before   = null;
            $value_after    = null; // doesn't save anything coz too much data to be saved
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after);

            DB::commit();

            # SUCCESS

            // VALIDASI APAKAH DALAM SESI PROSES ADD NEW PRODUCT ITEM
            $session_count_product_variant = 'count_product_variant_' . urlencode($product_item_id);
            if (Session::has($session_count_product_variant)) {
                // +1 VALUE
                $increment = (int) Session::get($session_count_product_variant) + 1;

                // PUT NEW SESSION
                Session::forget($session_count_product_variant);
                Session::put($session_count_product_variant, $increment);
            }

            $success_message = lang('Successfully added new #item', $this->translations, ['#item' => $this->item]);
            if ($process_ajax) {
                $response = [
                    'status' => 'true',
                    'message' => $success_message
                ];
                return response()->json($response, 200);
            }
            return redirect()
                ->route('admin.product_variant', $raw_product_item_id)
                ->with('success', $success_message);
        } catch (\Exception $ex) {
            DB::rollback();

            $error_message = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_message, $this->module_id, null, "Failed to input product variants");

            if (env('APP_DEBUG') == false) {
                $error_message = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            if ($process_ajax) {
                return response()->json([
                    'status' => 'false',
                    'message' => $error_message
                ]);
            }
            return back()->withInput()->with('error', $error_message);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param   String    $product_item_id
     * @param   \Illuminate\Http\Request    $request
     * @return  \Illuminate\Http\Response
     */
    public function update($product_item_id, Request $request)
    {
        $process_ajax = false;
        if ($request->ajax()) {
            $process_ajax = true;
        }

        # AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            # ERROR
            $error_message = $authorize['message'];
            if ($process_ajax) {
                return response()->json([
                    'status' => 'false',
                    'message' => $error_message
                ]);
            }
            return back()->with('error', $error_message);
        }

        // validate product item id
        $raw_product_item_id = $product_item_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        // cek apakah produk item exist
        $product_item = product_item::find($product_item_id);
        if (!$product_item) {
            # ERROR
            $error_message = lang(
                '#item not found, please check your link again',
                $this->translations,
                ['#item' => lang('product item', $this->translations)]
            );
            if ($process_ajax) {
                return response()->json([
                    'status' => 'false',
                    'message' => $error_message
                ]);
            }
            return back()
                ->with('error', $error_message);
        }

        # SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        # LARAVEL VALIDATION
        $validation_rules = [
            'variant_1'  => 'required',
            'price.*'   => 'required',
            'weight.*'   => 'required',
            'status.*'  => 'required'
        ];

        $global_stock = (int) $request->global_stock;
        if ($global_stock) {
            $validation_rules['global_stock_value'] = 'required';
        } else {
            $validation_rules['stock.*'] = 'required';
        }

        $validation_messages = [
            'required'  => ':attribute ' . lang('should not be empty', $this->translations),
        ];

        $validation_custom_attributes = [
            'variant_1'          => ucwords(lang('variant 1', $this->translations)),
            'variant_2'          => ucwords(lang('variant 2', $this->translations)),
            'price.*'            => ucwords(lang('variant price', $this->translations)),
            'stock.*'            => ucwords(lang('variant stock', $this->translations)),
            'weight.*'            => ucwords(lang('variant weight', $this->translations)),
            'status.*'           => ucwords(lang('variant status', $this->translations)),
            'global_stock_value' => ucwords(lang('global stock', $this->translations)),
        ];

        if ($process_ajax) {
            $validator = Validator::make($request->all(), $validation_rules, $validation_messages, $validation_custom_attributes);
            if ($validator->fails()) {
                # ERROR
                return response()->json([
                    'status' => 'false',
                    'message' => 'Validation Error: ' . implode(', ', $validator->errors()->all()),
                    'data' => $validator->errors()->messages()
                ]);
            }
        } else {
            $this->validate($request, $validation_rules, $validation_messages, $validation_custom_attributes);
        }

        DB::beginTransaction();
        try {
            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $variant_1_name = Helper::validate_input_text($request->variant_1, true);
            if (!$variant_1_name) {
                # ERROR
                $error_message = lang(
                    'Invalid format for #item',
                    $this->translations,
                    ['#item' => $validation_custom_attributes['variant_1']]
                );
                if ($process_ajax) {
                    return response()->json([
                        'status' => 'false',
                        'message' => $error_message
                    ]);
                }
                return back()
                    ->withInput()
                    ->with('error', $error_message);
            }

            // store to product item data
            $product_item->variant_1 = $variant_1_name;

            # use global stock
            $product_item->global_stock = (int) $global_stock;
            if ($global_stock) {
                // validate the value
                $global_stock_value = (int) str_replace(',', '', $request->global_stock_value);
                if ($global_stock_value < 1) {
                    $qty_buyed = $product_item->qty_booked + $product_item->qty_sold;
                    if ($qty_buyed < 1) { // jika belum ada qty_booked / qty sold dan qty yang di input = 0
                        # ERROR
                        $error_message = lang(
                            'Invalid value for #item #description',
                            $this->translations,
                            [
                                '#item' => $validation_custom_attributes['global_stock_value'],
                                '#description' => ' (Minimal 1)'
                            ]
                        );
                        if ($process_ajax) {
                            return response()->json([
                                'status' => 'false',
                                'message' => $error_message
                            ]);
                        }
                        return back()
                            ->withInput()
                            ->with('error', $error_message);
                    }
                }

                // then store to product item data
                $product_item->qty = $global_stock_value;
            } else {
                // jika bukan global stock, maka kosongkan qty pada product item
                $product_item->qty = 0;
            }

            # VALIDATE VARIANT 1 LIST
            $variant_1_list = $request->variant_1_list; // Array
            if (empty($variant_1_list)) {
                # ERROR
                $error_message = 'Pilihan varian 1 harus diisi minimal 1';
                if ($process_ajax) {
                    return response()->json([
                        'status' => 'false',
                        'message' => $error_message
                    ]);
                }
                return back()->withInput()->with('error', $error_message);
            }
            $variant_1_list_array = [];
            foreach ($variant_1_list as $value) {
                $variant_1_list_array[] = $value;
            }

            // store to product item data
            $product_item->variant_1_list = json_encode($variant_1_list_array); // JSON Array

            # GET EXISTING PRODUCT VARIANT DATA
            $variant_data = product_item_variant::where('product_item_id', $product_item->id)->get();

            $variant_ids = [];
            $variant_data_list = [];
            if (isset($variant_data[0])) {
                foreach ($variant_data as $variant) {
                    $variant_ids[] = $variant->id;
                    $variant_data_list[$variant->id] = $variant;
                }
            }

            $key_mixed = 0;

            // get variant data
            $variant_prices_exist = $request->price;
            $variant_stocks_exist = $request->stock;
            $variant_weights_exist = $request->weight;
            $variant_status_exist = $request->status;
            $variant_images_exist_tmp = $request->image_variant_exist;
            $variant_images_exist = $request->image_variant;

            $variant_prices_new = $request->price_new;
            $variant_stocks_new = $request->stock_new;
            $variant_weights_new = $request->weight_new;
            $variant_status_new = $request->status_new;
            $variant_images_new_tmp = $request->image_variant_exist_new;
            $variant_images_new = $request->image_variant_new;

            $request_ordinal = $request->ordinal;
            
            // generate tmp data image
            $exist_variant_images_tmp = [];
            foreach ($variant_images_exist_tmp as $key => $variant_image_exist_tmp) {
                if (isset($variant_images_exist[$key])) {
                    array_push($exist_variant_images_tmp, $variant_images_exist[$key]);
                } else {
                    array_push($exist_variant_images_tmp, NULL);
                }
            }

            foreach ($request_ordinal as $key => $value) {
                if (empty($value)) {
                    # new variant
                    $variant_prices[] = array_shift($variant_prices_new);
                    $variant_stocks[] = array_shift($variant_stocks_new);
                    $variant_weights[] = array_shift($variant_weights_new);
                    $variant_status[] = array_shift($variant_status_new);
                    if (!empty($variant_images_new_tmp[0])) {
                        $variant_images[] = array_shift($variant_images_new);
                    } else {
                        $variant_images[] = NULL;
                    }
                    array_shift($variant_images_new_tmp);
                } else {
                    # existing variant
                    $variant_prices[] = array_shift($variant_prices_exist);
                    $variant_stocks[] = array_shift($variant_stocks_exist);
                    $variant_weights[] = array_shift($variant_weights_exist);
                    $variant_status[] = array_shift($variant_status_exist);
                    $variant_images[] = array_shift($exist_variant_images_tmp);
                }
            }

            # VALIDATE VARIANT 2
            if ($request->variant_2) {
                $variant_2_name = Helper::validate_input_text($request->variant_2, true);
                if (!$variant_2_name) {
                    # ERROR
                    $error_message = lang(
                        'Invalid format for #item',
                        $this->translations,
                        ['#item' => $validation_custom_attributes['variant_2']]
                    );
                    if ($process_ajax) {
                        return response()->json([
                            'status' => 'false',
                            'message' => $error_message
                        ]);
                    }
                    return back()
                        ->withInput()
                        ->with('error', $error_message);
                }

                // store to product item data
                $product_item->variant_2 = $variant_2_name;

                $variant_2_list = $request->variant_2_list; // Array
                if (empty($variant_2_list)) {
                    # ERROR
                    $error_message = 'Pilihan varian 2 harus diisi minimal 1';
                    if ($process_ajax) {
                        return response()->json([
                            'status' => 'false',
                            'message' => $error_message
                        ]);
                    }
                    return back()
                        ->withInput()
                        ->with('error', $error_message);
                }
                $variant_2_list_array = [];
                foreach ($variant_2_list as $value) {
                    $variant_2_list_array[] = $value;
                }

                // store to product item data
                $product_item->variant_2_list = json_encode($variant_2_list_array); // JSON Array

                # STORE PRODUCT ITEM VARIANT DATA
                $variant_list = [];
                $set_default = false;
                foreach ($variant_1_list as $var_1) {
                    foreach ($variant_2_list as $var_2) {
                        $variant_name = $var_1 . ' - ' . $var_2;
                        if (!in_array($variant_name, $variant_list)) {
                            $variant_list[] = $variant_name;

                            if (!empty($request_ordinal[$key_mixed])) {
                                # UPDATE EXISTING VARIANT
                                $update_data = true;
                                $variant_id = $request_ordinal[$key_mixed];
                                $product_item_variant = $variant_data_list[$variant_id];
                                unset($variant_data_list[$variant_id]);
                            } else {
                                # CREATE NEW VARIANT
                                $update_data = false;
                                $variant_id = $key_mixed;
                                $product_item_variant = new product_item_variant();
                                $product_item_variant->product_item_id = $product_item->id;

                                // generate SKU for product_item_variant (only for new variant)
                                $sku = 'EK-' . date('Ymd') . '-' . $product_item->id . ((int) substr(time(), 0, 3) + $key_mixed);
                                $product_item_variant->sku_id = Helper::check_unique('product_item_variant', $sku, 'sku_id');
                            }

                            $product_item_variant->variant_1 = $var_1;
                            $product_item_variant->variant_2 = $var_2;
                            $product_item_variant->name = $variant_name;

                            // generate slug for product_item_variant
                            $slug = $product_item->slug . '-' . Helper::generate_slug($product_item_variant->name);

                            if ($update_data) {
                                if ($product_item_variant->slug != $slug) {
                                    # jika slug baru tidak sama dgn slug baru, maka perlu di-update

                                    // memastikan slug variant utk product item tsb unique
                                    $slug_ordinal = 2;
                                    do {
                                        // cek apakah slug variant utk product item tsb exist
                                        $slug_exist = product_item_variant::where('product_item_id', $product_item->id)->where('slug', $slug)->count();
                                        if ($slug_exist > 0) {
                                            // slug variant sudah ada, maka perlu generate slug baru
                                            $slug = $product_item->slug . '-' . Helper::generate_slug($product_item_variant->name) . '-' . $slug_ordinal;
                                            $slug_ordinal++;
                                        }
                                    } while ($slug_exist > 0);

                                    $product_item_variant->slug = $slug;
                                }
                            } else {
                                # set slug utk variant baru

                                // memastikan slug variant utk product item tsb unique
                                $slug_ordinal = 2;
                                do {
                                    // cek apakah slug variant utk product item tsb exist
                                    $slug_exist = product_item_variant::where('product_item_id', $product_item->id)->where('slug', $slug)->count();
                                    if ($slug_exist > 0) {
                                        // slug variant sudah ada, maka perlu generate slug baru
                                        $slug = $product_item->slug . '-' . Helper::generate_slug($product_item_variant->name) . '-' . $slug_ordinal;
                                        $slug_ordinal++;
                                    }
                                } while ($slug_exist > 0);
                                $product_item_variant->slug = $slug;
                            }

                            // processing variant image
                            if (isset($variant_images[$key_mixed])) {
                                if (!empty($variant_images[$key_mixed])) {
                                    # PROCESSING IMAGE FILE
                                    $dir_path = 'uploads/product_item_variant/';
                                    $image_file = $variant_images[$key_mixed];
                                    $format_image_name = $product_item->slug . '-' . $product_item_variant->slug; // cth: sepatu-kulit-hitam-42
                                    $uploaded_image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
    
                                    if ($uploaded_image['status'] != 'true') {
                                        # ERROR
                                        $error_message = lang($uploaded_image['message'], $this->translation, $uploaded_image['dynamic_objects']);
                                        if ($process_ajax) {
                                            return response()->json([
                                                'status' => 'false',
                                                'message' => $error_message
                                            ]);
                                        }
                                        return back()->withInput()->with('error', $error_message);
                                    }
    
                                    // GET THE UPLOADED IMAGE RESULT
                                    $product_item_variant->variant_image = $dir_path . $uploaded_image['data'];
                                }
                            }

                            $product_item_variant->price = (int) str_replace(',', '', $variant_prices[$key_mixed]);
                            $product_item_variant->weight = (int) str_replace(',', '', $variant_weights[$key_mixed]);

                            # IF NOT USE GLOBAL STOCK
                            if (!$global_stock) {
                                # set variant stock
                                $item_variant_stocks = (int) str_replace(',', '', $variant_stocks[$key_mixed]);

                                if ($item_variant_stocks < 1) {
                                    $qty_buyed = $product_item_variant->qty_booked + $product_item_variant->qty_sold;
                                    if ($qty_buyed < 1) { // jika belum ada qty_booked / qty sold dan qty yang di input = 0
                                        # ERROR
                                        $error_message = lang(
                                            'Invalid value for #item #description',
                                            $this->translations,
                                            [
                                                '#item' => ucwords(lang('variant stock', $this->translations)),
                                                '#description' => ' (Minimal 1)'
                                            ]
                                        );
                                        if ($process_ajax) {
                                            return response()->json([
                                                'status' => 'false',
                                                'message' => $error_message
                                            ]);
                                        }
                                        return back()
                                            ->withInput()
                                            ->with('error', $error_message);
                                    }
                                }

                                $product_item_variant->qty = $item_variant_stocks;

                                // jika new data, baru set nilai qty (booked & sold)
                                if (!$update_data) {
                                    $product_item_variant->qty_booked = 0;
                                    $product_item_variant->qty_sold = 0;
                                }
                            } else {
                                $product_item_variant->qty = NULL;
                            }

                            $product_item_variant->status = (int) $variant_status[$key_mixed];

                            # SET DEFAULT VARIANT (variant pertama yg aktif diset sbg default variant)
                            if (!$set_default && $product_item_variant->status) {
                                $set_default = true;
                                $product_item_variant->is_default = 1;
                            } else {
                                $product_item_variant->is_default = 0;
                            }

                            $key_mixed++;

                            $product_item_variant->ordinal = (int) $key_mixed;

                            $product_item_variant->save();
                        }
                    }
                }
            } else {
                # VARIANT 1 ONLY

                // pastikan data variant 2 tidak ada
                $product_item->variant_2 = null;
                $product_item->variant_2_list = null;

                # STORE PRODUCT ITEM VARIANT DATA
                $variant_list = [];
                $set_default = false;
                $variant_id = $key_mixed;
                foreach ($variant_1_list as $var_1) {
                    $variant_name = $var_1;

                    if (!empty($request_ordinal[$key_mixed])) {
                        # UPDATE EXISTING VARIANT
                        $update_data = true;
                        $variant_id = $request_ordinal[$key_mixed];
                        $product_item_variant = $variant_data_list[$variant_id];
                        unset($variant_data_list[$variant_id]);
                    } else {
                        # CREATE NEW VARIANT
                        $update_data = false;
                        $variant_id = $key_mixed;
                        $product_item_variant = new product_item_variant();
                        $product_item_variant->product_item_id = $product_item->id;

                        // generate SKU for product_item_variant (only for new variant)
                        $sku = 'EK-' . date('Ymd') . '-' . $product_item->id . ((int) substr(time(), 0, 3) + $key_mixed);
                        $product_item_variant->sku_id = Helper::check_unique('product_item_variant', $sku, 'sku_id');
                    }

                    $product_item_variant->variant_1 = $var_1;
                    $product_item_variant->variant_2 = null;
                    $product_item_variant->name = $variant_name;

                    // generate slug for product_item_variant
                    $slug = $product_item->slug . '-' . Helper::generate_slug($product_item_variant->name);

                    if ($update_data) {
                        if ($product_item_variant->slug != $slug) {
                            # jika slug baru tidak sama dgn slug baru, maka perlu di-update

                            // memastikan slug variant utk product item tsb unique
                            $slug_ordinal = 2;
                            do {
                                // cek apakah slug variant utk product item tsb exist
                                $slug_exist = product_item_variant::where('product_item_id', $product_item->id)->where('slug', $slug)->count();
                                if ($slug_exist > 0) {
                                    // slug variant sudah ada, maka perlu generate slug baru
                                    $slug = $product_item->slug . '-' . Helper::generate_slug($product_item_variant->name) . '-' . $slug_ordinal;
                                    $slug_ordinal++;
                                }
                            } while ($slug_exist > 0);

                            $product_item_variant->slug = $slug;
                        }
                    } else {
                        # set slug utk variant baru

                        // memastikan slug variant utk product item tsb unique
                        $slug_ordinal = 2;
                        do {
                            // cek apakah slug variant utk product item tsb exist
                            $slug_exist = product_item_variant::where('product_item_id', $product_item->id)->where('slug', $slug)->count();
                            if ($slug_exist > 0) {
                                // slug variant sudah ada, maka perlu generate slug baru
                                $slug = $product_item->slug . '-' . Helper::generate_slug($product_item_variant->name) . '-' . $slug_ordinal;
                                $slug_ordinal++;
                            }
                        } while ($slug_exist > 0);
                        $product_item_variant->slug = $slug;
                    }

                    // processing variant image
                    if (isset($variant_images[$key_mixed])) {
                        # PROCESSING IMAGE FILE
                        if (!empty($variant_images[$key_mixed])) {
                            $dir_path = 'uploads/product_item_variant/';
                            $image_file = $variant_images[$key_mixed];
                            $format_image_name = $product_item->slug . '-' . $product_item_variant->slug; // cth: tas-anyaman-merah
                            $uploaded_image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
    
                            if ($uploaded_image['status'] != 'true') {
                                # ERROR
                                $error_message = lang($uploaded_image['message'], $this->translation, $uploaded_image['dynamic_objects']);
                                if ($process_ajax) {
                                    return response()->json([
                                        'status' => 'false',
                                        'message' => $error_message
                                    ]);
                                }
                                return back()->withInput()->with('error', $error_message);
                            }
    
                            // GET THE UPLOADED IMAGE RESULT
                            $product_item_variant->variant_image = $dir_path . $uploaded_image['data'];
                        }
                    }

                    $product_item_variant->price = (int) str_replace(',', '', $variant_prices[$key_mixed]);
                    $product_item_variant->weight = (int) str_replace(',', '', $variant_weights[$key_mixed]);

                    # IF NOT USE GLOBAL STOCK
                    if (!$global_stock) {
                        # set variant stock
                        $item_variant_stocks = (int) str_replace(',', '', $variant_stocks[$key_mixed]);

                        if ($item_variant_stocks < 1) {
                            $qty_buyed = $product_item_variant->qty_booked + $product_item_variant->qty_sold;
                            if ($qty_buyed < 1) { // jika belum ada qty_booked / qty sold dan qty yang di input = 0
                                # ERROR
                                $error_message = lang(
                                    'Invalid value for #item #description',
                                    $this->translations,
                                    [
                                        '#item' => ucwords(lang('variant stock', $this->translations)),
                                        '#description' => ' (Minimal 1)'
                                    ]
                                );
                                if ($process_ajax) {
                                    return response()->json([
                                        'status' => 'false',
                                        'message' => $error_message
                                    ]);
                                }
                                return back()
                                    ->withInput()
                                    ->with('error', $error_message);
                            }
                        }

                        $product_item_variant->qty = $item_variant_stocks;

                        // jika new data, baru set nilai qty (booked & sold)
                        if (!$update_data) {
                            $product_item_variant->qty_booked = 0;
                            $product_item_variant->qty_sold = 0;
                        }
                    } else {
                        $product_item_variant->qty = NULL;
                    }

                    $product_item_variant->status = (int) $variant_status[$key_mixed];

                    # SET DEFAULT VARIANT (variant pertama yg aktif diset sbg default variant)
                    if (!$set_default && $product_item_variant->status) {
                        $set_default = true;
                        $product_item_variant->is_default = 1;
                    } else {
                        $product_item_variant->is_default = 0;
                    }

                    $key_mixed++;

                    $product_item_variant->ordinal = (int) $key_mixed;

                    $product_item_variant->save();
                }
            }

            $product_item->save();

            // jika ada existing variant yg tidak diproses berarti hapus datanya
            if (!empty($variant_data_list)) {
                $deleted_ids = array_keys($variant_data_list);
                product_item_variant::whereIn('id', $deleted_ids)->delete();
            }

            # Logging
            $log_detail_id  = 7; // update
            $module_id      = $this->module_id;
            $target_id      = $product_item->id;
            $note           = null;
            $value_before   = null;
            $value_after    = null; // doesn't save anything coz too much data to be saved
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after);

            DB::commit();

            # SUCCESS

            // VALIDASI APAKAH DALAM SESI PROSES ADD NEW PRODUCT ITEM
            $session_count_product_variant = 'count_product_variant_' . urlencode($product_item_id);
            if (Session::has($session_count_product_variant)) {
                // +1 VALUE
                $increment = (int) Session::get($session_count_product_variant) + 1;

                // PUT NEW SESSION
                session::forget($session_count_product_variant);
                session::put($session_count_product_variant, $increment);
            }

            $success_message = lang('Successfully input #item', $this->translations, ['#item' => $this->item]);
            if ($process_ajax) {
                $response = [
                    'status' => 'true',
                    'message' => $success_message
                ];
                return response()->json($response, 200);
            }
            return redirect()
                ->route('admin.product_variant', $raw_product_item_id)
                ->with('success', $success_message);
        } catch (\Exception $ex) {
            DB::rollback();

            $error_message = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_message, $this->module_id, null, "Failed to input product variants");

            if (env('APP_DEBUG') == false) {
                $error_message = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            if ($process_ajax) {
                return response()->json([
                    'status' => 'false',
                    'message' => $error_message
                ]);
            }
            return back()->withInput()->with('error', $error_message);
        }
    }
}
