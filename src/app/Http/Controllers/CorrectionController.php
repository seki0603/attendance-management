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
                'user_id'       => auth()->id(),
                'work_date'     => $attendance->work_date,
                'clock_in'      => $clockIn,
                'clock_out'     => $clockOut,
                'note'          => $request->note,
                'status'        => '承認待ち',
            ]);

            if ($request->filled('break_start') && $request->filled('break_end')) {
                $breakStart = Carbon::parse($request->break_start);
                $breakEnd = Carbon::parse($request->break_end);

                CorrectionBreak::create([
                    'correction_request_id' => $correction->id,
                    'break_start' => $breakStart,
                    'break_end' => $breakEnd,
                ]);
            }
        });

        return redirect()->route('attendance.detail', $attendanceId)->with('message', '修正申請を送信しました');
    }
}
