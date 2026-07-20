<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class UserWebController extends BaseController
{
    public function loginPage()
    {
        return view('auth/login');
    }

    public function index()
    {
        return view('users/index');
    }

    public function create()
    {
        return view('users/create');
    }

    public function detail($id)
    {
        return view('users/detail', ['id' => $id]);
    }
}
