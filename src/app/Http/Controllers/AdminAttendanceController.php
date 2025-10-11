<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Http\Requests\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    private const WEEKDAY_LABELS = ['日', '月', '火', '水', '木', '金', '土'];

    public function index(Request $request)
    {
        $dateParam = $request->input('date', now()->toDateString());
        $currentDate = Carbon::parse($dateParam)->startOfDay();

        $users = User::orderBy('id')->get();

        $attendances = Attendance::whereDate('work_date', $currentDate->toDateString())
            ->with('user')
            ->get()
            ->keyBy('user_id');

        $records = $users->map(function ($user) use ($attendances) {
            $attendance = $attendances->get($user->id);

            return [
                'user_name' => $user->full_name,
                'clock_in' => $attendance?->clock_in?->format('H:i') ?? '',
                'clock_out' => $attendance?->clock_out?->format('H:i') ?? '',
                'total_break_time' => $attendance?->total_break_time
                    ? $this->formatMinutes($attendance->total_break_time)
                    : '',
                'total_work_time' => $attendance?->total_work_time
                    ? $this->formatMinutes($attendance->total_work_time)
                    : '',
                'detail_url' => $attendance
                    ? route('admin.attendance.detail', $attendance->id)
                    : null,
            ];
        });

        return view('admin.attendance-list', [
            'records' => $records,
            'displayDate' => $currentDate->format('Y年n月j日'),
            'displayDateSlash' => $currentDate->format('Y/m/d'),
            'previousDayUrl' => route('admin.attendance.list', [
                'date' => $currentDate->copy()->subDay()->toDateString(),
            ]),
            'nextDayUrl' => route('admin.attendance.list', [
                'date' => $currentDate->copy()->addDay()->toDateString(),
            ]),
        ]);
    }

    public function update(CorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        DB::transaction(function () use ($attendance, $request) {
            $clockIn = Carbon::parse($request->clock_in);
            $clockOut = Carbon::parse($request->clock_out);

            $attendance->update([
                'clock_in'  => $clockIn,
                'clock_out' => $clockOut,
            ]);

            // 休憩を削除して再登録
            $attendance->breaks()->delete();

            $breakPairs = collect($request->all())
                ->filter(function ($value, $key) {
                    return preg_match('/^break_start_\d+$/', $key);
                })
                ->map(function ($start, $key) use ($request) {
                    $num = (int) str_replace('break_start_', '', $key);
                    return [
                        'start' => $start,
                        'end'   => $request->input("break_end_{$num}"),
                    ];
                })
                ->filter(fn($pair) => $pair['start'] && $pair['end'])
                ->values();

            foreach ($breakPairs as $pair) {
                $attendance->breaks()->create([
                    'break_start' => Carbon::parse($pair['start']),
                    'break_end'   => Carbon::parse($pair['end']),
                ]);
            }

            // 合計計算
            $totalBreakMinutes = $breakPairs->sum(function ($pair) {
                return Carbon::parse($pair['end'])->diffInMinutes(Carbon::parse($pair['start']));
            });

            $totalWorkMinutes = $clockOut->diffInMinutes($clockIn) - $totalBreakMinutes;

            $attendance->update([
                'total_break_time' => $totalBreakMinutes,
                'total_work_time'  => $totalWorkMinutes,
            ]);
        });

        return redirect()
            ->route('admin.attendance.detail', $attendance->id)
            ->with('message', '勤怠情報を修正しました。');
    }



    private function formatMinutes(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }
}
