<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class RequestController extends Controller
{
    public function index()
    {
        return view('requests.index');
    }

    public function create()
    {
        return view('requests.create');
    }

    public function show($id)
    {
        return view('requests.show', compact('id'));
    }

    public function edit($id)
    {
        return view('requests.edit', compact('id'));
    }
}
