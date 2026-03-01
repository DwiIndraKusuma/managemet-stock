<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        return view('items.index');
    }

    public function create()
    {
        return view('items.create');
    }

    public function show($id)
    {
        return view('items.show', compact('id'));
    }

    public function edit($id)
    {
        return view('items.edit', compact('id'));
    }
}
