<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;
use Validator;

// LIBRARIES
use App\Libraries\Helper;
use App\Libraries\HelperWeb;

// MODELS
use App\Models\buyer;
use App\Models\buyer_email;
use App\Models\buyer_provider;
use App\Models\buyer_forgetpass;
use App\Models\buyer_change_email;

class AuthController extends Controller
{
    protected $providers = ['google', 'facebook'];

    /**
     * REGISTER PAGE FOR BUYER
     */
    public function register()
    {
        // IF BUYER ALREADY LOGGED IN
        if (Session::get('buyer')) {
            return redirect()->route('web.home');
        }
        $navigation_menu    = HelperWeb::get_nav_menu();

        return view('web.auth.register', compact('navigation_menu'));
    }

    /**
     * PROCESSING BUYER REGISTER
     */
    public function register_submit(Request $request)
    {
        // IF BUYER ALREADY LOGGED IN
        if (Session::get('buyer')) {
            return redirect()->route('web.home');
        }

        $validation = [
            'email' => 'required|unique:buyer,email'
        ];

        if (!$request->provider) {
            $validation['password'] = [
                'required',
                'string',
                'min:8',                                // must be at least 8 characters in length
                'regex:/[a-z]/',                        // must contain at least one lowercase letter
                'regex:/[A-Z]/',                        // must contain at least one uppercase letter
                'regex:/[0-9]/',                        // must contain at least one digit numeric
                'regex:/[?!@#$%^&*~`_+=:;.,"><\'-]/',   // must contain a special character
            ];

            // $validation['agreement'] = 'required';
        }

        $message    = [
            'required'      => 'Wajib diisi',
            'unique'        => ':attribute ' . 'sudah pernah digunakan, silahkan input dengan data lain',
            'min'           => 'Min. 8 karakter, kombinasi huruf kapital, angka, & simbol',
            'regex'         => 'Min. 8 karakter, kombinasi huruf kapital, angka, & simbol'
        ];

        $names      = [
            'email'         => 'Email',
            'password'      => 'Password',
            'agreement'     => 'Persetujuan Syarat dan Ketentuan'
        ];

        $this->validate($request, $validation, $message, $names);

        // VALIDATE FROM SQL INJECTION
        $email = Helper::validate_input_email($request->email);
        if (!$email) {
            return back()->withInput()->with('error_email', 'Sepertinya email Anda salah');
        }

        // WITH PROVIDER
        if ($request->provider && ($request->provider == 'google' || $request->provider == 'facebook')) {
            $avatar         = $request->avatar;
            $password       = Helper::hashing_this(Helper::unique_string());
            $token          = null;
            $verified_at    = date('Y-m-d H:i:s');
            $status         = 1;
        } else {
            // WITHOUT PROVIDER
            if ($request->input('password') !== $request->input('password_confirmation')) {
                return back()->withInput()->with('error_password', 'Beda dengan password. Cek lagi?');
            }

            if (!$request->agreement) {
                return back()->withInput()->with('error_agreement', 'Persetujuan Syarat dan Ketentuan wajib dicentang');
            }
        
            if (!empty($this->global_config->recaptcha_secret_key_public)) {
                // reCAPTCHA checking...
                $recaptcha_check = Helper::validate_recaptcha($request->input('g-recaptcha-response'), $this->global_config->recaptcha_secret_key_public);
                if (!$recaptcha_check) {
                    return back()
                        ->withInput()
                        ->with('error', lang('reCAPTCHA validation unsuccessful, please try again', $this->translations));
                }
            }

            $avatar         = null;
            $password       = Helper::hashing_this($request->input('password'));
            $token          = Helper::unique_string();
            $verified_at    = null;
            $status         = 0;
        }

        DB::beginTransaction();
        try {
            // SAVE DATA
            $data               = new buyer();
            $data->email        = $email;
            $data->avatar       = $avatar;
            $data->password     = $password;
            $data->token        = $token;
            $data->verified_at  = $verified_at;
            $data->status       = $status;
            $data->save();

            DB::commit();

            // SET EMAIL CONTENT TO USER
            if ($request->provider && ($request->provider == 'google' || $request->provider == 'facebook')) {
                // IF REGISTER BY PROVIDER
                $email_template     = 'emails.buyer_thanks_register';
                $this_subject       = 'Akun LokalKorner Kamu Sudah Aktif';

                $content                = [];
                $content['title']       = 'Akun LokalKorner Kamu Sudah Aktif';
                $content['email']       = $email;
                $content['content']     = 'Email <strong style="color:#3A3A3A !important;">' . $content['email'] . '</strong> sudah berhasil diverifikasi.';
                $company_info = HelperWeb::get_company_info();
                $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
                $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;
            } else {
                // IF REGISTER MANUAL
                $email_template         = 'emails.buyer_need_verification';
                $this_subject           = 'Konfirmasi Email Akun Kamu di Sini';

                $content                = [];
                $content['title']       = 'Konfirmasi Email Akun Kamu di Sini';
                $content['email']       = $email;
                $content['link']        = route('web.auth.verification', $token);
                $content['content']     = 'Email kamu <strong>' . $content['email'] . '?</strong> Jika benar, klik tombol Verifikasi di bawah atau klik link ini dalam waktu 48 jam.';
                $company_info = HelperWeb::get_company_info();
                $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
                $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;
            }

            // SEND EMAIL TO USER
            Mail::send($email_template, ['data' => $content], function ($message) use ($email, $this_subject) {
                if (env('APP_MODE', 'STAGING') == 'STAGING') {
                    $this_subject = '[STAGING] ' . $this_subject;
                }

                $message->subject($this_subject);
                $message->to($email);
            });

            $token = $data->token;

            // UNSET DATA FOR SESSION
            unset($data->password);
            unset($data->token);
            unset($data->updated_at);
            unset($data->deleted_at);

            // SUCCESS
            if ($request->provider && ($request->provider == 'google' || $request->provider == 'facebook')) {
                // if buyer register using provider, then auto-login
                Session::put('buyer', $data);

                return [
                    'status' => true,
                    'data' => $data
                ];
            } else {
                // if register manual, then need verify the email
                Session::put('email_token', $token);
                return view('web.auth.register_thanks');
            }
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, null, null, 'Buyer Register');

            if (env('APP_DEBUG') == false) {
                $error_msg = 'Oops, terjadi kesalahan, silahkan hubungi admin';
            }

            return back()
                ->withInput()
                ->with('error', $error_msg);
        }
    }

    public function verification($token)
    {
        $buyer = buyer::where('token', $token)->whereNull('verified_at')->first();

        if (empty($buyer)) {
            # ERROR
            return view('web.auth.verify_failed');
        }

        $buyer->token       = null;
        $buyer->status      = 1;
        $buyer->verified_at = date('Y-m-d H:i:s');

        if ($buyer->save()) {
            // SET EMAIL CONTENT TO USER
            $email          = $buyer->email;
            $email_template = 'emails.buyer_thanks_register';
            $this_subject   = 'Akun LokalKorner Kamu Sudah Aktif';

            $content                = [];
            $content['title']       = 'Akun LokalKorner Kamu Sudah Aktif';
            $content['email']       = $email;
            $content['content']     = 'Email <strong style="color:#3A3A3A !important;">' . $content['email'] . '</strong> sudah berhasil diverifikasi.';
            $company_info = HelperWeb::get_company_info();
            $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
            $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;

            // SEND EMAIL TO USER
            Mail::send($email_template, ['data' => $content], function ($message) use ($email, $this_subject) {
                if (env('APP_MODE', 'STAGING') == 'STAGING') {
                    $this_subject = '[STAGING] ' . $this_subject;
                }

                $message->subject($this_subject);
                $message->to($email);
            });

            # SUCCESS
            return view('web.auth.verify_success');
        }

        # ERROR
        return view('web.auth.verify_failed');
    }

    /**
     * LOGIN PAGE FOR BUYER
     */
    public function login()
    {
        if (Session::get('buyer')) {
            return redirect()->route('web.home');
        }
        $navigation_menu    = HelperWeb::get_nav_menu();

        return view('web.auth.login', compact('navigation_menu'));
    }

    /**
     * PROCESSING BUYER LOGIN
     */
    public function login_submit(Request $request)
    {
        // IF BUYER ALREADY LOGGED IN
        if (Session::get('buyer')) {
            return redirect()->route('web.home');
        }

        $validation = [
            'email'         => 'required',
            'password'      => 'required'
        ];

        $message    = [
            'required'      => 'Wajib diisi',
        ];

        $names      = [
            'email'         => 'Email',
            'password'      => 'Password'
        ];

        $this->validate($request, $validation, $message, $names);

        // VALIDATE FROM SQL INJECTION
        $email = Helper::validate_input_email($request->email);
        if (!$email) {
            return back()->withInput()->with('error_email', 'Sepertinya email Anda salah');
        }

        $password = $request->input('password');

        // CHECK BUYER
        $buyer = buyer::where('email', $email)->first();
        if (empty($buyer)) {
            return back()->withInput()->with('error_password', 'Password salah. Ayo, coba lagi.');
        }

        // VERIFY PASSWORD
        if (!password_verify($password, $buyer->password)) {
            # ERROR
            return back()->withInput()->with('error_password', 'Password salah. Ayo, coba lagi.');
        }

        // CHECK VERIFIED_AT
        if (is_null($buyer->verified_at)) {
            return back()->withInput()->with('error_email', 'Silahkan konfirmasi email terlebih dahulu');
        }

        // CHECK STATUS
        if (!$buyer->status) {
            return back()->withInput()->with('error_email', 'Akun sedang dinonaktifkan, silahkan hubungi admin');
        }

        if (!empty($this->global_config->recaptcha_secret_key_public)) {
            // reCAPTCHA checking...
            $recaptcha_check = Helper::validate_recaptcha($request->input('g-recaptcha-response'), $this->global_config->recaptcha_secret_key_public);
            if (!$recaptcha_check) {
                return back()
                    ->withInput()
                    ->with('error', lang('reCAPTCHA validation unsuccessful, please try again', $this->translations));
            }
        }

        // UNSET DATA BUYER
        unset($buyer->password);
        unset($buyer->token);
        unset($buyer->updated_at);
        unset($buyer->deleted_at);

        // SET SESSION
        Session::put('buyer', $buyer);

        if (Session::has('redirect_uri')) {
            // SAVE IT TO VARIABLE
            $redirect_to_page = Session::get('redirect_uri');

            // THEN FORGET SESSION
            Session::forget('redirect_uri');

            return redirect($redirect_to_page);
        }

        if (Session::has('from_page') && (is_null($buyer->fullname) || is_null($buyer->phone_number))) {
            return redirect()->route('web.buyer.profile')->with('error', 'Mohon lengkapi dahulu nama dan nomor telepon Anda.');
        } else {
            if (Session::has('from_page')) {
                // SAVE IT TO VARIABLE
                $redirect_to_page = Session::get('from_page');

                // THEN FORGET SESSION
                Session::forget('from_page');

                // AND THE LAST IS REDIRECT TO THIS PAGE
                return redirect($redirect_to_page);
            }
        }

        // SUCCESS
        return redirect()->route('web.home');
    }

    /**
     * LOGOUT
     */
    public function logout()
    {
        if (Session::has('buyer')) {
            Session::forget('buyer');
        }

        return redirect()->route('web.home');
    }

    /**
     * CHANGE PASSWORD PAGE FOR BUYER
     */
    public function change_password()
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login');
        }

        $navigation_menu = HelperWeb::get_nav_menu();

        return view('web.auth.change_password', compact('navigation_menu'));
    }

    /**
     * PROCESSING CHANGE PASSWORD BUYER
     */
    public function change_password_submit(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login');
        }

        $validation = [
            'old_password'  => 'required|min:6',
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',                                // must be at least 8 characters in length
                'regex:/[a-z]/',                        // must contain at least one lowercase letter
                'regex:/[A-Z]/',                        // must contain at least one uppercase letter
                'regex:/[0-9]/',                        // must contain at least one digit numeric
                'regex:/[?!@#$%^&*~`_+=:;.,"><\'-]/',   // must contain a special character
            ]
        ];

        $message    = [
            'required'      => 'Wajib diisi',
            'confirmed'     => 'Beda dengan password baru. Cek lagi?',
            'min'           => 'Min. 8 karakter, kombinasi huruf kapital, angka, & simbol',
            'regex'         => 'Min. 8 karakter, kombinasi huruf kapital, angka, & simbol'
        ];

        $names      = [
            'old_password'  => 'Password lama',
            'password'      => 'Password'
        ];

        $this->validate($request, $validation, $message, $names);

        // CHECK BUYER BY ID
        $buyer = buyer::where('id', Session::get('buyer')->id)->first();
        if (empty($buyer)) {
            Session::forget('buyer');
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login untuk melanjutkan');
        }

        // VERIFY PASSWORD
        if (!password_verify($request->old_password, $buyer->password)) {
            # ERROR
            return back()->withInput()->with('error_old_password', 'Hmmm.. password salah. Coba lagi?');
        }

        // VALIDATE FROM SQL INJECTION
        if ($request->input('password') !== $request->input('password_confirmation')) {
            return back()->withInput()->with('error_password', 'Beda dengan password baru. Cek lagi?');
        }

        // VERIFY NEW PASSWORD WITH OLD PASSWORD
        if ($request->input('password') === $request->input('old_password')) {
            return back()->withInput()->with('error_password', 'Password harus beda dengan yang lama');
        }

        // SAVE DATA
        $password           = Helper::hashing_this($request->input('password'));
        $buyer->password    = $password;

        DB::beginTransaction();
        try {
            // SAVE THE DATA
            $buyer->save();

            // SEND EMAIL
            $this_subject           = 'Konfirmasi Penggantian Password Akun Kamu';

            $content                = [];
            $content['title']       = 'Konfirmasi Penggantian Password Akun Kamu';
            $content['email']       = $buyer->email;
            $content['content']     = 'Password kamu berhasil diganti. <br><br>Untuk keamanan, jangan beritahukan password kamu ke siapa pun termasuk tim LokalKorner.';
            $company_info = HelperWeb::get_company_info();
            $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
            $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;
            $email                  = $buyer->email;

            // SEND EMAIL
            Mail::send('emails.success_change_password', ['data' => $content], function ($message) use ($email, $this_subject) {
                if (env('APP_MODE', 'STAGING') == 'STAGING') {
                    $this_subject = '[STAGING] ' . $this_subject;
                }

                $message->subject($this_subject);
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $message->to($email);
            });

            DB::commit();
            return redirect()->route('web.auth.change_password')->with('success', 'Berhasil mengganti password.');
        } catch (\Exception $e) {
            DB::rollback();
            if (env('APP_MODE', 'STAGING') == 'STAGING') {
                // dd($e->getMessage() . ' ' . $e->getLine());
                return back()->withInput()->with('error_old_password', 'Oops, terjadi kesalahan, gagal mengganti password');
            }
        }
    }

    /**
     * FORGOT PASSWORD PAGE FOR BUYER
     */
    public function forgot_password()
    {
        if (Session::get('buyer')) {
            return redirect()->route('web.home');
        }

        return view('web.auth.forgot_password');
    }

    /**
     * PROCESSING BUYER FORGOT PASSWORD
     */
    public function forgot_password_submit(Request $request)
    {
        // IF BUYER ALREADY LOGGED IN
        if (Session::get('buyer')) {
            return redirect()->route('web.home');
        }

        $validation = [
            'email' => 'required'
        ];

        $message    = [
            'required' => 'Wajib diisi'
        ];

        $names      = [
            'email' => 'Email',
        ];

        $this->validate($request, $validation, $message, $names);

        // VALIDATE FROM SQL INJECTION
        $email = Helper::validate_input_email($request->email);
        if (!$email) {
            return back()->withInput()->with('error_email', 'Sepertinya email Anda salah');
        }

        // CHECK BUYER DATA BY EMAIL
        $buyer = buyer::where('email', $email)->first();
        if (empty($buyer)) {
            return back()->withInput()->with('error_email', 'Data tidak ditemukan, periksa kembali input email Anda');
        }

        // CHECK BUYER FORGOT PASS DATA, IF 
        $check_buyer_forgetpass = buyer_forgetpass::where('user_id', $buyer->id) // SEARCH BY ID
            ->where('status', 0) // UNUSED
            ->where('created_at', '>', date('Y-m-d H:i:s', strtotime('-10 minutes')))
            ->where('created_at', '<', date('Y-m-d H:i:s'))
            ->get();

        if (count($check_buyer_forgetpass) > 2) {
            return back()->withInput()->with('error_email', 'Anda baru saja melakukan tindakan ini, silahkan coba beberapa saat lagi');
        }

        // INSERT DATA INTO BUYER FORGET PASS TABLE
        $token  = Helper::unique_string();

        // SETUP TIME FOR EXPIRED BUYER SESSION
        $time   = env('BUYER_SESSION_EXPIRED', 86400);

        DB::beginTransaction();
        try {
            // SAVE THE DATA
            $data               = new buyer_forgetpass();
            $data->user_id      = $buyer->id;
            $data->token        = $token;
            $data->expired_at   = date('Y-m-d H:i:s', time() + $time); // SECONDS;
            $data->status       = 0;
            $data->save();

            // SEND EMAIL VERIFICATION
            $this_subject           = 'Reset Password Kamu di Sini';

            $content                = [];
            $content['title']       = 'Reset Password Kamu di Sini';
            $content['email']       = $buyer->email;
            $content['content']     = 'Urusan ganti password memang merepotkan, ya. <br>Tenang, kamu cukup klik tombol di bawah, kok. Mudah, kan?';
            $content['link']        = route('web.auth.reset_password', $token);
            $company_info = HelperWeb::get_company_info();
            $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
            $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;
            $email                  = $buyer->email;

            // SEND EMAIL
            Mail::send('emails.forgot_password', ['data' => $content], function ($message) use ($email, $this_subject) {
                if (env('APP_MODE', 'STAGING') == 'STAGING') {
                    $this_subject = '[STAGING] ' . $this_subject;
                }

                $message->subject($this_subject);
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $message->to($email);
            });

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            if (env('APP_MODE', 'STAGING') == 'STAGING') {
                // dd($e->getMessage() . ' ' . $e->getLine());
                return back()->withInput()->with('error_email', 'Oops, terjadi kesalahan, silahkan hubungi admin');
            }
        }

        // SUCCESS
        return redirect()->route('web.home')->with('success', 'Verifikasi sudah dikirimkan. Cek email kamu sekarang, ya.');
    }

    public function reset_password($token)
    {
        $buyer = buyer_forgetpass::where('token', urlencode($token))->first();
        if (empty($buyer)) {
            return redirect()->route('web.home')->with('error', 'Token tidak dikenal.');
        }

        if ($buyer->expired_at < date('Y-m-d H:i:s')) {
            return redirect()->route('web.home')->with('error', 'Token sudah kaladuarsa, silahkan isi form lupa password kembali.');
        }

        // CHECK DATA BUYER
        $data = buyer::where('id', $buyer->user_id)->where('status', 1)->first();
        if (empty($data)) {
            return redirect()->route('web.home')->with('error', 'Data tidak ditemukan, silahkan coba kembali.');
        }

        $email = $data->email;

        // PUT SESSION
        Session::put('token_reset_password', $token);

        // REDIRECT TO PAGE RESET PASSWORD
        return view('web.auth.reset_password', compact('email'));
    }

    /**
     * PROCESSING BUYER RESET PASSWORD
     */
    public function reset_password_submit(Request $request)
    {
        if (!Session::get('token_reset_password')) {
            return redirect()->route('web.home')->with('error', 'Token tidak dikenal.');
        }

        // IF BUYER ALREADY LOGGED IN
        if (Session::get('buyer')) {
            return redirect()->route('web.home');
        }

        $validation = [
            'password' => [
                'required',
                'string',
                'min:8',                                // must be at least 8 characters in length
                'regex:/[a-z]/',                        // must contain at least one lowercase letter
                'regex:/[A-Z]/',                        // must contain at least one uppercase letter
                'regex:/[0-9]/',                        // must contain at least one digit numeric
                'regex:/[?!@#$%^&*~`_+=:;.,"><\'-]/',   // must contain a special character
            ]
        ];

        $message    = [
            'required'      => 'Wajib diisi',
            'unique'        => ':attribute ' . 'sudah pernah digunakan, silahkan input dengan data lain',
            'min'           => 'Min. 8 karakter, kombinasi huruf kapital, angka, & simbol',
            'regex'         => 'Min. 8 karakter, kombinasi huruf kapital, angka, & simbol'
        ];

        $names      = [
            'password' => 'Password'
        ];

        $this->validate($request, $validation, $message, $names);

        // VALIDATE FROM SQL INJECTION
        if ($request->input('password') !== $request->input('password_confirmation')) {
            return back()->withInput()->with('error_password', 'Beda dengan password. Cek lagi?');
        }

        $password = Helper::hashing_this($request->input('password'));

        // CHECK BUYER BY TOKEN
        $token              = Session::get('token_reset_password');
        $buyer_forgetpass   = buyer_forgetpass::where('token', urlencode($token))->first();
        if (empty($buyer_forgetpass)) {
            return redirect()->route('web.home')->with('error', 'Token tidak dikenal.');
        }

        // CHECK BUYER DATA
        $buyer = buyer::where('id', $buyer_forgetpass->user_id)->first();
        if (empty($buyer)) {
            return redirect()->route('web.home')->with('error', 'Data tidak ditemukan.');
        }

        // SAVE DATA
        $buyer->password    = $password;
        if (is_null($buyer->verified_at)) {
            $buyer->verified_at = date('Y-m-d H:i:s');
        }

        DB::beginTransaction();
        try {
            // SAVE THE DATA
            $buyer->save();

            // SUCCESS - UPDATE BUYER FORGETPASS TABLE
            $buyer_forgetpass->status = 1;
            $buyer_forgetpass->save();

            DB::commit();

            // SEND EMAIL
            $this_subject           = 'Konfirmasi Penggantian Password Akun Kamu';

            $content                = [];
            $content['title']       = 'Konfirmasi Penggantian Password Akun Kamu';
            $content['name']        = isset($buyer->fullname) ? $buyer->fullname : $buyer->email;
            $content['content']     = 'Password akun lokalkorner Anda telah berhasil direset';
            $company_info = HelperWeb::get_company_info();
            $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
            $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;
            $email                  = $buyer->email;

            // SEND EMAIL
            Mail::send('emails.buyer_password_change_info', ['data' => $content], function ($message) use ($email, $this_subject) {
                if (env('APP_MODE', 'STAGING') == 'STAGING') {
                    $this_subject = '[STAGING] ' . $this_subject;
                }

                $message->subject($this_subject);
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $message->to($email);
            });

            Session::forget('token_reset_password');
            return redirect()->route('web.home')->with('success', 'Ubah password berhasil.');
        } catch (\Exception $e) {
            DB::rollback();
            if (env('APP_MODE', 'STAGING') == 'STAGING') {
                // dd($e->getMessage() . ' ' . $e->getLine());

                return back()->withInput()->with('error_email', $e->getMessage() . ' in ' . $e->getFile() . ' at ' . $e->getLine());
            }
            return back()->withInput()->with('error_email', 'Oops, terjadi kesalahan, reset password tidak berhasil');
        }
    }

    // ========================
    // PROVIDERS
    // ========================

    private function is_provider_allowed($driver)
    {
        return in_array($driver, $this->providers) && config()->has("services.{$driver}");
    }

    protected function send_failed_response($msg = null)
    {
        return redirect()
            ->route('web.home')
            ->withErrors(['msg' => $msg ?: 'Tidak bisa masuk/daftar, silahkan coba dengan provider lainnya.']);
    }

    public function redirect_to_provider($driver)
    {
        if (!$this->is_provider_allowed($driver)) {
            return $this->send_failed_response("{$driver} sedang tidak bisa digunakan");
        }

        try {
            return Socialite::driver($driver)->redirect();
        } catch (\Exception $e) {
            // You should show something simple fail message
            return $this->send_failed_response($e->getMessage());
        }
    }

    public function handle_provider_callback($social, Request $request)
    {
        $user = Socialite::driver($social)->stateless()->user();
        if ($user) {
            // CHECK BUYER BY EMAIL
            $result = buyer::where('email', $user->email)->first();

            if (!empty($result)) {
                // REGISTERED
                if (!$result->status) {
                    return redirect()->route('web.auth.login')->with('error', 'Gagal login, akun Anda telah dinonaktifkan');
                }

                // UNSET DATA BUYER
                unset($result->password);
                unset($result->token);
                unset($result->updated_at);
                unset($result->deleted_at);

                // LOGIN SESSION
                Session::put('buyer', $result);

                // SUCCESS
                if (is_null($result->fullname) || is_null($result->phone_number)) {
                    return redirect()->route('web.buyer.profile')->with('error', 'Mohon lengkapi dahulu nama dan nomor telepon Anda.');
                } else {
                    if (Session::has('from_page')) {
                        // SAVE IT TO VARIABLE
                        $redirect_to_page = Session::get('from_page');

                        // THEN FORGET SESSION
                        Session::forget('from_page');

                        // AND THE LAST IS REDIRECT TO THIS PAGE
                        return redirect($redirect_to_page);
                    } else {
                        // DEFAULT
                        return redirect()->route('web.home');
                    }
                }
            } else {
                // USER NOT FOUND, SO CREATE NEW BUYER DATA 
                $param_request = new Request([
                    'email'     => $user->email,
                    'provider'  => $social,
                    'fullname'  => $user->name,
                    'avatar'    => $user->avatar,
                ]);

                $result = $this->register_submit($param_request);

                if (isset($result['status']) && $result['status'] == true) {
                    $user_new = $result['data'];
                    try {
                        // INSERT INTO BUYER PROVIDER
                        $insert                 = new buyer_provider();
                        $insert->user_id        = $user_new->id;
                        $insert->provider_name  = $social;
                        $insert->token          = $user->token;

                        if (!$insert->save()) {
                            # FAILED TO INSERT INTO BUYER PROVIDER
                            return redirect()
                                ->route('web.auth.login')
                                ->with('error', 'Autentikasi dengan ' . ucwords($social) . ' gagal, silahkan coba lagi. (Kode A2)');
                        }

                        // SUCCESS
                        if (is_null($user_new->fullname) || is_null($user_new->phone_number)) {
                            return redirect()->route('web.buyer.profile')->with('error', 'Mohon lengkapi dahulu nama dan nomor telepon Anda.');
                        } else {
                            if (Session::has('from_page')) {
                                // SAVE IT TO VARIABLE
                                $redirect_to_page = Session::get('from_page');

                                // THEN FORGET SESSION
                                Session::forget('from_page');

                                // AND THE LAST IS REDIRECT TO THIS PAGE
                                return redirect($redirect_to_page)->with('success', 'Registrasi berhasil.');
                            } else {
                                // DEFAULT
                                return redirect()->route('web.home')->with('success', 'Registrasi berhasil, selamat berbelanja.');
                            }
                        }
                    } catch (\Exception $e) {
                        $error_msg = 'Autentikasi dengan ' . ucwords($social) . ' gagal, silahkan coba lagi. (Kode A1)';
                        if (env('APP_MODE', 'STAGING') == 'STAGING') {
                            $error_msg = $e->getMessage() . ' in ' . $e->getFile() . ' at ' . $e->getLine();
                        }

                        return redirect()
                            ->route('web.auth.login')
                            ->with('error', $error_msg);
                    }
                } else {
                    // FAILED TO REGISTER USING PROVIDER
                    return redirect()->route('web.auth.login')->with('error', 'Autentikasi dengan ' . ucwords($social) . ' gagal, silahkan coba lagi.');
                }
            }
        }

        // FAILED
        return redirect()
            ->route('web.auth.login')
            ->with('error', 'Autentikasi dengan ' . ucwords($social) . ' gagal, silahkan coba lagi.');
    }

    /**
     * CHANGE EMAIL PAGE FOR BUYER
     */
    public function change_email()
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login');
        }

        $navigation_menu = HelperWeb::get_nav_menu();

        return view('web.auth.change_email', compact('navigation_menu'));
    }

    public function ajax_check_password(Request $request)
    {
        $validation = [
            'user_id'       => 'required|integer',
            'user_email'    => 'required',
            'password'      => [
                'required',
                'string',
                'min:8',                                // must be at least 8 characters in length
                'regex:/[a-z]/',                        // must contain at least one lowercase letter
                'regex:/[A-Z]/',                        // must contain at least one uppercase letter
                'regex:/[0-9]/',                        // must contain at least one digit numeric
                'regex:/[?!@#$%^&*~`_+=:;.,"><\'-]/',   // must contain a special character
            ]
        ];

        $message    = [
            'required'      => ':attribute' . ' Wajib diisi',
            'min'           => ':attribute' . ' Min. 8 karakter, kombinasi huruf kapital, angka, & simbol',
            'regex'         => ':attribute' . ' Min. 8 karakter, kombinasi huruf kapital, angka, & simbol'
        ];

        $names      = [
            'user_id'       => 'User ID',
            'user_email'    => 'User Email',
            'password'      => 'Password'
        ];

        $validator = Validator::make($request->all(), $validation, $message, $names);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => 'Validation Error',
                // 'data'    => $validator->errors()->messages()
                'data'    => $validator->errors()->all()
            ];

            return response()->json($data);
        }

        // $this->validate($request, $validation, $message, $names);

        // GET USER_ID, USER_EMAIL, AND PASSWORD
        $user_id    = (int) $request->user_id;
        $user_email = Helper::validate_input_email($request->user_email);
        $password   = Helper::validate_input_text($request->password);

        // CHECK DATA
        $buyer = buyer::where('id', $user_id)->where('email', $user_email)->first();
        if (empty($buyer)) {
            return response()->json([
                'status'    => false,
                'message'   => 'data tidak ditemukan'
            ]);
        }

        // VERIFY PASSWORD
        if (!password_verify($password, $buyer->password)) {
            return response()->json([
                'status'    => false,
                'message'   => 'password salah, silahkan coba lagi'
            ]);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'berhasil, silahkan lanjutkan request'
        ]);
    }

    /**
     * PROCESSING CHANGE EMAIL BUYER
     */
    public function ajax_change_email(Request $request)
    {
        $validation = [
            'user_id'           => 'required|integer',
            'user_new_email'    => 'required',
            'user_email'        => 'required',
            'password'          => [
                'required',
                'string',
                'min:8',                                // must be at least 8 characters in length
                'regex:/[a-z]/',                        // must contain at least one lowercase letter
                'regex:/[A-Z]/',                        // must contain at least one uppercase letter
                'regex:/[0-9]/',                        // must contain at least one digit numeric
                'regex:/[?!@#$%^&*~`_+=:;.,"><\'-]/',   // must contain a special character
            ]
        ];

        $message    = [
            'required'      => ':attribute' . ' Wajib diisi',
            'min'           => ':attribute' . ' Min. 8 karakter, kombinasi huruf kapital, angka, & simbol',
            'regex'         => ':attribute' . ' Min. 8 karakter, kombinasi huruf kapital, angka, & simbol'
        ];

        $names      = [
            'user_id'           => 'User ID',
            'user_email'        => 'User Email',
            'user_new_email'    => 'Email Baru',
            'password'          => 'Password'
        ];

        $validator = Validator::make($request->all(), $validation, $message, $names);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => 'Validation Error',
                // 'data'    => $validator->errors()->messages()
                'data'    => $validator->errors()->all()
            ];

            return response()->json($data);
        }

        // GET USER_ID, USER_EMAIL, USER_NEW_EMAIL, AND PASSWORD
        $user_id        = (int) $request->user_id;
        $user_new_email = Helper::validate_input_email($request->user_new_email);
        $user_email     = Helper::validate_input_email($request->user_email);
        $password       = Helper::validate_input_text($request->password);

        // CHECK DATA
        $buyer = buyer::where('id', $user_id)->where('email', $user_email)->first();
        if (empty($buyer)) {
            return response()->json([
                'status'    => false,
                'message'   => 'data tidak ditemukan'
            ]);
        }

        // VERIFY PASSWORD
        if (!password_verify($password, $buyer->password)) {
            return response()->json([
                'status'    => false,
                'message'   => 'password salah, silahkan coba lagi'
            ]);
        }

        // CHECK NEW EMAIL
        if (!$user_new_email) {
            return response()->json([
                'status'    => false,
                'message'   => 'Sepertinya email baru Anda salah'
            ]);
        }

        $exist_new_email = buyer::where('email', $user_new_email)->first();
        if (!empty($exist_new_email)) {
            return response()->json([
                'status'    => false,
                'message'   => 'Email sudah pernah dipakai'
            ]);
        }

        // CHECK EMAIL HISTORY
        $check_email_history = buyer_email::where('value', $user_new_email)->where('user_id', $user_id)->first();
        if (!empty($check_email_history)) {
            return response()->json([
                'status'    => false,
                'message'   => 'Email pernah terpakai, silahkan coba dengan nomer lain'
            ]);
        }

        // LAST EMAIL BUYER
        $last_email = $buyer->email;

        DB::beginTransaction();
        try {
            // CHECK TO BUYER CHANGE EMAIL TABLE
            $exist_data_change_email = buyer_change_email::where('user_id', $user_id)
                ->where('user_new_email', $user_new_email)
                ->where('status', 0)
                ->where('expired_at', '>', date('Y-m-d H:i:s'))
                ->first();

            if (!empty($exist_data_change_email)) {
                if ($exist_data_change_email->retry >= 3) {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'Anda sudah terlalu sering melakukan aksi ini'
                    ]);
                } else {
                    $exist_data_change_email->retry = $exist_data_change_email->retry + 1;
                    $exist_data_change_email->save();
                }

                $token      = $exist_data_change_email->token;
                $insert_id  = $exist_data_change_email->id;
            } else {
                $token = Helper::unique_string();

                // SETUP TIME FOR EXPIRED BUYER SESSION
                $time = env('BUYER_SESSION_CHANGE_EMAIL_EXPIRED', 86400);

                // INSERT NEW TOKEN
                $insert                 = new buyer_change_email();
                $insert->user_id        = $user_id;
                $insert->user_new_email = $user_new_email;
                $insert->token          = $token;
                $insert->retry          = 0;
                $insert->expired_at     = date('Y-m-d H:i:s', time() + $time); // SECONDS;
                $insert->status         = 0;
                $insert->save();

                $insert_id = $insert->id;
            }

            // SEND EMAIL
            $this_subject           = '[LokalKorner] Perubahan Email';

            $content                = [];
            $content['title']       = '[LokalKorner] Perubahan Email';
            $content['content']     = 'Mohon konfirmasi perubahan email anda di link berikut ' . route('web.auth.confirm_change_email', $token);
            $content['email']       = $user_new_email;
            $email                  = $user_new_email;

            // SEND EMAIL
            Mail::send('emails.change_email', ['data' => $content], function ($message) use ($email, $this_subject) {
                if (env('APP_MODE', 'STAGING') == 'STAGING') {
                    $this_subject = '[STAGING] ' . $this_subject;
                }

                $message->subject($this_subject);
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $message->to($email);
            });

            DB::commit();
            
            // INSERT HISTORY BANNER
            if ($last_email != $user_new_email) {
                $buyer_email = new buyer_email();
                $buyer_email->user_id = $buyer->id;
                $buyer_email->value = $last_email;
                $buyer_email->save();
            }

            Session::put('change_email_id', $insert_id);

            return response()->json([
                'status'            => true,
                'message'           => 'Berhasil mengganti email',
                'change_email_id'   => $insert_id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            if (env('APP_MODE', 'STAGING') == 'STAGING') {
                // dd($e->getMessage() . ' ' . $e->getLine());
                return response()->json([
                    'status'    => false,
                    'message'   => 'Oops, terjadi kesalahan, gagal mengganti email'
                    // 'message'   => $e->getMessage() . ' ' . $e->getLine()
                ]);
            }
        }
    }

    /**
     * REDIRECT PAGE CHANGE EMAIL SUCCESS
     */
    public function change_email_success()
    {
        // THEN FORGET ALL SESSION BUYER
        Session::forget('buyer');

        // THEN REDIRECT TO SUCCESS PAGE
        return view('web.auth.change_email_success');
    }

    /**
     * VERIFICATION CHANGE EMAIL
     */
    public function confirm_change_email($token)
    {
        if (Session::get('buyer')) {
            Session::forget('buyer');
        }

        $buyer_change_email = buyer_change_email::where('token', $token)->where('status', 0)->first();

        if (empty($buyer_change_email)) {
            # ERROR
            return view('web.auth.verify_failed');
        }

        DB::beginTransaction();
        try {
            // UPDATE BUYER CHANGE EMAIL TABLE
            $buyer_change_email->status = 1;
            $buyer_change_email->save();

            // UPDATE BUYER EMAIL TO THE NEW EMAIL
            $buyer = buyer::find($buyer_change_email->user_id);
            if (!empty($buyer)) {
                $buyer->email = $buyer_change_email->user_new_email;
                $buyer->save();
                DB::commit();
            } else {
                DB::rollback();
                # ERROR
                return view('web.auth.verify_failed');
            }

            # SUCCESS
            return view('web.auth.verify_success');
        } catch (\Exception $e) {
            DB::rollback();
            # ERROR
            return view('web.auth.verify_failed');
        }
    }

    /**
     * RESEND EMAIL
     */
    public function resend_change_email(Request $request)
    {
        $buyer_change_email_id = (int) $request->id;

        // CHECK DATA
        $exist_data = buyer_change_email::where('id', $buyer_change_email_id)->where('status', 0)->first();
        if (empty($exist_data)) {
            return response()->json([
                'status'    => false,
                'message'   => 'data tidak ditemukan'
            ]);
        }

        if ($exist_data->expired_at < date('Y-m-d H:i:s')) {
            return response()->json([
                'status'    => false,
                'message'   => 'data sudah expired'
            ]);
        }

        if ($exist_data->retry >= 3) {
            return response()->json([
                'status'    => false,
                'message'   => 'anda sudah terlalu sering melakukan aksi ini'
            ]);
        }

        // +1 RETRY
        $exist_data->retry = $exist_data->retry + 1;
        $exist_data->save();

        // SEND EMAIL
        $this_subject           = '[LokalKorner] Perubahan Email';

        $content                = [];
        $content['title']       = '[LokalKorner] Perubahan Email';
        $content['content']     = 'Mohon konfirmasi perubahan email anda di link berikut ' . route('web.auth.confirm_change_email', $exist_data->token);
        $content['email']       = $exist_data->user_new_email;
        $email                  = $exist_data->user_new_email;

        // SEND EMAIL
        Mail::send('emails.change_email', ['data' => $content], function ($message) use ($email, $this_subject) {
            if (env('APP_MODE', 'STAGING') == 'STAGING') {
                $this_subject = '[STAGING] ' . $this_subject;
            }

            $message->subject($this_subject);
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $message->to($email);
        });

        return response()->json([
            'status'    => true,
            'message'   => 'berhasil mengirim ulang email'
        ]);
    }

    /**
     * RESEND EMAIL REGISTER
     */
    public function resend_email_register(Request $request)
    {
        // GET TOKEN
        $token = Helper::validate_input_text($request->email_token);

        // CHECK DATA
        $exist_data = buyer::where('token', $token)->where('status', 0)->whereNull('verified_at')->first();
        if (empty($exist_data)) {
            return response()->json([
                'status'    => false,
                'message'   => 'Data tidak ditemukan'
            ]);
        }

        // IF REGISTER MANUAL
        $email                  = $exist_data->email;
        $email_template         = 'emails.buyer_need_verification';
        $this_subject           = 'Konfirmasi Email Akun Kamu di Sini';

        $content                = [];
        $content['title']       = 'Konfirmasi Email Akun Kamu di Sini';
        $content['email']       = $email;
        $content['link']        = route('web.auth.verification', $token);
        $content['content']     = 'Email kamu <strong>' . $content['email'] . '?</strong> Jika benar, klik tombol Verifikasi di bawah atau klik link ini dalam waktu 48 jam.';
        $company_info = HelperWeb::get_company_info();
        $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
        $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;

        // SEND EMAIL TO USER
        Mail::send($email_template, ['data' => $content], function ($message) use ($email, $this_subject) {
            if (env('APP_MODE', 'STAGING') == 'STAGING') {
                $this_subject = '[STAGING] ' . $this_subject;
            }

            $message->subject($this_subject);
            $message->to($email);
        });

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil mengirim ulang email'
        ]);
    }
}
