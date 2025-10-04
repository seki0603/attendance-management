<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;

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

        if ($request->has('clock_in')) {
            $attendance->update(['clock_in' => now()]);
        }

        // 休憩入処理
        if ($request->has('break_start')) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => now(),
            ]);
            session(['on_break' => true]);
        }

        // 休憩戻処理
        if ($request->has('break_end')) {
            $break = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest()
                ->first();

            if ($break) {
                $break->update(['break_end' => now()]);
            }
            session(['on_break' => false]);
        }

        // 退勤処理
        if ($request->has('clock_out')) {
            $attendance->update(['clock_out' => now()]);

            $totalWork = $attendance->clock_in && $attendance->clock_out
                ? $attendance->clock_in->diffInMinutes($attendance->clock_out)
                : null;

            $totalBreak = BreakTime::where('attendance_id', $attendance->id)
                ->whereNotNull('break_start')
                ->whereNotNull('break_end')
                ->get()
                ->sum(fn($b) => $b->break_start->diffInMinutes($b->break_end));

            $attendance->update([
                'total_break_time' => $totalBreak,
                'total_work_time'  => $totalWork ? max($totalWork - $totalBreak, 0) : null,
            ]);
        }

        return redirect()->route('attendance.index');
    }

    public function showList(Request $request)
    {
        $user = auth()->user();

        // 現在の月 or パラメータ指定
        $month = $request->input('month', now()->format('Y-m'));
        $current = Carbon::createFromFormat('Y-m', $month);
        $start = $current->copy()->startOfMonth();
        $end   = $current->copy()->endOfMonth();

        // 該当月の勤怠情報取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->work_date)->format('Y-m-d'));


        $records = collect();
        $day = $start->copy();

        while ($day->lte($end)) {
            $key = $day->format('Y-m-d');
            $attendance = $attendances->get($key);

            $records->push([
                'date_str' => $day->format('m/d'),
                'weekday'  => ['日', '月', '火', '水', '木', '金', '土'][$day->dayOfWeek],
                'clock_in'  => $attendance?->clock_in ? $attendance->clock_in->format('H:i') : '',
                'clock_out' => $attendance?->clock_out ? $attendance->clock_out->format('H:i') : '',
                'total_break_time' => $attendance?->total_break_time
                    ? gmdate('H:i', $attendance->total_break_time * 60)
                    : '',
                'total_work_time'  => $attendance?->total_work_time
                    ? gmdate('H:i', $attendance->total_work_time * 60)
                    : '',
                'detail_url'  => $attendance ? route('attendance.detail', $attendance->id) : null,
            ]);

            $day->addDay();
        }

        return view('attendance.list', compact('records', 'current'));
    }

    public function showDetail($id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        return view('attendance.detail', compact('attendance'));
    }
}
