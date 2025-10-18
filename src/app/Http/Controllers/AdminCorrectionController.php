<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminCorrectionController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $tab = $request->get('tab', 'waiting');

        $query = CorrectionRequest::with(['user', 'attendance']);

        if ($tab === 'completed') {
            $requests = $query->where('status', '承認済み')->get();
        } else {
            $requests = $query->where('status', '承認待ち')->get();
        }

        $records = $requests->map(function ($request) {
            return [
                'status' => $request->status,
                'name' => $request->user->name,
                'work_date' => optional($request->attendance)->work_date
                    ? Carbon::parse($request->attendance->work_date)->format('Y/m/d')
                    : '-',
                'note' => $request->note ?? '',
                'created_at' => $request->created_at->format('Y/m/d'),
                'detail_url' => route('admin.correction.approve', ['attendance_correct_request_id' => $request->id]),
            ];
        });

        return view('correction.admin-list', compact('records', 'tab'));
    }

    public function showApproveForm($attendance_correct_request_id)
    {
        $request = CorrectionRequest::with(['user', 'attendance', 'correctionBreaks'])
            ->findOrFail($attendance_correct_request_id);

        $attendance = $request->attendance;

        if ($request->correctionBreaks->isNotEmpty()) {
            $breakSource = $request->correctionBreaks;
        } else {
            $breakSource = collect();
        }

        $formattedBreaks = $breakSource->map(function ($break, $index) {
            $index++;
            return [
                'break_start' => $break->break_start ? Carbon::parse($break->break_start)->format('H:i') : '',
                'break_end'   => $break->break_end   ? Carbon::parse($break->break_end)->format('H:i')   : '',
            ];
        });


        $nextIndex = $formattedBreaks->count() + 1;

        $date = Carbon::parse($attendance->work_date);

        $viewData = [
            'id' => $request->id,
            'status' => $request->status,
            'name'       => $request->user->full_name,
            'year'       => $date->format('Y年'),
            'month_day'  => $date->format('n月j日'),
            'clock_in'   => $request->clock_in ? Carbon::parse($request->clock_in)->format('H:i') : '',
            'clock_out'  => $request->clock_out ? Carbon::parse($request->clock_out)->format('H:i') : '',
            'breaks'     => $formattedBreaks,
            'next_index' => $nextIndex,
            'note'       => $request->note ?? '',
        ];

        return view('correction.approve', compact('request', 'attendance', 'viewData'));
    }

    public function update($id)
    {
        $request = CorrectionRequest::with(['attendance.breaks', 'correctionBreaks'])
            ->findOrFail($id);

        DB::transaction(function () use ($request) {
            $request->status = '承認済み';
            $request->save();

            $attendance = $request->attendance;

            // 出退勤の申請があれば更新
            $attendance->clock_in = $request->clock_in ?? $attendance->clock_in;
            $attendance->clock_out = $request->clock_out ?? $attendance->clock_out;
            $attendance->save();

            $attendance->breaks()->delete();

            // 修正申請に休憩が含まれていれば再登録
            if ($request->correctionBreaks->isNotEmpty()) {
                foreach ($request->correctionBreaks as $correctionBreak) {
                    $attendance->breaks()->create([
                        'break_start' => $correctionBreak->break_start,
                        'break_end'   => $correctionBreak->break_end,
                    ]);
                }
            }

            $attendance->load('breaks');

            // 休憩合計を再計算
            $totalBreakTime = $attendance->breaks->reduce(function ($carry, $break) {
                if ($break->break_start && $break->break_end) {
                    return $carry + Carbon::parse($break->break_end)
                        ->diffInMinutes(Carbon::parse($break->break_start));
                }
                return $carry;
            }, 0);

            // 労働合計の再計算
            $totalWorkTime = 0;
            if ($attendance->clock_in && $attendance->clock_out) {
                $totalWorkTime = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in)) - $totalBreakTime;
            }

            $attendance->update([
                'total_break_time' => $totalBreakTime,
                'total_work_time' => max($totalWorkTime, 0),
            ]);
        });

        return redirect()->route('admin.correction.approve', ['attendance_correct_request_id' => $id])->with('message', '修正申請を承認しました');
    }
}
