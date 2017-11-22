<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    function store(Request $request)
    {
        dd($request->data);
    }
}
