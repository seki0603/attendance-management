<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceDetailController extends Controller
{
    public function showDetail($id)
    {
        $attendance = Attendance::with([
            'user',
            'breaks',
            'correctionRequests.correctionBreaks',
        ])->findOrFail($id);

        // 承認待ち申請を取得
        $pending = $attendance->correctionRequests
            ->where('status', '承認待ち')
            ->sortByDesc('id')
            ->first();

        // 承認済み申請を取得
        $approved = $attendance->correctionRequests
            ->where('status', '承認済み')
            ->sortByDesc('id')
            ->first();

        if ($pending) {
            $source = $pending;
            $breakSource = $pending->correctionBreaks;
            $note = $pending->note;
            $isPending = true;
        } elseif ($approved) {
            $source = $approved;
            $breakSource = $approved->correctionBreaks;
            $note = $approved->note;
            $isPending = false;
        } else {
            $source = $attendance;
            $breakSource = $attendance->breaks;
            $note = '';
            $isPending = false;
        }

        // 休憩データの整形
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
            'break_end'   => old('break_end_' . $nextIndex),
        ];

        $date = Carbon::parse($attendance->work_date);

        $viewData = [
            'name'       => $attendance->user->full_name,
            'year'       => $date->format('Y年'),
            'month_day'  => $date->format('n月j日'),
            'clock_in'   => old('clock_in',  $source->clock_in ? Carbon::parse($source->clock_in)->format('H:i') : ''),
            'clock_out'  => old('clock_out', $source->clock_out ? Carbon::parse($source->clock_out)->format('H:i') : ''),
            'breaks'     => $formattedBreaks,
            'next_break' => $nextBreak,
            'next_index' => $nextIndex,
            'note'       => old('note', $note),
            'is_pending' => $isPending,
        ];

        if (auth()->user()->role === 'admin') {
            return view('admin.attendance-detail', compact('attendance', 'viewData'));
        }

        return view('attendance.detail', compact('attendance', 'viewData'));
    }
}
