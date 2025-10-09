<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CorrectionRequest as Correction;
use App\Models\CorrectionBreak;
use App\Http\Requests\CorrectionRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CorrectionController extends Controller
{
    public function store(CorrectionRequest $request, $attendanceId)
    {
        $attendance = Attendance::findOrFail($attendanceId);

        DB::transaction(function () use ($request, $attendance) {
            $clockIn = Carbon::parse($request->clock_in);
            $clockOut = Carbon::parse($request->clock_out);

            $correction = Correction::firstOrCreate([
                'attendance_id' => $attendance->id,
                'user_id' => auth()->id(),
                'work_date' => $attendance->work_date,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'note' => $request->note,
                'status' => '承認待ち',
            ]);

            $breakPairs = collect($request->all())
                ->filter(fn($v, $k) => preg_match('/^break_start_\d+$/', $k))
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
                CorrectionBreak::create([
                    'correction_request_id' => $correction->id,
                    'break_start' => Carbon::parse($pair['start']),
                    'break_end'   => Carbon::parse($pair['end']),
                ]);
            }
        });

        return redirect()->route('attendance.detail', $attendanceId)->with('message', '修正申請を送信しました');
    }

    public function showList()
    {
        return view('correction.user-list');
    }
}
