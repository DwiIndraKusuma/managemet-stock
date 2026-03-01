<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class StockOpnameController extends Controller
{
    public function index()
    {
        return view('stock-opnames.index');
    }

    public function create()
    {
        return view('stock-opnames.create');
    }

    public function show($id)
    {
        return view('stock-opnames.show', compact('id'));
    }

    public function edit($id)
    {
        return view('stock-opnames.edit', compact('id'));
    }
}
