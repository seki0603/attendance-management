<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    private const WEEKDAY_LABELS = ['日', '月', '火', '水', '木', '金', '土'];

    public function index()
    {
        $user = auth()->user();
        $now = now();
        $today = $now->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        // ステータス判定
        if (!$attendance) {
            $attendanceStatus = '勤務外';
        } elseif ($attendance->clock_out) {
            $attendanceStatus = '退勤済';
        } elseif (session('on_break')) {
            $attendanceStatus = '休憩中';
        } elseif ($attendance->clock_in && !$attendance->clock_out) {
            $attendanceStatus = '出勤中';
        } else {
            $attendanceStatus = '勤務外';
        }

        // 表示用データをまとめる
        $viewData = [
            'status' => $attendanceStatus,
            'date' => $now->format('Y年m月d日'),
            'weekday' => '(' . self::WEEKDAY_LABELS[$now->dayOfWeek] . ')',
            'time' => $now->format('H:i'),
        ];

        return view('attendance.index', compact('attendance', 'viewData'));
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

            // 休憩のたびに合計を更新
            $totalBreak = BreakTime::where('attendance_id', $attendance->id)
                ->whereNotNull('break_start')
                ->whereNotNull('break_end')
                ->get()
                ->sum(fn($break) => $break->break_end->diffInMinutes($break->break_start));

            $attendance->update(['total_break_time' => $totalBreak]);
            session(['on_break' => false]);
        }

        // 退勤処理
        if ($request->has('clock_out')) {
            $attendance->update(['clock_out' => now()]);

            $totalWork = $attendance->clock_in && $attendance->clock_out
                ? $attendance->clock_in->diffInMinutes($attendance->clock_out)
                : null;

            $attendance->update([
                'total_work_time'  => $totalWork
                ? max($totalWork - $attendance->total_break_time, 0)
                : null,
            ]);
        }

        return redirect()->route('attendance.index');
    }

    public function showList(Request $request)
    {
        $user = auth()->user();

        // 対象月（指定 or 現在月）
        $monthParam = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $monthParam);
        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate = $currentMonth->copy()->endOfMonth();

        // 勤怠情報取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->work_date)->format('Y-m-d'));

        // 一覧データ生成
        $records = collect();
        $day = $startDate->copy();

        while ($day->lte($endDate)) {
            $dateKey = $day->format('Y-m-d');
            $attendance = $attendances->get($dateKey);

            $records->push([
                'date_str' => $day->format('m/d'),
                'weekday' => self::WEEKDAY_LABELS[$day->dayOfWeek],
                'clock_in' => $attendance?->clock_in?->format('H:i') ?? '',
                'clock_out' => $attendance?->clock_out?->format('H:i') ?? '',
                'total_break_time' => $attendance?->total_break_time
                    ? $this->formatMinutes($attendance->total_break_time)
                    : '',
                'total_work_time' => $attendance?->total_work_time
                    ? $this->formatMinutes($attendance->total_work_time)
                    : '',
                'detail_url' => $attendance
                    ? route('attendance.detail', $attendance->id)
                    : null,
            ]);

            $day->addDay();
        }

        return view('attendance.list', [
            'records' => $records,
            'displayMonth' => $currentMonth->format('Y/m'),
            'previousMonthUrl' => route('attendance.list', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]),
            'nextMonthUrl' => route('attendance.list', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]),
        ]);
    }

    public function showDetail($id)
    {
        $attendance = Attendance::with([
            'user',
            'breaks',
            'correctionRequests.correctionBreaks',
        ])->findOrFail($id);

        $pending = $attendance->correctionRequests
            ->where('status', '承認待ち')
            ->sortByDesc('id')
            ->first();

        $source = $pending ?? $attendance;

        if ($pending) {
            $breakSource = $pending->correctionBreaks->isNotEmpty()
                ? $pending->correctionBreaks
                : collect(); // 修正申請があれば空でも元のbreaksを表示しない
        } else {
            $breakSource = $attendance->breaks;
        }

        // 休憩の表示用データ
        $formattedBreaks = collect($breakSource)->map(function ($break, $index) {
            $index++;
            return [
                'break_start' => old('break_start_' . $index, $break->break_start ? Carbon::parse($break->break_start)->format('H:i') : ''),
                'break_end'   => old('break_end_' . $index,   $break->break_end   ? Carbon::parse($break->break_end)->format('H:i')   : ''),
            ];
        })->values();

        $nextIndex = $formattedBreaks->count() + 1;

        $nextBreak = [
            'break_start' => old('break_start_' . $nextIndex),
            'break_end' => old('break_end_' . $nextIndex),
        ];

        $date = Carbon::parse($attendance->work_date);

        $viewData = [
            'name' => $attendance->user->full_name,
            'year' => $date->format('Y年'),
            'month_day' => $date->format('n月j日'),
            'clock_in' => old('clock_in',  $source->clock_in ? Carbon::parse($source->clock_in)->format('H:i') : ''),
            'clock_out' => old('clock_out', $source->clock_out ? Carbon::parse($source->clock_out)->format('H:i') : ''),
            'breaks' => $formattedBreaks,
            'next_break' => $nextBreak,
            'next_index' => $nextIndex,
            'note' => old('note', $pending?->note ?? ''),
            'is_pending' => (bool) $pending,
        ];

        if (auth()->user()->role === 'admin') {
            return view('admin.attendance-detail', compact('attendance', 'viewData'));
        }

        return view('attendance.detail', compact('attendance', 'viewData'));
    }

    private function formatMinutes($time): string
    {
        if ($time === null) return '';
        $minutes = is_float($time) ? (int) round($time * 60) : (int) $time;
        return sprintf('%d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
