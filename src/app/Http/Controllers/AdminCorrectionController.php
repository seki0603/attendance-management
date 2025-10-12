<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
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

        $breakSource = $request->correctionBreaks->isNotEmpty()
            ? $request->correctionBreaks
            : $request->attendance->breaks;

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
}
