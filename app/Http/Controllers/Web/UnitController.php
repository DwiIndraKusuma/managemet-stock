<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class UnitController extends Controller
{
    public function index()
    {
        return view('units.index');
    }

    public function create()
    {
        return view('units.create');
    }

    public function edit($id)
    {
        return view('units.edit', compact('id'));
    }
}
