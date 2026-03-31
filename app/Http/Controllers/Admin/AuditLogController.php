<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
  public function index(Request $request)
  {
    $companies = Company::all();
    return view('Admin.audit_logs', compact('companies'));
  }
}
