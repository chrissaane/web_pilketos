<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VotingSchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = VotingSchedule::orderBy('time')->get();
        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        return view('admin.schedules.index');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.schedules.index')->with('success', 'Schedule added (stub).');
    }

    public function edit($id)
    {
        return view('admin.schedules.index');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('admin.schedules.index')->with('success', 'Schedule updated (stub).');
    }

    public function destroy($id)
    {
        return redirect()->route('admin.schedules.index')->with('success', 'Schedule removed (stub).');
    }
}
