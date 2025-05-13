<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

// MAIL
use App\Mail\MailTester;

// Libraries
use App\Libraries\Helper;
use App\Libraries\Jne;
use App\Libraries\Anteraja;

use App\Models\faq;
use App\Models\order;

class DevController extends Controller
{
    public function sandbox()
    {
        // GET DATA FOR EMAIL
            $expired_plus_seven     = env('SELLER_EMAIL_EXPIRED', 86400) + 25200; // PLUS 7

            $expired_at = date('Y-m-d H:i:s', time() + $expired_plus_seven);
            $tes = date('l', strtotime($expired_at));
            $expired_date           = Helper::convert_date_to_indonesian(date('Y-m-d', strtotime($expired_at))) . ' Pukul ' . date('H:i', strtotime($expired_at)) . ' WIB';
        // $origin_code = '32.12.10';
        // $origin_code = '31.73.02';
        // $tariff_code = '31.74.07';
        // $weight = 1;
        // $check_tariff = Anteraja::check_tariff($origin_code, $tariff_code, $weight);
        // dd($check_tariff);

        // // $origin = 'CGK10403'; // Pademangan, Jakarta Utara 14410
        // $origin = 'CGK10000'; // Jakarta
        // $destination = 'BKI10042'; // Kaliabang Tengah, Bekasi Utara 17125
        // $weight = 1;
        // $data = Jne::check_tariff($origin, $destination, $weight);

        dd($expired_date);
    }

    public function cheatsheet_form()
    {
        return view('admin.core.dev.cheatsheet_form');
    }

    public function custom_pages($name)
    {
        $preview = true;
        if ($name == 'login') {
            return view('admin.core.login', compact('preview'));
        } else {
            return view('errors.' . $name, compact('preview'));
        }
    }

    public function encrypt(Request $request)
    {
        if ($request->isMethod('post')) {
            $dir_path = 'uploads/tmp/';
            $file = $request->file('key');
            $uploaded_file = Helper::upload_file($dir_path, $file, true, null, ['txt']);
            if ($uploaded_file['status'] == 'false') {
                return back()
                    ->withInput()
                    ->with('error', $uploaded_file['message']);
            }
            $uploaded_file_name = $uploaded_file['data'];

            $result = Helper::encrypt($request->string, 'public/' . $dir_path . $uploaded_file_name);

            // remove the uploaded key file
            $uploaded_file_path = public_path($dir_path . $uploaded_file_name);
            if (file_exists($uploaded_file_path)) {
                unlink($uploaded_file_path);
            }

            $data = new \stdClass();
            $data->string = $request->string;
            $data->result = $result;

            return view('admin.core.dev.encrypt', compact('data'));
        } else {
            return view('admin.core.dev.encrypt');
        }
    }

    public function decrypt(Request $request)
    {
        if ($request->isMethod('post')) {
            $dir_path = 'uploads/tmp/';
            $file = $request->file('key');
            $uploaded_file = Helper::upload_file($dir_path, $file, true, null, ['txt']);
            if ($uploaded_file['status'] == 'false') {
                return back()
                    ->withInput()
                    ->with('error', $uploaded_file['message']);
            }
            $uploaded_file_name = $uploaded_file['data'];

            $result = Helper::decrypt($request->string, 'public/' . $dir_path . $uploaded_file_name);

            // remove the uploaded key file
            $uploaded_file_path = public_path($dir_path . $uploaded_file_name);
            if (file_exists($uploaded_file_path)) {
                unlink($uploaded_file_path);
            }

            $data = new \stdClass();
            $data->string = $request->string;
            $data->result = $result;

            return view('admin.core.dev.decrypt', compact('data'));
        } else {
            return view('admin.core.dev.decrypt');
        }
    }

    /**
     * EMAIL
     */
    public function email_send(Request $request)
    {
        // SET THE DATA
        $data = \App\Models\admin::first();

        // SET EMAIL SUBJECT
        $subject_email = 'Test Send Email from ' . env('APP_NAME');

        $email_address = $request->email;
        if ($request->send && !$email_address) {
            return 'Must set email as recipient in param email';
        }

        try {
            // SEND EMAIL
            if ($request->send) {
                // send email using SMTP
                Mail::to($email_address)->send(new MailTester($data, $subject_email));
            } else {
                // rendering email in browser
                return (new MailTester($data, $subject_email))->render();
            }
        } catch (\Exception $e) {
            // Debug via $e->getMessage();
            dd($e->getMessage());
            // return "We've got errors!";
        }

        return 'Successfully sent email to ' . $email_address;
    }

    public function nav_menu_structure()
    {
        $json = file_get_contents(public_path('admin/json/nav_menu_structure.json'));
        $data = json_decode($json);
        dd($data);
    }

    public function product_migration()
    {
        $now = Helper::current_datetime();

        // GET PRODUCT ITEM DATA
        $product_item = DB::table('product_item')
            ->whereNull('variant_1')
            ->get();

        if (empty($product_item[0])) {
            return 'Tidak ada product item yang perlu migrasi';
        }

        DB::beginTransaction();
        try {
            // UPDATING PRODUCT ITEM
            $count_product_item = 0;
            $count_product_item_variant = 0;
            foreach ($product_item as $item) {
                // GET SELECTED PRODUCT ITEM VARIANT
                $product_item_variant = DB::table('product_item_variant')
                    ->where('product_item_id', $item->id)
                    ->get();

                // UPDATE PRODUCT VARIANT
                $name_variant_arr = [];
                foreach ($product_item_variant as $key => $variant) {
                    array_push($name_variant_arr, $variant->name);

                    $is_default = 0;
                    if ($key == 0) {
                        $is_default = 1;
                    }

                    $slug_variant = Helper::generate_slug($variant->name);

                    $update_product_item_variant = DB::table('product_item_variant')
                        ->where('id', $variant->id)
                        ->update([
                            'price' => $item->price,
                            'variant_1' => $variant->name,
                            'slug' => $item->slug . '-' . $slug_variant,
                            'is_default' => $is_default,
                            'updated_at' => $now
                        ]);

                    if ($update_product_item_variant) {
                        $count_product_item_variant += 1;
                    }
                }
                
                if (!empty($name_variant_arr)) {
                    // UPDATE PRODUCT ITEM
                    $update_product_item = DB::table('product_item')
                        ->where('id', $item->id)
                        ->update([
                            'variant_1' => 'Variant 1',
                            'variant_1_list' => json_encode($name_variant_arr),
                            'updated_at' => $now
                        ]);

                    if ($update_product_item) {
                        $count_product_item += 1;
                    }
                }
            }
            
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return $error_msg;
        }
        
        return 'Berhasil update ' . $count_product_item . ' data product item dan ' . $count_product_item_variant . ' data product item variant.';
    }

    public function order_migration()
    {
        // GET ORDER DATA
        $order = DB::table('order')
            ->whereNull('invoice_id')
            ->whereIn('progress_status', [1, 4]) // MENUNGGU PEMBAYARAN ATAU CANCEL
            ->get();

        if (empty($order[0])) {
            return 'Tidak ada data order yang perlu migrasi';
        }

        // dd($order);

        DB::beginTransaction();
        try {
            $count_order_affected = 0;
            $count_new_invoice = 0;
            foreach ($order as $item) {
                $check_invoices = DB::table('invoices')->select('id')
                    ->where('invoice_no', $item->transaction_id)
                    ->where('buyer_id', $item->buyer_id)
                    ->where('payment_url', $item->payment_url)
                    ->where('subtotal', $item->price_subtotal)
                    ->where('shipping_fee', $item->price_shipping)
                    ->where('total_amount', $item->price_total)
                    ->first();

                if (!empty($check_invoices)) {
                    continue; // JIKA INVOICE DARI DATA LAMA SUDAH ADA, SKIP CURRENT LOOPING
                }

                // CREATE NEW INVOICE
                $is_cancelled = 0;
                if ($item->progress_status == 4) {
                    $is_cancelled = 1;
                }

                $new_invoice_id = DB::table('invoices')->insertGetId([
                    'invoice_no'             => $item->transaction_id,
                    'buyer_id'               => $item->buyer_id,
                    'subtotal'               => $item->price_subtotal,
                    'shipping_fee'           => $item->price_shipping,
                    'shipping_insurance_fee' => empty($item->insurance_shipping_fee) ? 0 : $item->insurance_shipping_fee,
                    'discount_amount'        => 0,
                    'total_amount'           => $item->price_total,
                    'voucher_id'             => NULL,
                    'voucher_code'           => NULL,
                    'voucher_type'           => NULL,
                    'payment_url'            => $item->payment_url,
                    'payment_result_id'      => $item->payment_result_id,
                    'payment_method'         => $item->payment_method,
                    'payment_channel'        => $item->payment_channel,
                    'payment_remarks'        => $item->payment_remarks,
                    'paid_at'                => $item->paid_at,
                    'payment_status'         => $item->payment_status,
                    'is_cancelled'           => $is_cancelled,
                    'is_refunded'            => 0,
                    'expired_at'             => $item->expired_at,
                    'created_at'             => $item->created_at,
                    'updated_at'             => $item->updated_at
                ]);

                if ($new_invoice_id > 0) {
                    $count_new_invoice += 1;

                    // ASSIGN NEW INVOICE TO ORDER DATA
                    $update_order = DB::table('order')
                        ->where('id', $item->id)
                        ->update([
                            'invoice_id' => $new_invoice_id
                        ]);

                    if ($update_order) {
                        $count_order_affected += 1;
                    }
                }
            }
            
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return $error_msg;
        }
        
        return 'Berhasil update ' . $count_order_affected . ' data order dan menambah ' . $count_new_invoice . ' data invoice.';
    }

    public function ordinal_migration()
    {
        dd('disabled');
        $product_items = DB::table('product_item')->get();

        foreach ($product_items as $product_item) {
            $product_item_variants = DB::table('product_item_variant')
                ->where('product_item_id', $product_item->id)
                ->get();

            $ordinal = 1;
            foreach ($product_item_variants as $product_item_variant) {
                $update_ordinal = DB::table('product_item_variant')
                    ->where('id', $product_item_variant->id)
                    ->update([
                        'ordinal' => $ordinal
                    ]);

                $ordinal++;
            }
        }

        return 'OK';
    }
}
