<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class HotelController extends Controller
{
    public function index()
    {
        return Inertia::render('Hoteles/Index');
    }
}
