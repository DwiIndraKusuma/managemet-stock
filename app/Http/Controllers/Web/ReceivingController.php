<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class ReceivingController extends Controller
{
    public function index()
    {
        return view('receivings.index');
    }

    public function create()
    {
        return view('receivings.create');
    }

    public function show($id)
    {
        return view('receivings.show', compact('id'));
    }

    public function edit($id)
    {
        return view('receivings.edit', compact('id'));
    }
}
