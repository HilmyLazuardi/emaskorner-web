<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Jfcherng\Diff\DiffHelper;
use Jfcherng\Diff\Factory\RendererFactory;
use Jfcherng\Diff\Renderer\RendererConstant;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\module;
use App\Models\log;
use App\Models\log_detail;
use App\Models\admin;

class SystemLogController extends Controller
{
    // SET THIS MODULE
    private $module = 'System Logs';
    private $module_id = '11';

    // SET THIS OBJECT/ITEM NAME
    private $item = 'system log';

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

        return view('admin.core.system_logs.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        // get table system name
        $table_log = (new log())->getTable();
        $table_log_detail = (new log_detail())->getTable();
        $table_module = (new module())->getTable();
        $table_admin = (new admin())->getTable();

        $query = log::select(
            $table_log . '.*',
            $table_log_detail . '.action',
            $table_module . '.name AS module_name',
            $table_admin . '.username'
        )
            ->leftJoin($table_log_detail, $table_log . '.log_detail_id', '=', $table_log_detail . '.id')
            ->leftJoin($table_module, $table_log . '.module_id', '=', $table_module . '.id')
            ->leftJoin($table_admin, $table_log . '.admin_id', '=', $table_admin . '.id');

        return $datatables->eloquent($query)
            ->addColumn('timestamp', function ($data) {
                return Helper::locale_timestamp($data->created_at) . '<br>' . Helper::time_ago(strtotime($data->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->addColumn('activity', function ($data) {
                $arr_log_label = [];
                $arr_log_label[] = $data->action;
                if ($data->module_name) {
                    $arr_log_label[] = $data->module_name;
                }
                if ($data->note) {
                    $arr_log_label[] = $data->note;
                }

                return implode(' ', $arr_log_label);
            })
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('view', $this->translations));
                $html = '<a href="' . route('admin.system_logs.view', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-eye"></i>&nbsp; ' . $wording_edit . '</a>';

                return $html;
            })
            ->rawColumns(['timestamp', 'activity', 'action'])
            ->toJson();
    }

    /**
     * View details of the specified resource.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function view($id, Request $request)
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
                ->route('admin.system_logs')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // get table system name
        $table_log = (new log())->getTable();
        $table_log_detail = (new log_detail())->getTable();
        $table_module = (new module())->getTable();
        $table_admin = (new admin())->getTable();

        // GET DATA BY ID
        $data = log::select(
            $table_log . '.*',
            $table_log_detail . '.action',
            $table_module . '.name AS module_name',
            $table_admin . '.username'
        )
            ->leftJoin($table_log_detail, $table_log . '.log_detail_id', '=', $table_log_detail . '.id')
            ->leftJoin($table_module, $table_log . '.module_id', '=', $table_module . '.id')
            ->leftJoin($table_admin, $table_log . '.admin_id', '=', $table_admin . '.id')
            ->where($table_log . '.id', $id)
            ->first();

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.system_logs')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // manipulate the data
        $arr_log_label = [];
        $arr_log_label[] = $data->action;
        if ($data->module_name) {
            $arr_log_label[] = $data->module_name;
        }
        if ($data->note) {
            $arr_log_label[] = str_replace('"', "'", $data->note);
        }
        $data->activity = implode(' ', $arr_log_label);

        $data->value_after_raw = $data->value_after;

        /**
         * GET DIFF VALUE - BEGIN
         */
        if ($data->value_before || $data->value_after) {
            $old = '';
            if ($data->value_before) {
                $old = $data->value_before;
                // manipulate JSON to string
                $value_before = json_decode($data->value_before);
                if (is_object($value_before)) {
                    foreach ($value_before as $key => $value) {
                        if (is_array($value)) {
                            $value_string = json_encode($value);
                        } else {
                            $value_string = '"' . $value . '"';
                            if ($value == null) {
                                $value_string = 'null';
                            } elseif (is_numeric($value)) {
                                $value_string = $value;
                            }
                        }
                        $value_before_string[] = '"' . $key . '": ' . $value_string;
                    }
                    $old = "{\n" . implode(",\n", $value_before_string) . "\n}";
                }
            }

            $new = '';
            if ($data->value_after) {
                $new = $data->value_after;
                // manipulate JSON to string
                $value_after = json_decode($data->value_after);
                if (is_object($value_after)) {
                    foreach ($value_after as $key => $value) {
                        if (is_array($value)) {
                            $value_string = json_encode($value);
                        } else {
                            $value_string = '"' . $value . '"';
                            if ($value == null) {
                                $value_string = 'null';
                            } elseif (is_numeric($value)) {
                                $value_string = $value;
                            }
                        }
                        $value_after_string[] = '"' . $key . '": ' . $value_string;
                    }
                    $new = "{\n" . implode(",\n", $value_after_string) . "\n}";
                }
            }

            // renderer class name:
            //     Text renderers: Context, JsonText, Unified
            //     HTML renderers: Combined, Inline, JsonHtml, SideBySide
            $rendererName = 'SideBySide';

            // the Diff class options
            $differOptions = [
                // show how many neighbor lines
                // Differ::CONTEXT_ALL can be used to show the whole file
                'context' => 3,
                // ignore case difference
                'ignoreCase' => false,
                // ignore whitespace difference
                'ignoreWhitespace' => false,
            ];

            // the renderer class options
            $rendererOptions = [
                // how detailed the rendered HTML in-line diff is? (none, line, word, char)
                'detailLevel' => 'char',
                // renderer language: eng, cht, chs, jpn, ...
                // or an array which has the same keys with a language file
                'language' => 'eng',
                // show line numbers in HTML renderers
                'lineNumbers' => true,
                // show a separator between different diff hunks in HTML renderers
                'separateBlock' => true,
                // show the (table) header
                'showHeader' => true,
                // the frontend HTML could use CSS "white-space: pre;" to visualize consecutive whitespaces
                // but if you want to visualize them in the backend with "&nbsp;", you can set this to true
                'spacesToNbsp' => false,
                // HTML renderer tab width (negative = do not convert into spaces)
                'tabSize' => 4,
                // this option is currently only for the Combined renderer.
                // it determines whether a replace-type block should be merged or not
                // depending on the content changed ratio, which values between 0 and 1.
                'mergeThreshold' => 0.8,
                // this option is currently only for the Unified and the Context renderers.
                // RendererConstant::CLI_COLOR_AUTO = colorize the output if possible (default)
                // RendererConstant::CLI_COLOR_ENABLE = force to colorize the output
                // RendererConstant::CLI_COLOR_DISABLE = force not to colorize the output
                'cliColorization' => RendererConstant::CLI_COLOR_AUTO,
                // this option is currently only for the Json renderer.
                // internally, ops (tags) are all int type but this is not good for human reading.
                // set this to "true" to convert them into string form before outputting.
                'outputTagAsString' => false,
                // this option is currently only for the Json renderer.
                // it controls how the output JSON is formatted.
                // see available options on https://www.php.net/manual/en/function.json-encode.php
                'jsonEncodeFlags' => \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,
                // this option is currently effective when the "detailLevel" is "word"
                // characters listed in this array can be used to make diff segments into a whole
                // for example, making "<del>good</del>-<del>looking</del>" into "<del>good-looking</del>"
                // this should bring better readability but set this to empty array if you do not want it
                'wordGlues' => [' ', '-'],
                // change this value to a string as the returned diff if the two input strings are identical
                'resultForIdenticals' => null,
                // extra HTML classes added to the DOM of the diff container
                'wrapperClasses' => ['diff-wrapper'],
            ];

            // one-line simply compare two strings
            // $result = DiffHelper::calculate($old, $new, $rendererName, $differOptions, $rendererOptions);
            // use the JSON result to render in HTML
            $jsonResult = DiffHelper::calculate($old, $new, 'Json'); // may store the JSON result in your database
            $htmlRenderer = RendererFactory::make($rendererName, $rendererOptions);
            $result = $htmlRenderer->renderArray(json_decode($jsonResult, true));
        } else {
            $result = '';
        }

        // get diff css
        $diff_css = \Jfcherng\Diff\DiffHelper::getStyleSheet();
        /**
         * GET DIFF VALUE - END
         */

        return view('admin.core.system_logs.details', compact('data', 'raw_id', 'diff_css', 'result'));
    }
}
