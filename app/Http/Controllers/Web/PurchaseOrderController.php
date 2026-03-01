<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return view('purchase-orders.index');
    }

    public function create()
    {
        return view('purchase-orders.create');
    }

    public function show($id)
    {
        return view('purchase-orders.show', compact('id'));
    }

    public function edit($id)
    {
        return view('purchase-orders.edit', compact('id'));
    }
}
