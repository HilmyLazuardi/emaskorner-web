<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Support\Facades\DB;

// LIBRARIES
use App\Libraries\Helper;

HeadingRowFormatter::default('none');

// MODELS
use App\Models\config;

class ConfigImport implements ToModel, WithHeadingRow
{
    private $module_id = 10;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        DB::beginTransaction();
        try {
            $data = config::first();
            $data->app_name = $row['App Name'];
            $data->app_version = $row['App Version'];
            $data->app_copyright_year = $row['App Copyright Year'];
            $data->app_url_site = $row['App URL'];
            $data->app_url_main = $row['Main App URL'];
            $data->app_info = $row['App Info'];
            $data->powered_by = $row['App Powered By'];
            $data->powered_by_url = $row['App Powered By URL'];
            $data->meta_title = $row['Meta Title'];
            $data->meta_description = $row['Meta Description'];
            $data->meta_keywords = $row['Meta Keywords'];
            $data->meta_author = $row['Meta Author'];
            $data->og_type = $row['Open Graph Type'];
            $data->og_site_name = $row['Open Graph Site Name'];
            $data->og_title = $row['Open Graph Title'];
            $data->og_description = $row['Open Graph Description'];
            $data->twitter_card = $row['Open Graph Twitter Card'];
            $data->twitter_site = $row['Open Graph Twitter Site'];
            $data->twitter_site_id = $row['Open Graph Twitter Site ID'];
            $data->fb_app_id = $row['Open Graph FB App ID'];
            $data->recaptcha_site_key_admin = $row['Site Key (admin)'];
            $data->recaptcha_secret_key_admin = $row['Secret Key (admin)'];
            $data->recaptcha_site_key_public = $row['Site Key (public)'];
            $data->recaptcha_secret_key_public = $row['Secret Key (public)'];
            $data->secure_login = $row['Secure Login'];
            $data->login_trial = $row['Login Trial Limit'];
            $data->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id, null, 'Import App Config');

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            dd($error_msg);
        }
    }

    /**
     * Heading row on different row
     * In case your heading row is not on the first row, you can easily specify this.
     * The 2nd row will now be used as heading row.
     */
    public function headingRow(): int
    {
        return 1;
    }
}
