<?php

namespace App\Http\Controllers\CA;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
  public function index()
  {
    return view('CA.dashboard');
  }
}
