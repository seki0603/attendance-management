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

        $pending = $attendance->correctionRequests
            ->where('status', '承認待ち')
            ->sortByDesc('id')
            ->first();

        $source = $pending ?? $attendance;

        if ($pending) {
            $breakSource = $pending->correctionBreaks->isNotEmpty()
                ? $pending->correctionBreaks
                : collect();
        } else {
            $breakSource = $attendance->breaks;
        }

        // 休憩の表示用データ生成
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
}
