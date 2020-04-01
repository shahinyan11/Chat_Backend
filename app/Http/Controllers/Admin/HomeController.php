<?php

namespace App\Http\Controllers\Admin;

class HomeController extends AdminController
{
    public function index()
    {
        return view('admin.home');
    }
}
