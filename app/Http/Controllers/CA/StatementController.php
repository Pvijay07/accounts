<?php

namespace App\Http\Controllers\CA;

use App\Http\Controllers\Controller;

class StatementController extends Controller
{
  public function index()
  {
    return view('CA.statements');
  }
}
