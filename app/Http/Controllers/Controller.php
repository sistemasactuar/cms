<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Application;
abstract class Controller
{
    public function index()
    {
        $apps = Application::orderBy('name', 'asc')->get();
        return view('welcome', compact('apps'));
    }
}
