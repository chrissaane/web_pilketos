<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function index()
    {
        return view('admin.exports.index');
    }

    public function run(Request $request)
    {
        // Stub: implement real export logic later
        return redirect()->route('admin.export.index')->with('success','Export started (stub).');
    }
}
