<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\admin_notification;

class CronController extends Controller
{
    public function reminder()
    {
        DB::beginTransaction();
        try {
            // DB query here

            DB::commit();

            return 'OK';
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, null, null, 'CRON Job');

            # ERROR
            return $error_msg;
        }
    }
}
