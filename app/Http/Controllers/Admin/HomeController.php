<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Display home page.
     *
     * @return view()
     */
    public function index()
    {
        return view('admin.home');
    }
}
