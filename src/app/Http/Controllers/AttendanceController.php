<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

            if (!$attendance) {
                $status = '勤務外';
            } elseif ($attendance->clock_out) {
                $status = '退勤済';
            } elseif (session('on_break')) {
                $status = '休憩中';
            } elseif ($attendance->clock_in && !$attendance->clock_out) {
                $status = '出勤中';
            } else {
                $status = '勤務外';
            }

        return view('attendance.index', compact('attendance', 'status'))->with('now', now());
    }

    public function store(AttendanceRequest $request)
    {
        $user = auth()->user();
        $today = now()->toDateString();

        // 出勤処理
        $attendance = Attendance::firstOrCreate([
            'user_id' => $user->id,
            'work_date' => $today,
        ]);

        if ($request->filled('clock_in')) {
            $attendance->update(['clock_in' => now()]);
        }

        // 退勤処理
        if ($request->filled('clock_out')) {
            $attendance->update(['clock_out' => now()]);
        }

        // 休憩入処理
        if ($request->filled('break_start')) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => now(),
            ]);
            session(['on_break' => true]);
        }

        // 休憩戻処理
        if ($request->filled('break_end')) {
            $break = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest()
                ->first();

            if ($break) {
                $break->update(['break_end' => now()]);
            }
            session(['on_break' => false]);
        }

        return redirect()->route('attendance.index');
    }
}
