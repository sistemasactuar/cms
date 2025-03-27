<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class WelcomeController extends Controller
{
    public function index()
    {
        $apps = Application::orderBy('name', 'asc')->get();
        return view('welcome', compact('apps'));
    }
}
