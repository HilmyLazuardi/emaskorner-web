<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'namespace' => 'API'
], function () {
    Route::get('/', 'ApiController@index');

    Route::post('/upload-image', 'ApiController@upload_image');
    Route::post('/upload-image-base64', 'ApiController@upload_image_base64');
    Route::post('/delete-uploaded-image', 'ApiController@delete_uploaded_image');
});
