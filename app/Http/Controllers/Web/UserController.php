<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }

    public function create()
    {
        return view('users.create');
    }

    public function show($id)
    {
        return view('users.show', compact('id'));
    }

    public function edit($id)
    {
        return view('users.edit', compact('id'));
    }
}
