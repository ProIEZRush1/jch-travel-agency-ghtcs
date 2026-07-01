<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AutoController extends Controller
{
    public function index()
    {
        return Inertia::render('Autos/Index');
    }
}
