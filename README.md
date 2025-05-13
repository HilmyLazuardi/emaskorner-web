# Siorensys

A PHP Laravel Content Management System (CMS) using Bootstrap 4 Admin Dashboard Template [Gentelella](https://github.com/ColorlibHQ/gentelella).

Developed by [@vickzkater](https://github.com/vickzkater/) (Powered by [KINIDI Tech](https://kiniditech.com/)) since 20 Feb 2021.

Based on Laravel 7.30.4


## Features
- `Multilingual`: You can develop application that supports multilingual.
- `Multi-Office`: You can develop application that supports multi-office with separate privileges.
- `App Configurations`: You can easily manage app configurations, including app name, app logo, favicon, meta data, open graph data, etc. And you can easily back up your app configurations with the export feature, then you can import them into other applications that also use this boilerplate.
- `AES-256 encryption`: You can easily encrypt strings using the AES-256 algorithm with the function `Helper::encrypt()` & `Helper::decrypt()`.
- `DB & File Log`: You can keep system & error logs using database and/or file.
- `System Logs with comparison changes`: You can see any changes of data easily in comparison changes
- `App Themes`: You can change the CMS theme with 7 different options.
- `Custom 404 | Not Found Page`: You can modify the custom 404 | Not Found Page in `resources\views\errors\404.blade.php`.
- `Custom 405 | Method Not Allowed`: You can modify the custom 404 | Not Found Page in `resources\views\errors\404.blade.php`.
- `Custom 419 | Expired Page`: You can modify the custom 405 | Method Not Allowed Page in `resources\views\errors\405.blade.php`.
- `Custom 503 | Maintenance Page`: You can modify the custom 503 | Maintenance Page in `resources\views\errors\503.blade.php`.
- `Secure Login`: You can enable secure login to the app using Google reCAPTCHA v2 Checkbox & block the IP address that tries to login for several times.
- `Blocked IP`: You can manage the blocked IP list (unblock) or manually set the IP blacklist.
- `Error Logs`: You can see & manage error logs in CMS.
- `Custom Website Navigation Menu`: You can manage the website navigation menu (top & bottom) with unlimited sub-level.
- `Standard Website Security`: Including SSL Cookie with secure flag and [HTTP Security Response Headers](https://owasp.org/www-project-secure-headers/). By default, the security module is enabled. If you want to disable it, please check the ENV file for `SESSION_SECURE_COOKIE`, `APP_SECURE`, and `APP_SECURE_STRICT`.
- `Page Builder`: You can manage landing pages using content elements (masthead, text, image, button, video, script).
- `Social Media`: You can manage social media that is used by the website.
- `FAQ`: You can manage FAQ (Frequently Asked Questions) that is used by the website.
- `Note`: You can save some notes using simple cryptography.
- `Form`: You can manage quiz or questionnaire forms using content elements.


## Modules
- Application Configurations
- System Logs
- Error Logs
- Module Management
- Rule Management
- Office & Branch Office Management
- Admin Group & Access Management
- Administrator Management
- Country Management
- Phrase Management
- Language Management
- Blocked IP Management
- Navigation Menu
- Page
- Social Media
- FAQ
- Note
- Form


## Server Requirements
- PHP >= 7.3
- [Laravel 7.x Requirements](https://laravel.com/docs/7.x/installation#server-requirements)


## Installing Siorensys

Siorensys utilizes [Composer](http://getcomposer.org/) to manage its dependencies. So, before using Siorensys, make sure you have Composer installed on your machine.

### 1) Clone this project
You may install Siorensys by clone this project using git command or download as .zip file then extract it.

### 2) Install the dependencies
You must install the dependencies first to setup this project using command
```
composer install
```

### 3) Set ENV file
You must have `ENV` file to set up configuration, you may rename `.env.example` file to `.env` or copy it using command below
```
# Mac/Linux
cp .env.example .env

# Windows
copy .env.example .env
```

### 4) Set application key
The next thing you should do is set your application key to a random string. Typically, this string should be 32 characters long. The key can be set in the `ENV` environment file. You may generate the application key using command
```
php artisan key:generate
```
****If the application key is not set, your user sessions and other encrypted data will not be secure!***

### 5) Setting timezone
`UTC` is the default timezone. However, you can set the timezone that will be used in the application to display timestamps using helper function `locale_timestamp()` and `ENV config "APP_TIMEZONE"`.

### 6) Set Environment Configurations
- `PREFIX_TABLE` for set prefix table (only used by this application).
- `SESSION_ADMIN_NAME` for set name of admin session.
- `SESSION_SECURE_COOKIE` for enable HTTPS Only Cookies.
- `APP_SECURE` for enable secure headers.
- `APP_SECURE_STRICT` for enable secure headers - more secure, but you need to register all your external links (CSS & scripts).
- `DISPLAY_SESSION` for show/hide session dump in Footer Admin (Development Purpose).
- `SYSTEM_LOG` for enable/disable system logging (using database only).
- `SYSTEM_LOG_FILE` for enable/disable system logging using file, but must enable `SYSTEM_LOG` first to enable this.
- `ERROR_LOG` for enable/disable error logging (using database only).
- `ERROR_LOG_FILE` for enable/disable error logging using file, but must enable `ERROR_LOG` first to enable this.
- `APP_BACKEND` for choose application back-end mode (MODEL or API) if use API, please make sure `APP_API_URL` is not empty.
- `APP_API_URL` for set API URL, if this project using back-end mode API (`APP_BACKEND`=API).
- `API_USER` for set API auth credential (optional).
- `API_PASS` for set API auth credential (optional).
- `CRYPTOGRAPHY_MODE` for enable/disable cryptography for object ID.
- `PHP_ENCRYPTION_PATH` for set path where you save the secret key file.
- `ADMIN_CMS` for enable/disable Admin CMS feature.
- `ADMIN_DIR` for set Admin CMS directory name (or leave it blank if using the Admin CMS only).
- `MULTILANG_MODULE` for enable/disable Multilingual Module.
- `DEFAULT_COUNTRY` for set default country.
- `DEFAULT_LANGUAGE` for set default language.
- `NOTIF_MODULE` for enable/disable Notification Module.
- `NOTIF_INTERVAL` for set interval looping to get notifications in miliseconds (1000 ms = 1 second).
- `APP_TIMEZONE` for set timezone using function helper `locale_timestamp()`, sample: UTC (GMT) or Asia/Jakarta (GMT+7) or Asia/Kuala_Lumpur (GMT+8). Check [List of Supported Timezones in PHP](https://www.php.net/manual/en/timezones.php) for others.

### 7) Database setup
****You must run the database migration for running this application.***

Our recommendation is using `utf8` as Database Encoding and `utf8_general_ci` as Database Collation.

Make sure `DB_DATABASE` is set correctly in `ENV` file then run migrations to create the database structure and some system data
```
php artisan migrate
```

****Login details (default)***

You may login to application (for default using URL `project-path/public/superuser`) using credentials below:
```
# Super Administrator
Username: superuser
Password: Sudo123!

# Administrator
Username: admin
Password: Admin123!
```

*If you can't login using the above credentials, you may reset the password directly to the database with the following algorithm:
```
password_hash($string, PASSWORD_DEFAULT);

# Reference: https://www.php.net/manual/en/function.password-hash.php
```


### 8) Set application secret key for encryption *optional
You may use AES-256 encryption content feature, then you must generate a random encryption key using command
```
vendor/bin/generate-defuse-key
```
It will print a random encryption key to standard output. Then save the output to a configuration file, for example in `/etc/app-secret-key.txt` and set the file permissions so that only the user that the website PHP scripts run as can access it.


### 9) Encrypted configuration settings for security reasons *optional
If you are very concerned about security issues, maybe you can activate this feature.
```
How-To
1) You must do the "Step 8" first, after that keep the key file, example: app-secret-key.txt
2) Backup your configuration settings first
3) Use the "Encrypt Tool" in this application for generate encrypted string for important configuration settings (DB, Email, API, etc)
4) Use the encrypted string as your configuration settings in ENV file
5) Enable "SECURE_CONFIG" in ENV file
6) Now your configuration settings will be safe because they are encrypted
```
*Note: The encryption used is [defuse/php-encryption](https://github.com/defuse/php-encryption)


## *IMPORTANT NOTE!
Please set `APP_DEBUG` to `false` on Production to disable [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar).


## Packages Used (Outside of Laravel)
- [intervention/image](https://github.com/Intervention/image) - used for generate thumbnail image
- [yajra/laravel-datatables-oracle](https://github.com/yajra/laravel-datatables) - used for display a list of data in a table
- [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) - used for debugging
- [defuse/php-encryption](https://github.com/defuse/php-encryption) - used for encrypting data with a key or password in PHP
- [jfcherng/php-diff](https://github.com/jfcherng/php-diff) - used for generating diff between two strings
- [maatwebsite/excel](https://github.com/Maatwebsite/Laravel-Excel) - used for export & import Excel data
- [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) - used to send HTTP requests and trivial to integrate with web services


## Custom Functions
- `CustomFunction.php` in `app\Libraries\` which is automatically called in the web load as it is set in `composer.json`
- `cheatsheet_form.blade.php` in `resources\views\admin\core\dev` is a guide for using custom function `set_input_form` that generate form element with validations easily
- `Helper.php` in `app\Libraries\` that can be called in Controller/View using line code `use App\Libraries\Helper;` for call some helper functions
- `TheHelper.php` in `app\Libraries\`
- `thehelper.js` in `public\js\` which contains some helper functions


## Maintenance Mode
When your application is in maintenance mode, a custom view will be displayed for all requests into your application. This makes it easy to "disable" your application while it is updating or when you are performing maintenance. A maintenance mode check is included in the default middleware stack for your application. If the application is in maintenance mode, an HttpException will be thrown with a status code of 503.

To enable maintenance mode, simply execute the `down` Artisan command:
```
php artisan down
```
To disable maintenance mode, use the `up` command:
```
php artisan up
```

Even while in maintenance mode, specific IP addresses or networks may be allowed to access the application using the command
```
php artisan down --allow=127.0.0.1 --allow=192.168.0.0/16
```

Source: [Laravel Documentations](https://laravel.com/docs/7.x/configuration#maintenance-mode)


## Bugs, Improvements & Security Vulnerabilities
If you discover a bug or security vulnerability within this project, please send an email to Vicky Budiman at [vicky@kiniditech.com](mailto:vicky@kiniditech.com). All requests will be addressed promptly.


## License
Siorensys is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


## Credits
- Vicky Budiman (https://github.com/vickzkater)
- Laravel (https://github.com/laravel/laravel)
- ColorlibHQ (https://github.com/ColorlibHQ/gentelella)


<p align="center">Brought to you by</p>
<p align="center"><img src="https://hosting.kiniditech.com/kiniditech_logo.png" width="200" alt="KINDI Tech"></p>
<p align="center">KINIDI Tech</p>
