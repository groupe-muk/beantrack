<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}


use Illuminate\Http\Request;
use App\Models\Inventory;

class InventoryController extends Controller
{
    public function index()
    {
        return Inventory::all();
    }

    public function store(Request $request)
    {
        $item = Inventory::create($request->all());
        return response()->json($item, 201);
    }
}