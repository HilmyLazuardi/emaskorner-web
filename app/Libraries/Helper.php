<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;

// Models
use App\Models\admin_group;

class Helper extends TheHelper
{
    public static function authorizing($module_name, $rule_name)
    {
        // special access for "*root" as Super Administrator group
        $session_group = Session::get('sysadmin_group');
        if ($session_group) {
            foreach ($session_group as $group) {
                if ($group['group_name'] == '*root') {
                    return ['status' => 'true'];
                }
            }
        }

        if (empty($module_name) || empty($rule_name)) {
            return ['status' => 'false', 'message' => 'Sorry, you are unauthorized'];
        }

        // get access from session
        $access = Session::get('sysadmin_access');

        $granted = false;
        foreach ($access as $item) {
            if ($item->module_name == $module_name && $item->rule_name == $rule_name) {
                $granted = true;
                break;
            }
        }

        if ($granted) {
            return ['status' => 'true'];
        }

        // UNAUTHORIZED...
        return ['status' => 'false', 'message' => 'Sorry, you are unauthorized for ' . $rule_name . ' in ' . $module_name . ' module'];
    }

    public static function logging($log_detail_id, $module_id = null, $target_id = null, $note = null, $value_before = null, $value_after = null, $ip_address = null)
    {
        if (env('SYSTEM_LOG', false) == false) {
            return true;
        }

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            $log = new \App\Models\log();
            $log->admin_id = Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->id;
            $log->log_detail_id = $log_detail_id;
            $log->module_id = $module_id;
            $log->target_id = $target_id;
            $log->note = $note;
            $log->value_before = $value_before;
            $log->value_after = $value_after;
            $log->url = url()->full();
            $log->ip_address = request()->ip();
            $log->user_agent = request()->userAgent();
            $log->save();

            if (env('SYSTEM_LOG_FILE')) {
                $log_file = 'syslogs.txt';
                $is_log_file_exists = \Illuminate\Support\Facades\Storage::exists($log_file);
                if (!$is_log_file_exists) {
                    \Illuminate\Support\Facades\Storage::disk('local')->put($log_file, '');
                }
                $file_content_raw = \Illuminate\Support\Facades\Storage::get($log_file);
                if (!empty($file_content_raw)) {
                    $file_content = json_decode($file_content_raw);
                }
                $file_content[] = $log;
                $file_content_json = json_encode($file_content);
                \Illuminate\Support\Facades\Storage::disk('local')->put($log_file, $file_content_json);
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, null, null, 'System logging');

            dd('SYSTEM LOGGING ERROR: ' . $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine());
        }
    }

    public static function get_periods($translations)
    {
        return array(lang('second', $translations), lang('minute', $translations), lang('hour', $translations), lang('day', $translations), lang('week', $translations), lang('month', $translations), lang('year', $translations), lang('decade', $translations));
    }

    public static function upload_image($dir_path, $image_file, $reformat_image_name = true, $format_image_name = null, $allowed_extensions = null, $generate_thumbnail = false, $thumbnail_width = 0, $thumbnail_height = 0, $thumbnail_quality_percentage = 100)
    {
        // SET ALLOWED EXTENSIONS DEFAULT
        if (!$allowed_extensions) {
            $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
        }

        // PROCESSING IMAGE
        $destination_path = public_path($dir_path);
        $image = $image_file;
        $extension = strtolower($image->getClientOriginalExtension());

        // VALIDATING FOR ALLOWED EXTENSIONS
        if (!in_array($extension, $allowed_extensions)) {
            // FAILED
            return [
                'status' => 'false',
                'message' => 'Failed to upload image, please upload image with extensions allowed #item',
                'dynamic_objects' => ['#item' => '(' . implode("/", $allowed_extensions) . ')']
            ];
        }

        // SET IMAGE FILE NAME
        if ($reformat_image_name) {
            // REFORMAT IMAGE NAME USING $format_image_name
            if ($format_image_name) {
                $image_name = $format_image_name;
            } else {
                // REFORMAT IMAGE NAME USING TIMESTAMP
                $image_name = time();
            }
        } else {
            // USING ORIGINAL FILENAME
            $image_name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        }

        // UPLOADING...
        if (!$image->move($destination_path, $image_name . '.' . $extension)) {
            // FAILED
            return [
                'status' => 'false',
                'message' => 'Oops, failed to upload image. Please try again or try uploading another one.',
                'dynamic_objects' => []
            ];
        }

        // GENERATE IMAGE THUMBNAIL - http://image.intervention.io/api/make
        $thumbnail_name = null;
        // VALIDATE THUMBNAIL SIZE
        $thumbnail_width = (int) $thumbnail_width;
        $thumbnail_height = (int) $thumbnail_height;
        if ($generate_thumbnail && $thumbnail_width && $thumbnail_height && $extension != 'gif') {
            // VALIDATE THUMBNAIL QUALITY PERCENTAGE
            if ($thumbnail_quality_percentage > 100) {
                $thumbnail_quality_percentage = 100;
            } else if ($thumbnail_quality_percentage < 1) {
                $thumbnail_quality_percentage = 50;
            }
            // GET THE UPLOADED IMAGE RESULT
            $uploaded_image = $dir_path . $image_name . '.' . $extension;
            // SET THUMBNAIL FILENAME
            $thumbnail_name = $image_name . '-' . $thumbnail_width . 'x' . $thumbnail_height . '.' . $extension;
            try {
                // CREATE A NEW IMAGE FROM GD RESOURCE
                switch ($extension) {
                    case 'jpg':
                        $image_source = imagecreatefromjpeg(public_path($uploaded_image));
                        break;
                    case 'jpeg':
                        $image_source = imagecreatefromjpeg(public_path($uploaded_image));
                        break;
                    case 'png':
                        $image_source = imagecreatefrompng(public_path($uploaded_image));
                        break;
                    case 'gif':
                        $image_source = imagecreatefromgif(public_path($uploaded_image));
                        break;
                    default:
                        // FAILED
                        return [
                            'status' => 'false',
                            'data' => $image_name,
                            'message' => 'Successfully uploaded the image, but failed to generate thumbnail as supported formats are only #item',
                            'dynamic_objects' => ['#item' => 'jpeg/jpg/png/gif']
                        ];
                }
                // OPEN FILE A IMAGE RESOURCE
                $img_thumb = Image::make($image_source);
                // CROP THEN RESIZE TO AxB PIXEL
                $img_thumb->fit($thumbnail_width, $thumbnail_height);
                // SAVE CROPPED FILE WITH X% QUALITY
                $img_thumb->save($dir_path . $thumbnail_name, $thumbnail_quality_percentage);
                // THUMBNAIL IMAGE GENERATED SUCCESSFULLY
            } catch (\Intervention\Image\Exception\NotReadableException $e) {
                // THROWING ERROR WHEN EXCEPTION OCCURS

                Helper::error_logging($e, null, null, 'Uploading image');

                // FAILED
                return [
                    'status' => 'false',
                    'message' => $e,
                    'dynamic_objects' => []
                ];
            }
        }

        if ($extension == 'gif') {
            $thumbnail_name = $image_name . '.' . $extension;
        }

        // SUCCESS
        return [
            'status' => 'true',
            'message' => 'Successfully uploaded the image',
            'data' => $image_name  . '.' . $extension,
            'thumbnail' => $thumbnail_name
        ];
    }

    public static function check_unique($table_name, $value, $field_name = 'slug')
    {
        $unique = false;
        $no = 2;
        $value_raw = $value;
        while (!$unique) {
            $value_exist = DB::table($table_name)->where($field_name, $value)->count();
            if ($value_exist == 0) {
                $unique = true;
            } else {
                // SET NEW SLUG
                $value = $value_raw . '-' . $no;
                $no++;
            }
        }
        return $value;
    }

    public static function upload_file($dir_path, $file, $reformat_file_name = true, $format_file_name = null, $allowed_extensions = ['pdf', 'txt', 'docx', 'doc'])
    {
        // PROCESSING FILE
        $destination_path = public_path($dir_path);
        $extension = strtolower($file->getClientOriginalExtension());

        // VALIDATING FOR ALLOWED EXTENSIONS
        if (!in_array($extension, $allowed_extensions)) {
            // FAILED
            return [
                'status' => 'false',
                'message' => 'Failed to upload the file, please upload file with allowed extensions (' . implode(",", $allowed_extensions) . ')'
            ];
        }

        if ($reformat_file_name) {
            // REFORMAT FILE NAME USING $format_file_name
            if ($format_file_name) {
                $file_name = $format_file_name . '.' . $extension;
            } else {
                // REFORMAT FILE NAME USING RANDOM STRING
                $file_name = md5(uniqid()) . '.' . $extension;
            }
        } else {
            // USING ORIGINAL FILENAME
            $file_name = $file->getClientOriginalName();
        }

        // UPLOADING...
        if (!$file->move($destination_path, $file_name)) {
            // FAILED
            return [
                'status' => 'false',
                'message' => 'Oops, failed to upload file. Please try again or try upload another one.'
            ];
        }

        // SUCCESS
        return [
            'status' => 'true',
            'message' => 'Successfully uploaded the file',
            'data' => $file_name
        ];
    }

    public static function is_menu_active($word_in_url)
    {
        $actual_link = Helper::get_url();
        if (strpos($actual_link, $word_in_url) !== false) {
            // FOUND
            return true;
        }
        return false;
    }

    public static function get_avatar()
    {
        if (Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->avatar_with_path) {
            return asset(Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->avatar_with_path);
        } else {
            return asset('images/avatar.png');
        }
    }

    public static function locale_timestamp($timestamp, $format = 'D, d M Y H:i:s', $with_gmt = true)
    {
        $local_timezone = env('APP_TIMEZONE', 'UTC');

        $locale_timestamp = date($format, strtotime($timestamp->setTimezone($local_timezone)));

        if ($with_gmt) {
            $locale_timestamp .= ' (' . $local_timezone . ')';
        }

        return $locale_timestamp;
    }

    public static function current_datetime($format = 'Y-m-d H:i:s', $timezone = null)
    {
        if (!$timezone) {
            $timezone = env('APP_TIMEZONE', 'UTC');
        }

        $date = new \DateTime(null, new \DateTimeZone($timezone));
        return $date->format($format);
    }

    public static function loadEncryptionKeyFromConfig($path_key = null)
    {
        if (!$path_key) {
            $path_key = env('PHP_ENCRYPTION_PATH');
        }
        $keyAscii = file_get_contents(base_path($path_key));
        return Key::loadFromAsciiSafeString($keyAscii);
    }

    public static function encrypt($secret_data, $path_key = null)
    {
        $key = Helper::loadEncryptionKeyFromConfig($path_key);
        $ciphertext = Crypto::encrypt($secret_data, $key);
        return $ciphertext;
    }

    public static function decrypt($ciphertext, $path_key = null)
    {
        $key = Helper::loadEncryptionKeyFromConfig($path_key);
        try {
            $secret_data = Crypto::decrypt($ciphertext, $key);
        } catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
            // An attack! Either the wrong key was loaded, or the ciphertext has
            // changed since it was created -- either corrupted in the database or
            // intentionally modified by someone trying to carry out an attack.

            $error_msg = 'Failed to decrypt the data for some reasons. Maybe the wrong key was loaded, or the ciphertext has changed, or corrupted in the database, or intentionally modified by someone trying to carry out an attack';
            Helper::error_logging($error_msg, null, null, json_encode($ex));

            return $error_msg;
        }

        return $secret_data;
    }

    public static function decrypt_config($ciphertext)
    {
        if (env('SECURE_CONFIG')) {
            return Helper::decrypt($ciphertext);
        }
        return $ciphertext;
    }

    public static function error_logging($error_msg, $module_id = null, $target_id = null, $remarks = null)
    {
        if (env('ERROR_LOG', false) == false) {
            return true;
        }

        DB::beginTransaction();
        try {
            $data = new \App\Models\error_log();
            $data->url_get_error = url()->full();
            $data->url_prev = url()->previous();
            $data->err_message = $error_msg;
            if (Session::has(env('SESSION_ADMIN_NAME', 'sysadmin'))) {
                $data->admin_id = Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->id;
            }
            $data->module_id = $module_id;
            $data->target_id = $target_id;
            $data->remarks = $remarks;
            $data->ip_address = request()->ip();
            $data->user_agent = request()->userAgent();
            $data->save();

            DB::commit();

            if (env('ERROR_LOG_FILE')) {
                $log_file = 'errorlogs.txt';
                $is_log_file_exists = \Illuminate\Support\Facades\Storage::exists($log_file);
                if (!$is_log_file_exists) {
                    \Illuminate\Support\Facades\Storage::disk('local')->put($log_file, '');
                }
                $file_content_raw = \Illuminate\Support\Facades\Storage::get($log_file);
                if (!empty($file_content_raw)) {
                    $file_content = json_decode($file_content_raw);
                }
                $file_content[] = $data;
                $file_content_json = json_encode($file_content);
                \Illuminate\Support\Facades\Storage::disk('local')->put($log_file, $file_content_json);
            }
        } catch (\Exception $ex) {
            DB::rollback();

            dd('FAILED TO ERROR LOGGING: ' . $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine());
        }
    }

    public static function error_logging_api($error_msg, $module_name = null, $user_id = null, $remarks = null)
    {
        DB::beginTransaction();
        try {
            $data = new \App\Models\zeta_error_log();
            $data->url_get_error = url()->full();
            $data->module_name = $module_name;
            $data->err_message = $error_msg;
            $data->user_id = $user_id;
            $data->ip_address = request()->ip();
            $data->user_agent = request()->userAgent();
            $data->remarks = $remarks;
            $data->save();

            DB::commit();

            if (env('ERROR_LOG_FILE')) {
                $log_file = 'errorlogs_api.txt';
                $is_log_file_exists = \Illuminate\Support\Facades\Storage::exists($log_file);
                if (!$is_log_file_exists) {
                    \Illuminate\Support\Facades\Storage::disk('local')->put($log_file, '');
                }
                $file_content_raw = \Illuminate\Support\Facades\Storage::get($log_file);
                if (!empty($file_content_raw)) {
                    $file_content = json_decode($file_content_raw);
                }
                $file_content[] = $data;
                $file_content_json = json_encode($file_content);
                \Illuminate\Support\Facades\Storage::disk('local')->put($log_file, $file_content_json);
            }
        } catch (\Exception $ex) {
            DB::rollback();

            if (env('APP_DEBUG')) {
                dd('FAILED TO ERROR LOGGING API: ' . $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine());
            }
        }
    }

    public static function convert_date_to_indonesian($date) {
        $month = array (
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );

        $piece = explode('-', $date);
        
        // variabel piece 0 = tahun
        // variabel piece 1 = bulan
        // variabel piece 2 = tanggal

        // GET DAY
        $day            = date('l', strtotime($date));
        $indonesian_day = array('Monday'  => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu');
     
        return $indonesian_day[$day] . ', ' . date('d', strtotime($date)) . ' ' . substr($month[(int)$piece[1]], 0, 3) . ' ' . $piece[0];
    }

    public static function get_admin_groups()
    {
        $query = admin_group::where('status', 1);

        $authorize = Helper::authorizing('Super User', 'All Access');
        if ($authorize['status'] != 'true') {
            $query->where('name', '!=', '*root');
        }

        $data = $query->orderBy('name')->get();

        return $data;
    }

    /**
     * Convert timestamp from app timezone to server timezone
     */
    public static function server_timestamp($timestamp, $format = 'Y-m-d H:i:s')
    {
        $local_timezone = env('APP_TIMEZONE', 'UTC');
        $date = new \DateTime($timestamp, new \DateTimeZone($local_timezone));
        $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return $date->format($format);
    }
}