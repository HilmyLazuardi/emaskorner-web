<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 

// Libraries
use App\Libraries\Helper;

class ApiController extends Controller
{
    public function index()
    {
        return '[API] ' . $this->global_config->meta_title;
    }

    public function upload_image(Request $request)
    {
        // CHECK ISSET TOKEN
        if (!$request->token) {
            return response()->json([
                'status'    => false,
                'message'   => 'Authentication failed',
                'data'      => ''
            ]);
        }

        // CHECK TOKEN
        if ($request->token != env('AUTH_TOKEN_SELLER_DASHBOARD')) {
            return response()->json([
                'status'    => false,
                'message'   => 'Token invalid',
                'data'      => ''
            ]);
        }

        // PROCESSING IMAGE
        $dir_path           = $request->dir_path;
        $image_file         = $request->image;
        $format_image_name  = $request->name;
        $upload_image       = Helper::upload_image($dir_path, $image_file, true, $format_image_name);

        if ($upload_image['status'] == 'false') {
            $error_message = lang($upload_image['message'], $this->translations, $upload_image['dynamic_objects']);
            return response()->json([
                'status'    => $upload_image['status'],
                'message'   => $error_message
            ]);
        }

        return response()->json([
            'status'    => $upload_image['status'],
            'message'   => $upload_image['message'],
            'data'      => $upload_image['data']
        ]);
    }

    public function upload_image_base64(Request $request)
    {
        // CHECK ISSET TOKEN
        if (!$request->token) {
            return response()->json([
                'status'    => false,
                'message'   => 'Authentication failed',
                'data'      => ''
            ]);
        }

        // CHECK TOKEN
        if ($request->token != env('AUTH_TOKEN_SELLER_DASHBOARD')) {
            return response()->json([
                'status'    => false,
                'message'   => 'Token invalid',
                'data'      => ''
            ]);
        }

        try {
            // GET REQUEST DATA
            $dir_path        = $request->dir_path;
            $image_name      = $request->image_name;
            $base_64_image   = $request->base_64_image;

            //PROCESSING IMAGE
            file_put_contents(public_path($dir_path.$image_name), file_get_contents($base_64_image));

            return response()->json([
                'status'  => true,
                'message' => 'Successfully uploaded the image',
                'data'    => $image_name
            ]);
        } catch (\Exception $ex) {
            $error_msg = 'Oops! Something went wrong. Please try again later.';
            if (env('APP_DEBUG', FALSE)) {
                $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            }

            # ERROR
            return response()->json([
                'status'  => false,
                'message' => $error_msg,
                'data'    => ''
            ]);
        }
    }

    public function delete_uploaded_image(Request $request)
    {
        // CHECK ISSET TOKEN
        if (!$request->token) {
            return response()->json([
                'status'    => false,
                'message'   => 'Authentication failed',
                'data'      => ''
            ]);
        }

        // CHECK TOKEN
        if ($request->token != env('AUTH_TOKEN_SELLER_DASHBOARD')) {
            return response()->json([
                'status'    => false,
                'message'   => 'Token invalid',
                'data'      => ''
            ]);
        }

        try {
            // GET REQUEST DATA
            $image_name = $request->image_name;

            // PRODUCT ITEM IAMGE DIR
            $dir        = public_path('/');
            $file       = $dir . $image_name;

            // CHECK FILE IS EXIST
            if (!file_exists($file)) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'File tidak ditemukan',
                    'data'      => ''
                ]);
            }

            // DELETE FILE
            File::delete($file);

            return response()->json([
                'status'  => true,
                'message' => 'Successfully delete the image',
                'data'    => ''
            ]);
        } catch (\Exception $ex) {
            $error_msg = 'Oops! Something went wrong. Please try again later.';
            if (env('APP_DEBUG', FALSE)) {
                $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            }

            # ERROR
            return response()->json([
                'status'  => false,
                'message' => $error_msg,
                'data'    => ''
            ]);
        }
    }
}