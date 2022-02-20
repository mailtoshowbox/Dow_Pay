<?php

namespace App\Http\Controllers;

class LoginController extends Controller
{
   

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
    
        return view('dashboard');
    }
}
