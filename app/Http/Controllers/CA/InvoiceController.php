<?php

namespace App\Http\Controllers\CA;

use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
  public function index()
  {
    return view('CA.invoices');
  }
}
