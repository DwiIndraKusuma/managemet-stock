<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    public function index()
    {
        return view('inventory.index');
    }

    public function show($id)
    {
        return view('inventory.show', compact('id'));
    }
}
