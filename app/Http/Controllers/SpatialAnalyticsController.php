<?php

namespace App\Http\Controllers;

class SpatialAnalyticsController extends Controller
{
    public function index()
    {
        return redirect()->route('developments.index');
    }
}
