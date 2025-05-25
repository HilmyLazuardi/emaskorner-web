<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

use App\Exports\ProductItemExportView;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\product_item;
use App\Models\product_category;
use App\Models\product_featured;
use App\Models\product_item_variant;
use App\Models\seller;

class ProductItemController extends Controller
{
    // SET THIS MODULE
    private $module     = 'Product Item';
    private $module_id  = 23;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'product item';

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

        return view('admin.product_item.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = product_item::select(
            'product_item.id',
            'product_item.name',
            'product_item.slug',
            'product_item.summary',
            'product_item.image',
            'product_item.details',
            'product_item.qty',
            'product_item.price',
            // 'product_item.campaign_start',
            // 'product_item.campaign_end',
            'product_item.featured',
            'product_item.approval_status',
            'product_item.published_status',
            'product_item.created_at',
            'product_item.updated_at',
            'product_item.deleted_at',

            'seller.fullname as seller_name',
            'seller.store_name',

            'product_category.name as product_category_name',

            DB::raw('group_concat(DISTINCT product_item_variant.sku_id SEPARATOR ", ") as sku_variant')
        )
            ->leftJoin('product_category', 'product_item.category_id', 'product_category.id')
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->leftJoin('product_item_variant', 'product_item.id', 'product_item_variant.product_item_id')
            ->whereNull('product_item_variant.deleted_at')
            ->groupBy(
                'product_item.id',
                'product_item.name',
                'product_item.slug',
                'product_item.summary',
                'product_item.image',
                'product_item.details',
                'product_item.qty',
                'product_item.price',
                // 'product_item.campaign_start',
                // 'product_item.campaign_end',
                'product_item.featured',
                'product_item.approval_status',
                'product_item.published_status',
                'product_item.created_at',
                'product_item.updated_at',
                'product_item.deleted_at',

                'seller.fullname',
                'seller.store_name',

                'product_category.name'
            );

        // FILTER STATUS
        switch ($request->status) {
            case 'draft':
                $query->where('product_item.published_status', 0);
                $query->where('product_item.approval_status', 0);
                break;

            case 'on-review':
                $query->where('product_item.published_status', 1);
                $query->where('product_item.approval_status', 0);
                break;

            default:
                # approved
                $query->where('product_item.published_status', 1);
                $query->where('product_item.approval_status', 1);
                break;
        }

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                if ($data->featured) {
                    $wording_featured   = 'unfeatured';
                    $button_featured    = 'btn-dark';
                    $icon_featured      = 'fa-star-o';
                } else {
                    $wording_featured   = 'featured';
                    $button_featured    = 'bg-purple';
                    $icon_featured      = 'fa-star';
                }

                $word_featured = ucwords(lang($wording_featured, $this->translations));
                $html = '<form action="' . route('admin.product_item.featured') . '" method="POST" onsubmit="return confirm(\'' . lang('Apakah Anda yakin untuk membuat item ini sebagai Featured Produk?') . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-block ' . $button_featured . '" title="' . $word_featured . '"><i class="fa ' . $icon_featured . '"></i>&nbsp; ' . $word_featured . '</button></form>';

                $wording_preview = ucwords(lang('preview', $this->translations));
                $html .= '<span class="btn btn-xs btn-block btn-default" style="background:#D3A381; color:#FFFFFF;" title="' . $wording_preview . '" onclick="preview(`' . $object_id . '`);"><i class="fa fa-eye"></i>&nbsp; ' . $wording_preview . '</span>';

                $wording_edit = ucwords(lang('edit', $this->translations));
                $html .= '<a href="' . route('admin.product_item.edit', $object_id) . '" class="btn btn-xs btn-block btn-default" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $wording_variant = ucwords(lang('variants', $this->translations));
                $html .= '<a href="' . route('admin.product_variant', $object_id) . '" class="btn btn-xs btn-block btn-primary" title="' . $wording_variant . '"><i class="fa fa-th-large"></i>&nbsp; ' . $wording_variant . '</a>';

                $wording_content = ucwords(lang('description', $this->translations));
                $html .= '<a href="' . route('admin.product_content', $object_id) . '" class="btn btn-xs btn-block btn-warning" title="' . $wording_content . '"><i class="fa fa-info-circle"></i>&nbsp; ' . $wording_content . '</a>';

                $wording_faq = 'FAQ';
                $html .= '<a href="' . route('admin.product_faq', $object_id) . '" class="btn btn-xs btn-block btn-info" title="' . $wording_faq . '"><i class="fa fa-question-circle"></i>&nbsp; ' . $wording_faq . '</a>';

                $wording_delete = ucwords(lang('delete', $this->translations));
                $html .= '<hr><form action="' . route('admin.product_item.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-block btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                return $html;
            })
            ->addColumn('source_image', function ($data) {
                if (!is_null($data->image) || $data->image != '') {
                    $html = '<img src="' . env('APP_URL') . $data->image . '" height="200">';
                } else {
                    $html = '<img src="' . env('APP_URL') . 'images/no-image.png" height="200">';
                }

                return $html;
            })
            ->addColumn('seller_store_name', function ($data) {
                return $data->store_name . '<br>' . $data->seller_name;
            })
            // ->editColumn('campaign_start', function ($data) {
            //     $o = '[NOT SET]';
            //     if (!is_null($data->campaign_start)) {
            //         $o = date('Y-m-d', strtotime($data->campaign_start));
            //     }
            //     return  $o;
            // })
            // ->editColumn('campaign_end', function ($data) {
            //     $o = '[NOT SET]';
            //     if (!is_null($data->campaign_end)) {
            //         $o = date('Y-m-d', strtotime($data->campaign_end));
            //     }
            //     return  $o;
            // })
            ->editColumn('updated_at', function ($data) {
                return Helper::time_ago(strtotime($data->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->addColumn('approval', function ($data) {
                if ($data->approval_status) {
                    $html = '<span class="label label-success">' . ucwords(lang('yes', $this->translations)) . '</span>';
                } else {
                    if ($data->published_status) {
                        $html = '<span class="label label-warning">' . ucwords(lang('on review', $this->translations)) . '</span>';
                    } else {
                        $html = '<span class="label label-danger">' . ucwords(lang('no', $this->translations)) . '</span>';
                    }
                }

                return $html;
            })
            ->addColumn('published', function ($data) {
                if ($data->published_status) {
                    $html = '<span class="label label-success">' . ucwords(lang('yes', $this->translations)) . '</span>';
                } else {
                    $html = '<span class="label label-danger">' . ucwords(lang('no', $this->translations)) . '</span>';
                }

                return $html;
            })
            ->rawColumns(['action', 'status', 'source_image', 'approval', 'published', 'seller_store_name'])
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

        $categories = product_category::where('status', 1)->orderBy('ordinal')->get();

        // DEFINED DATA CATEGORIES
        $defined_data_categories = [];

        if (!empty($categories)) {
            foreach ($categories as $category) {
                $opt_select2_1              = new \stdClass();
                $opt_select2_1->id          = $category->id;
                $opt_select2_1->name        = $category->name;
                $defined_data_categories[]  = $opt_select2_1;
            }
        }

        $seller = seller::where('approval_status', 1)->where('status', 1)->get();

        // DEFINED DATA SELLER
        $defined_data_seller = [];
        $selected_seller_id = null;

        if (!empty($seller)) {
            foreach ($seller as $sell) {
                $opt_select2_1              = new \stdClass();
                $opt_select2_1->id          = $sell->id;
                $opt_select2_1->name        = $sell->store_name;
                $defined_data_seller[]  = $opt_select2_1;
            }

            // Jika hanya 1 seller, auto-select
            if ($seller->count() === 1) {
                $selected_seller_id = $seller->first()->id;
                $data['seller_id'] = $selected_seller_id;
            }
        }

        // DECLARE MULTIPLE IMAGES AS ZERO
        $multiple_images = [];
        $multiple_images = json_encode($multiple_images);

        return view('admin.product_item.form', compact('defined_data_categories', 'defined_data_seller', 'multiple_images', 'selected_seller_id'));
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
        $this->item = ucwords(lang($this->item, $this->translations));

        // LARAVEL VALIDATION
        $validation = [
            'name'              => 'required',
            'category_id'       => 'required|integer',
            'seller_id'         => 'nullable|integer',
            'image'             => 'image|mimes:jpeg,png,jpg|max:2048',
            // 'qty'               => 'required|integer',
            // 'price'             => 'required',
            // 'campaign_start'    => 'nullable',
            // 'campaign_end'      => 'nullable'
        ];
        $message = [
            'required'  => ':attribute ' . lang('should not be empty', $this->translations),
            'integer'   => ':attribute ' . lang('must be a numeric', $this->translations),
            'image'     => ':attribute ' . lang('must be an image and max size is 2MB', $this->translations)
        ];
        $names = [
            'name'              => ucwords(lang('name', $this->translations)),
            'category_id'       => ucwords(lang('category', $this->translations)),
            'seller_id'         => ucwords(lang('seller', $this->translations)),
            'image'             => ucwords(lang('image', $this->translations)),
            'qty'               => ucwords(lang('quantity', $this->translations)),
            'price'             => ucwords(lang('price', $this->translations)),
            // 'campaign_start'    => ucwords(lang('campaign start', $this->translations)),
            // 'campaign_end'      => ucwords(lang('campaign end', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW
            $multi_image = [];
            if ($request->image_2) {
                $multi_image[] = $request->image_2;
            }
            if ($request->image_3) {
                $multi_image[] = $request->image_3;
            }
            if ($request->image_4) {
                $multi_image[] = $request->image_4;
            }
            if ($request->image_5) {
                $multi_image[] = $request->image_5;
            }
            $data = new product_item();

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $name = Helper::validate_input_text($request->name);
            if (!$name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('name', $this->translations))]));
            }

            if (strlen($name) > 40) {
                return back()
                    ->withInput()
                    ->with('error', lang('Maximal character for #item is 40 chars', $this->translations, ['#item' => ucwords(lang('name', $this->translations))]));
            }

            // SLUG
            $slug = Helper::generate_slug($name);
            if ($request->slug) {
                $slug = Helper::generate_slug($request->slug);
            }

            // MAKE SURE SLUG IS UNIQUE
            $slug    = Helper::check_unique('product_item', $slug);

            $summary = Helper::validate_input_text($request->summary);

            // $seller_id = (int) $request->seller_id;
            $seller_id = (int) 1;

            // CHECK CATEGORY
            $category_id = (int) $request->category_id;
            $check_category = product_category::where('id', $category_id)->where('status', 1)->first();
            if (empty($check_category)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('category', $this->translations))]));
            }

            // PROCESSING IMAGE
            $multi_image = $request->file_photo;

            // GET CROPPED IMAGE
            $attachments = json_decode($request->attachments[0]);

            // VALIDATE ALL CROPPED IMAGE
            $count_img = 0;
            foreach ($attachments as $attachment) {
                if (!empty($attachment->src)) {
                    $count_img += 1;

                    // ALLOWED EXTENSION
                    $allowed_extension = ['png', 'jpg', 'jpeg'];
                    if (!in_array($this->get_extension($attachment->type), $allowed_extension)) {
                        // FAILED
                        return back()
                            ->withInput()
                            ->with('error', 'Ekstensi gambar yang diperbolehkan hanya PNG, JPG, dan JPEG');
                    }

                    // ALLOWED SIZE
                    $size_in_bytes = (int) (strlen(rtrim($attachment->src, '=')) * 3 / 4);
                    $size_in_kb    = $size_in_bytes / 1024;

                    if ($size_in_kb > 2048) { // MAX SIZE 2MB
                        return back()
                            ->withInput()
                            ->with('error', 'Maaf, maksimal ukuran gambar yang bisa diupload adalah sebesar 2MB');
                    }
                }
            }

            // VALIDATE TOTAL IMAGE
            if ($count_img < 3) {
                return back()->withInput()->with('error', 'Anda harus input minimal 3 gambar');
            }

            // IF PASS, UPLOAD ALL CROPPED IMAGE
            $collect_images = [];
            $counter = 0;
            foreach ($attachments as $attachment) {
                if (!empty($attachment->src)) {
                    $extension = $this->get_extension($attachment->type);
                    $dir_path = 'uploads/product_item/';

                    // FOR MULTIPLE IMAGE
                    $number = $counter + 1;
                    $format_image_name = time() . '-multi_product_item_' . $number . '.' . $extension;

                    // FOR MAIN IMAGE
                    if ($counter == 0) {
                        $format_image_name = time() . '-product_item.' . $extension;
                    }

                    // UPLOADING TO SERVER
                    $upload = $this->upload_image_base64($dir_path, $format_image_name, $attachment->src);

                    if (!is_null($upload) && $upload['status']) {
                        $obj                = new \stdClass();
                        $obj->value         = $dir_path . $upload['data'];
                        $collect_images[]   = $obj;
                    } else {
                        return back()
                            ->withInput()
                            ->with('error', $upload['message']);
                    }

                    $counter++;
                }
            }

            // FOREACH FOR EVERY FIELD
            $counter_insert = 0;
            foreach ($collect_images as $item) {
                if ($counter_insert == 0) {
                    $data->image = $item->value;
                } else {
                    $number = $counter_insert + 1;
                    $field = 'image_' . $number;
                    $data->$field = $item->value;
                }
                $counter_insert++;
            }

            // QUANTITY
            // $qty = (int) $request->qty;
            // if ($qty < 0) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('quantity', $this->translations))]));
            // }
            $qty = 0;

            // PRICE
            // $price = (int) str_replace(',', '', $request->price);
            // if ($price < 0) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('price', $this->translations))]));
            // }
            $price = 0;

            // CAMPAIGN START
            // $campaign_start = Helper::validate_input_text($request->campaign_start);
            // $campaign_start = str_replace('/', '-', $campaign_start);
            // if (!$campaign_start) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('campaign start', $this->translations))]));
            // }

            // $campaign_end = Helper::validate_input_text($request->campaign_end);
            // $campaign_end = str_replace('/', '-', $campaign_end);
            // if (!$campaign_end) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('campaign end', $this->translations))]));
            // }

            // // CUSTOM VALIDATION FOR CAMPAIGN PERIOD
            // $date_campaign_start = date('Y-m-d', strtotime($campaign_start));
            // $date_campaign_end = date('Y-m-d', strtotime($campaign_end));
            // if ($date_campaign_end <= $date_campaign_start) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('campaign end', $this->translations))]));
            // }

            // SAVE THE DATA
            $data->category_id      = $category_id;
            $data->seller_id        = $seller_id;
            $data->name             = $name;
            $data->slug             = $slug;
            $data->summary          = $summary;
            $data->qty              = $qty;
            $data->price            = $price;
            // $data->campaign_start   = date('Y-m-d', strtotime($campaign_start)) . ' 00:00:00';
            // $data->campaign_end     = date('Y-m-d', strtotime($campaign_end)) . ' 23:59:59';
            $data->need_insurance  = (int) $request->need_insurance;
            // $data->approval_status  = (int) $request->approval_status;
            $data->approval_status  = 1;
            $data->published_status = 0;
            $new_product_published  = (int) $request->published_status;
            $data->save();

            // logging
            $log_detail_id  = 5; // add new
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $name . '"';
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
            return back()
                ->withInput()
                ->with('error', $error_msg);
        }

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $object_id = Helper::generate_token($data->id);
        }

        $session_count_product_variant = 'count_product_variant_' . $object_id;

        // SET SESSION NEW PRODUCT SESSION
        session::put('new_product_published', $new_product_published);
        session::put('add_new_product', $object_id);
        session::put($session_count_product_variant, 0);

        # SUCCESS
        return redirect()->route('admin.product_variant', $object_id)->with('success', 'Silahkan tambahkan varian untuk melengkapi detail produk');
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
                ->route('admin.product_item')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = product_item::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.product_item')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // DEFINED DATA CATEGORIES
        $categories = product_category::where('status', 1)->orderBy('ordinal')->get();
        $defined_data_categories = [];
        $multiple_images = [];

        // PROCESSING MULTIPLE IMAGE DATA
        $multiple_images[] = [
            'id'         => 1, // SET ID
            'src_url'    => !empty($data->image) ? asset($data->image) : NULL, // GET IMAGE URL
            'src_base64' => !empty($data->image) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path($data->image))) : NULL, // GET IMAGE BASE64 STRING
            'type'       => !empty($data->image) ? 'image/' . substr($data->image, strpos($data->image, ".") + 1) : NULL, // GET MIME TYPE (SUBSTR FROM FILE EXTENSION)
        ];
        $multiple_images[] = [
            'id'         => 2,
            'src_url'    => !empty($data->image_2) ? asset($data->image_2) : NULL,
            'src_base64' => !empty($data->image_2) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path($data->image_2))) : NULL, // GET IMAGE BASE64 STRING
            'type'       => !empty($data->image_2) ? 'image/' . substr($data->image_2, strpos($data->image_2, ".") + 1) : NULL,
        ];
        $multiple_images[] = [
            'id'         => 3,
            'src_url'    => !empty($data->image_3) ? asset($data->image_3) : NULL,
            'src_base64' => !empty($data->image_3) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path($data->image_3))) : NULL, // GET IMAGE BASE64 STRING
            'type'       => !empty($data->image_3) ? 'image/' . substr($data->image_3, strpos($data->image_3, ".") + 1) : NULL,
        ];
        $multiple_images[] = [
            'id'         => 4,
            'src_url'    => !empty($data->image_4) ? asset($data->image_4) : NULL,
            'src_base64' => !empty($data->image_4) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path($data->image_4))) : NULL, // GET IMAGE BASE64 STRING
            'type'       => !empty($data->image_4) ? 'image/' . substr($data->image_4, strpos($data->image_4, ".") + 1) : NULL,
        ];
        $multiple_images[] = [
            'id'         => 5,
            'src_url'    => !empty($data->image_5) ? asset($data->image_5) : NULL,
            'src_base64' => !empty($data->image_5) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path($data->image_5))) : NULL, // GET IMAGE BASE64 STRING
            'type'       => !empty($data->image_5) ? 'image/' . substr($data->image_5, strpos($data->image_5, ".") + 1) : NULL,
        ];
        $multiple_images = json_encode($multiple_images);

        if (!empty($categories)) {
            foreach ($categories as $category) {
                $opt_select2_1              = new \stdClass();
                $opt_select2_1->id          = $category->id;
                $opt_select2_1->name        = $category->name;
                $defined_data_categories[]  = $opt_select2_1;
            }
        }

        // DEFINED DATA SELLER
        $seller = seller::where('approval_status', 1)->where('status', 1)->get();
        $defined_data_seller = [];

        if (!empty($seller)) {
            foreach ($seller as $sell) {
                $opt_select2_1              = new \stdClass();
                $opt_select2_1->id          = $sell->id;
                $opt_select2_1->name        = $sell->store_name;
                $defined_data_seller[]  = $opt_select2_1;
            }
        }

        return view('admin.product_item.form', compact('data', 'raw_id', 'defined_data_categories', 'defined_data_seller', 'multiple_images'));
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
        $data = product_item::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        // GET DATA ORIGIN FOR REVIEW
        $product_data = product_item::find($id);

        // LARAVEL VALIDATION
        $validation = [
            'name'              => 'required',
            'category_id'       => 'required|integer',
            'seller_id'         => 'nullable|integer',
            'image'             => 'image|mimes:jpeg,png,jpg|max:2048',
            // 'qty'               => 'required|integer',
            // 'price'             => 'required',
            'campaign_start'    => 'nullable',
            'campaign_end'      => 'nullable'
        ];
        $message = [
            'required'  => ':attribute ' . lang('should not be empty', $this->translations),
            'integer'   => ':attribute ' . lang('must be a numeric', $this->translations),
            'image'     => ':attribute ' . lang('must be an image and max size is 2MB', $this->translations)
        ];
        $names = [
            'name'              => ucwords(lang('name', $this->translations)),
            'category_id'       => ucwords(lang('category', $this->translations)),
            'seller_id'         => ucwords(lang('seller', $this->translations)),
            'image'             => ucwords(lang('image', $this->translations)),
            'qty'               => ucwords(lang('quantity', $this->translations)),
            'price'             => ucwords(lang('price', $this->translations)),
            // 'campaign_start'    => ucwords(lang('campaign start', $this->translations)),
            // 'campaign_end'      => ucwords(lang('campaign end', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $name = Helper::validate_input_text($request->name);
            if (!$name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('name', $this->translations))]));
            }

            if (strlen($name) > 40) {
                return back()
                    ->withInput()
                    ->with('error', lang('Maximal character for #item is 40 chars', $this->translations, ['#item' => ucwords(lang('name', $this->translations))]));
            }

            $summary = Helper::validate_input_text($request->summary);

            // $seller_id = (int) $request->seller_id;
            $seller_id = (int) 1;

            $seller_data = seller::find($seller_id);

            // CHECK CATEGORY
            $category_id = (int) $request->category_id;
            $check_category = product_category::where('id', $category_id)->where('status', 1)->first();
            if (empty($check_category)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('category', $this->translations))]));
            }

            // CHECK TOTAL EXISTING IMAGES
            $count_existing_images = 0;

            if ($data->image) {
                $count_existing_images += 1;
            }
            if ($data->image_2) {
                $count_existing_images += 1;
            }
            if ($data->image_3) {
                $count_existing_images += 1;
            }
            if ($data->image_4) {
                $count_existing_images += 1;
            }
            if ($data->image_5) {
                $count_existing_images += 1;
            }


            // IF THE IMAGE CHANGES
            if (!empty($request->attachments[0])) {
                // GET CROPPED IMAGE
                $attachments = json_decode($request->attachments[0]);

                // CHECK IF ATTACHMENTS IMAGE IS EMPTY
                $empty = TRUE;
                foreach ($attachments as $attachment) {
                    if (!empty($attachment->src)) {
                        $empty = FALSE;
                    }
                }

                // VALIDATE ALL CROPPED IMAGE
                $count_img = 0;
                foreach ($attachments as $attachment) {
                    if (!empty($attachment->src)) {
                        $count_img += 1;

                        // ALLOWED EXTENSION
                        $allowed_extension = ['png', 'jpg', 'jpeg'];
                        if (!in_array($this->get_extension($attachment->type), $allowed_extension)) {
                            // FAILED
                            return back()
                                ->withInput()
                                ->with('error', 'Ekstensi gambar yang diperbolehkan hanya PNG, JPG, dan JPEG');
                        }

                        // ALLOWED SIZE
                        $size_in_bytes = (int) (strlen(rtrim($attachment->src, '=')) * 3 / 4);
                        $size_in_kb    = $size_in_bytes / 1024;

                        if ($size_in_kb > 2048) { // MAX SIZE 2MB
                            return back()
                                ->withInput()
                                ->with('error', 'Maaf, maksimal ukuran gambar yang bisa diupload adalah sebesar 2MB');
                        }
                    }
                }

                // VALIDATE TOTAL IMAGE
                if ($count_img < 3) {
                    return back()->withInput()->with('error', 'Anda harus input minimal 3 gambar');
                }

                // COLLECT EXISTING IMAGES
                $existing_images = [];
                array_push($existing_images, $data->image);
                array_push($existing_images, $data->image_2);
                array_push($existing_images, $data->image_3);
                array_push($existing_images, $data->image_4);
                array_push($existing_images, $data->image_5);

                // REMOVE ALL EXISTING IMAGES
                $data->image = NULL;
                $data->image_2 = NULL;
                $data->image_3 = NULL;
                $data->image_4 = NULL;
                $data->image_5 = NULL;

                // IF PASS, UPLOAD ALL CROPPED IMAGE
                $collect_images = [];
                $counter = 0;
                foreach ($attachments as $attachment) {
                    if (!empty($attachment->src)) {
                        $extension = $this->get_extension($attachment->type);
                        $dir_path = 'uploads/product_item/';

                        // FOR MULTIPLE IMAGE
                        $number = $counter + 1;
                        $format_image_name = time() . '-multi_product_item_' . $number . '.' . $extension;

                        // FOR MAIN IMAGE
                        if ($counter == 0) {
                            $format_image_name = time() . '-product_item.' . $extension;
                        }

                        // UPLOADING TO SERVER
                        $upload = $this->upload_image_base64($dir_path, $format_image_name, $attachment->src);

                        if (!is_null($upload) && $upload['status']) {
                            $obj                = new \stdClass();
                            $obj->value         = $dir_path . $upload['data'];
                            $collect_images[]   = $obj;
                        } else {
                            return back()
                                ->withInput()
                                ->with('error', $upload['message']);
                        }

                        $counter++;
                    }

                    // FOREACH FOR EVERY FIELD
                    $counter_insert = 0;
                    foreach ($collect_images as $item) {
                        if ($counter_insert == 0) {
                            $data->image = $item->value;
                        } else {
                            $number = $counter_insert + 1;
                            $field = 'image_' . $number;
                            $data->$field = $item->value;
                        }
                        $counter_insert++;
                    }
                }
            } else {
                // VALIDATE TOTAL EXISTING IMAGE
                if ($count_existing_images < 3) {
                    return back()->withInput()->with('error', 'Anda harus input minimal 3 gambar');
                }
            }

            // IF DELETE EXISTING IMAGE
            if ($request->identity_image_delete == 'yes') {
                $data->identity_image = null;
            }

            // QUANTITY
            // $qty = (int) $request->qty;
            // if ($qty < 0) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('quantity', $this->translations))]));
            // }

            // PRICE
            // $price = (int) str_replace(',', '', $request->price);
            // if ($price < 0) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('price', $this->translations))]));
            // }

            // CAMPAIGN START
            // $campaign_start = Helper::validate_input_text($request->campaign_start);
            // $campaign_start = str_replace('/', '-', $campaign_start);
            // if (!$campaign_start) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('campaign start', $this->translations))]));
            // }

            // $campaign_end = Helper::validate_input_text($request->campaign_end);
            // $campaign_end = str_replace('/', '-', $campaign_end);
            // if (!$campaign_end) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('campaign end', $this->translations))]));
            // }

            // // CUSTOM VALIDATION FOR CAMPAIGN PERIOD
            // $date_campaign_start = date('Y-m-d', strtotime($campaign_start));
            // $date_campaign_end = date('Y-m-d', strtotime($campaign_end));
            // if ($date_campaign_end <= $date_campaign_start) {
            //     return back()
            //         ->withInput()
            //         ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('campaign end', $this->translations))]));
            // }

            // MAKE SURE SLUG IS UNIQUE (FOR UPDATE DATA)
            $slug = Helper::generate_slug($name);
            if ($request->slug) {
                $slug = Helper::generate_slug($request->slug);
            }

            // MAKE SURE SLUG IS UNIQUE
            if ($data->slug != $slug) {
                $slug = Helper::check_unique('product_item', $slug);
            }

            // SAVE THE DATA
            $data->category_id      = $category_id;
            $data->seller_id        = $seller_id;
            $data->name             = $name;
            $data->slug             = $slug;
            $data->summary          = $summary;
            // $data->qty              = $qty;
            // $data->price            = $price;
            // $data->campaign_start   = date('Y-m-d', strtotime($campaign_start)) . ' 00:00:00';
            // $data->campaign_end     = date('Y-m-d', strtotime($campaign_end)) . ' 23:59:59';
            $data->need_insurance  = (int) $request->need_insurance;
            // $data->approval_status  = (int) $request->approval_status;
            $data->approval_status  = 1;
            $data->published_status = (int) $request->published_status;
            $data->save();

            // jika produk sedang request review dan di-approved, maka send email info ke seller
            // if ($product_data->published_status == 1 && $product_data->approval_status == 0 && $data->approval_status == 1 && $data->published_status == 1) {
            //     $default_variant        = product_item_variant::where('product_item_id', $data->id)->where('is_default', 1)->first();

            //     $email                  = $seller_data->email;
            //     $email_template         = 'emails.approval_product';
            //     $this_subject           = 'Selamat, Produk Kamu Sudah Bisa Live di LokalKorner!';
            //     $campaign_mulai         = date('d M Y', strtotime($campaign_start));
            //     $campaign_berakhir      = date('d M Y', strtotime($campaign_end));

            //     $content                    = [];
            //     $content['title']           = 'Selamat, Produk Kamu Sudah Bisa Live di LokalKorner!';
            //     $content['name_store']     = $seller_data->store_name;
            //     $content['produk']          = $name;
            //     $content['price']           = $default_variant->price;
            //     $content['campaign_start']  = $campaign_mulai;
            //     $content['campaign_end']    = $campaign_berakhir;

            //     // SEND EMAIL TO SELLER
            //     Mail::send($email_template, ['data' => $content], function ($message) use ($email, $this_subject) {
            //         if (env('APP_MODE', 'STAGING') == 'STAGING') {
            //             $this_subject = '[STAGING] ' . $this_subject;
            //         }

            //         $message->subject($this_subject);
            //         $message->to($email);
            //     });
            // }

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id  = 7; // update
                $module_id      = $this->module_id;
                $target_id      = $data->id;
                $note           = '"' . $name . '"';
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
            return back()->withInput()->with('error', $error_msg);
        }

        // DELETE EXISTING IMAGES IF UPDATED
        if (isset($existing_images)) {
            try {
                foreach ($existing_images as $existing_image) {
                    if (!empty($existing_image)) {
                        $delete = $this->delete_uploaded_image($existing_image);

                        if (!is_null($delete) && $delete['status']) {
                            $obj                = new \stdClass();
                            $obj->value         = $dir_path . $delete['data'];
                            $collect_images[]   = $obj;
                        } else {
                            Helper::error_logging($delete['message'], $this->module_id, $id);

                            return back()
                                ->withInput()
                                ->with('error', $delete['message']);
                        }
                    }
                }
            } catch (\Exception $ex) {
                $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();

                if (env('APP_DEBUG') == false) {
                    $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
                }

                Helper::error_logging($error_msg, $this->module_id, $id);

                # ERROR
                return back()->withInput()->with('error', $error_msg);
            }
        }

        # SUCCESS
        $success_message = lang('Successfully updated #item : #name', $this->translations, ['#item' => $this->item, '#name' => $name]);
        if ($request->stay_on_page) {
            return redirect()->route('admin.product_item.edit', $raw_id)->with('success', $success_message);
        } else {
            return redirect()->route('admin.product_item')->with('success', $success_message);
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
        $data = product_item::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        DB::beginTransaction();
        try {
            // SET TO UNFEATURED
            $data->featured = 0;

            // DELETE FROM TABLE PRODUCT FEATURED
            product_featured::where('product_id', $data->id)->delete();

            // UPDATE THE DATA
            $data->save();

            // DELETE THE DATA
            $data->delete();

            // logging
            $log_detail_id = 8; // delete
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $data->name . '"';
            $value_after = null;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            DB::commit();

            if ($data->approval_status == 0 && $data->published_status == 0) {
                $filter_status = 'draft';
            } elseif ($data->approval_status == 0 && $data->published_status == 1) {
                $filter_status = 'on-review';
            } else {
                $filter_status = 'approved';
            }

            return redirect()
                ->route('admin.product_item', ['status' => $filter_status])
                ->with('success', lang('Successfully deleted #item', $this->translations, ['#item' => $this->item]));
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id, $id);

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return back()->withInput()->with('error', $error_msg);
        }
    }

    /**
     * Display a listing of the deleted resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleted_data()
    {
        $deleted_data = true;

        return view('admin.product_item.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        $query = product_item::select(
            'product_item.id',
            'product_item.name',
            'product_item.summary',
            'product_item.image',
            'product_item.details',
            'product_item.qty',
            'product_item.price',
            // 'product_item.campaign_start',
            // 'product_item.campaign_end',
            'product_item.featured',
            'product_item.approval_status',
            'product_item.published_status',
            'product_item.created_at',
            'product_item.updated_at',
            'product_item.deleted_at',

            'seller.fullname as seller_name',
            'seller.store_name',

            'product_category.name as product_category_name',

            DB::raw('group_concat(DISTINCT product_item_variant.sku_id SEPARATOR ", ") as sku_variant')
        )
            ->leftJoin('product_category', 'product_item.category_id', 'product_category.id')
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->leftJoin('product_item_variant', 'product_item.id', 'product_item_variant.product_item_id')
            ->groupBy(
                'product_item.id',
                'product_item.name',
                'product_item.summary',
                'product_item.image',
                'product_item.details',
                'product_item.qty',
                'product_item.price',
                // 'product_item.campaign_start',
                // 'product_item.campaign_end',
                'product_item.featured',
                'product_item.approval_status',
                'product_item.published_status',
                'product_item.created_at',
                'product_item.updated_at',
                'product_item.deleted_at',

                'seller.fullname',
                'seller.store_name',

                'product_category.name'
            )
            ->onlyTrashed();

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.product_item.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-block btn-success" title="' . $wording_restore . '"><i class="fa fa-check"></i>&nbsp; ' . $wording_restore . '</button></form>';
            })
            ->addColumn('seller_store_name', function ($data) {
                return $data->store_name . '<br>' . $data->seller_name;
            })
            ->addColumn('source_image', function ($data) {
                if (!is_null($data->image) || $data->image != '') {
                    $html = '<img src="' . env('APP_URL') . $data->image . '" height="200">';
                } else {
                    $html = '<img src="' . env('APP_URL') . 'images/no-image.png" height="200">';
                }

                return $html;
            })
            // ->editColumn('campaign_start', function ($data) {
            //     return date('Y-m-d', strtotime($data->campaign_start));
            // })
            // ->editColumn('campaign_end', function ($data) {
            //     return date('Y-m-d', strtotime($data->campaign_end));
            // })
            ->editColumn('updated_at', function ($data) {
                return Helper::time_ago(strtotime($data->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->addColumn('approval', function ($data) {
                if ($data->approval_status) {
                    $html = '<span class="label label-success">' . ucwords(lang('yes', $this->translations)) . '</span>';
                } else {
                    $html = '<span class="label label-danger">' . ucwords(lang('no', $this->translations)) . '</span>';
                }

                return $html;
            })
            ->addColumn('published', function ($data) {
                if ($data->published_status) {
                    $html = '<span class="label label-success">' . ucwords(lang('yes', $this->translations)) . '</span>';
                } else {
                    $html = '<span class="label label-danger">' . ucwords(lang('no', $this->translations)) . '</span>';
                }

                return $html;
            })
            ->editColumn('deleted_at', function ($data) {
                return Helper::locale_timestamp($data->deleted_at);
            })
            ->rawColumns(['action', 'status', 'source_image', 'approval', 'published', 'seller_store_name'])
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
        $data = product_item::onlyTrashed()->find($id);

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
            $log_detail_id = 9; // restore
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $data->name . '"';
            $value_before = null;
            $value_after = $data->toJson();
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.product_item.deleted_data')
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()
            ->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }

    /**
     * Set flag to the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function featured(Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Featured');
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
        $data = product_item::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW
            if ($data->featured == 0) {
                // CHECK, MAX 5 PRODUCTS
                $count_featured = product_featured::count();
                if ($count_featured > 4) {
                    return back()
                        ->withInput()
                        ->with('error', lang('products featured max is 5 items', $this->translations, ['#item' => $this->item]));
                }

                $data->featured = 1;

                // SET ORDER / ORDINAL
                $last                           = product_featured::select('product_id', 'ordinal')->orderBy('ordinal', 'desc')->first();
                $ordinal                        = 1;

                if ($last) {
                    // $data_featured = product_item::where('id', $last->product_id)->update(['featured' => 0]);
                    // DELETE FROM TABLE PRODUCT FEATURED BEFORE INSERT NEW
                    // $delete_data = product_featured::where('product_id', $last->product_id)->delete();
                    $ordinal = $last->ordinal + 1; 
                }

                // INSERT TO TABLE PRODUCT FEATURED
                $insert_featured                = new product_featured();
                $insert_featured->product_id    = $id;
                $insert_featured->ordinal       = $ordinal;
                $insert_featured->save();
            } else {
                $data->featured = 0;

                // DELETE FROM TABLE PRODUCT FEATURED
                $exist_data = product_featured::where('product_id', $id)->delete();
            }

            // UPDATE THE DATA
            $data->save();

            $name = $data->name;

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id  = 7; // update
                $module_id      = $this->module_id;
                $target_id      = $data->id;
                $note           = '"' . $name . '"';
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
            return back()->withInput()->with('error', $error_msg);
        }

        # SUCCESS
        $success_message = lang('Successfully updated #item : #name', $this->translations, ['#item' => $this->item, '#name' => $name]);
        return redirect()->route('admin.product_item')->with('success', $success_message);
    }

    /**
     * TO FINISH FLOW ADD NEW
     */
    public function finish($product_id)
    {
        // UPDATE STATUS APPROVAL PRODUCT
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($product_id);
        }

        $data = product_item::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()->withInput()->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // UPDATE
        $data->published_status = Session::get('new_product_published');
        $data->save();

        // CHECK SESSION BY PRODUCT ID
        $session_count_product_variant = 'count_product_variant_' . urlencode($product_id);
        if (Session::has($session_count_product_variant)) {
            // IF YES, SO FORGET ALL THIS SESSIONS
            Session::forget('new_product_published');
            Session::forget('add_new_product');
            Session::forget($session_count_product_variant);
        }

        // THEN REDIRECT TO PRODUCT LIST
        return redirect()
            ->route('admin.product_item')
            ->with('success', lang('Successfully finished add a new product'));
    }

    /**
     * AJAX FOR CHECK PRODUCT ITEM BEFORE PREVIEW THE DETAIL
     */
    public function ajax_validate_preview_item(Request $request)
    {
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($request->id);
        }

        // CHECK PRODUCT ITEM
        $product_item = product_item::find($id);
        if (empty($product_item)) {
            return response()->json([
                'status'    => false,
                'message'   => 'Produk tidak ditemukan',
                'data'      => ''
            ]);
        }

        // CHECK PRODUCT CONTENT
        if (is_null($product_item->details) || $product_item->details == '') {
            return response()->json([
                'status'    => false,
                'message'   => 'Mohon lengkapi terlebih dahulu deskripsi produk',
                'data'      => ''
            ]);
        }

        // CHECK PRODUCT VARIANT
        $product_variant = product_item_variant::where('product_item_id', $id);

        $count_product_variant = $product_variant->count();
        if ($count_product_variant < 1) {
            return response()->json([
                'status'    => false,
                'message'   => 'Mohon buat varian produk terlebih dahulu',
                'data'      => ''
            ]);
        }

        $product_variant = $product_variant->where('is_default', 1)->first();

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil, data sudah lengkap',
            'data'      => route('web.product.detail', $product_variant->slug) . '?preview=' . time()
        ]);
    }

    public function export(Request $request)
    {
        $query = product_item::select(
            'product_item.id',
            'product_item.name',
            'product_item.slug',
            'product_item.summary',
            'product_item.image',
            'product_item.details',
            'product_item.qty',
            'product_item.price',
            // 'product_item.campaign_start',
            // 'product_item.campaign_end',
            'product_item.featured',
            'product_item.approval_status',
            'product_item.published_status',
            'product_item.created_at',
            'product_item.updated_at',
            'product_item.deleted_at',

            'seller.fullname as seller_name',
            'seller.store_name',

            'product_category.name as product_category_name',

            DB::raw('group_concat(DISTINCT product_item_variant.sku_id SEPARATOR ", ") as sku_variant')
        )
            ->leftJoin('product_category', 'product_item.category_id', 'product_category.id')
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->leftJoin('product_item_variant', 'product_item.id', 'product_item_variant.product_item_id')
            ->groupBy(
                'product_item.id',
                'product_item.name',
                'product_item.slug',
                'product_item.summary',
                'product_item.image',
                'product_item.details',
                'product_item.qty',
                'product_item.price',
                // 'product_item.campaign_start',
                // 'product_item.campaign_end',
                'product_item.featured',
                'product_item.approval_status',
                'product_item.published_status',
                'product_item.created_at',
                'product_item.updated_at',
                'product_item.deleted_at',

                'seller.fullname',
                'seller.store_name',

                'product_category.name'
            );

        if (!empty($request->status) && $request->status == 1) {
            $query->where('product_item.published_status', 0);
            $query->where('product_item.approval_status', 0);
        } elseif (!empty($request->status) && $request->status == 2) {
            $query->where('product_item.published_status', 1);
            $query->where('product_item.approval_status', 0);
        } else {
            $query->where('product_item.published_status', 1);
            $query->where('product_item.approval_status', 1);
        }

        $data = $query->get();

        foreach ($data as $item) {
            $item->source_image = env('APP_URL') . 'images/no-image.png';
            if (!is_null($item->image) || $item->image != '') {
                $item->source_image = env('APP_URL') . $item->image;
            }

            $item->seller_store_name = $item->store_name . '-' . $item->seller_name;

            // $item->campaign_start = date('Y-m-d', strtotime($item->campaign_start));
            // $item->campaign_end = date('Y-m-d', strtotime($item->campaign_end));

            if ($item->approval_status) {
                $item->approval = 'yes';
            } else {
                if ($data->published_status) {
                    $item->approval = 'on review';
                } else {
                    $item->approval = 'no';
                }
            }

            $item->published = 'no';
            if ($item->published_status) {
                $item->published = 'yes';
            }
        }

        $export_data = new \stdClass();
        $export_data->data = $data;

        // SET FILE NAME
        $filename = $this->global_config->app_name . '-export-product_item';

        return Excel::download(new ProductItemExportView($export_data), $filename . '.xlsx');
    }

    private function upload_image_base64($dir_path, $image_name, $base_64_image)
    {
        try {
            //PROCESSING IMAGE
            file_put_contents(public_path($dir_path . $image_name), file_get_contents($base_64_image));

            return array(
                'status'  => true,
                'message' => 'Successfully uploaded the image',
                'data'    => $image_name
            );
        } catch (\Exception $ex) {
            $error_msg = 'Oops! Something went wrong. Please try again later.';
            if (env('APP_DEBUG', FALSE)) {
                $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            }

            # ERROR
            return array(
                'status'  => false,
                'message' => $error_msg,
                'data'    => ''
            );
        }
    }

    private function get_extension($mime)
    {
        // GET EXTENSION FROM MIME TYPE
        $extension_tmp = explode('/', $mime);
        $extension = strtolower($extension_tmp[1]);

        return $extension;
    }

    private function delete_uploaded_image($image_name)
    {
        try {
            // PRODUCT ITEM IAMGE DIR
            $dir        = public_path('/');
            $file       = $dir . $image_name;

            // CHECK FILE IS EXIST
            if (!file_exists($file)) {
                return array(
                    'status'    => false,
                    'message'   => 'File tidak ditemukan',
                    'data'      => ''
                );
            }

            // DELETE FILE
            File::delete($file);

            return array(
                'status'  => true,
                'message' => 'Successfully delete the image',
                'data'    => ''
            );
        } catch (\Exception $ex) {
            $error_msg = 'Oops! Something went wrong. Please try again later.';
            if (env('APP_DEBUG', FALSE)) {
                $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            }

            # ERROR
            return array(
                'status'  => false,
                'message' => $error_msg,
                'data'    => ''
            );
        }
    }
}
