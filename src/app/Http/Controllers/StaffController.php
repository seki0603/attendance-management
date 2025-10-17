<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class StaffController extends Controller
{
    private const WEEKDAY_LABELS = ['日', '月', '火', '水', '木', '金', '土'];

    public function index()
    {
        $users = User::where('role', 'user')
            ->orderBy('id')
            ->get();

        $records = $users->map(function ($user) {
            return [
                'name' => $user->name,
                'email' => $user->email,
                'monthly_url' => route('admin.attendance.staff.list', ['id' => $user->id]),
            ];
        });

        return view('admin.staff.list', compact('records'));
    }

    public function showAttendanceList(Request $request, $id)
    {
        $user = User::where('role', 'user')->findOrFail($id);

        $monthParam = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $monthParam);
        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate   = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->work_date)->format('Y-m-d'));

        $records = collect();
        $day = $startDate->copy();

        while ($day->lte($endDate)) {
            $dateKey = $day->format('Y-m-d');
            $attendance = $attendances->get($dateKey);

            $records->push([
                'date_str' => $day->format('m/d'),
                'weekday'  => self::WEEKDAY_LABELS[$day->dayOfWeek],
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

        return view('admin.staff.attendance-list', [
            'records'          => $records,
            'displayMonth'     => $currentMonth->format('Y/m'),
            'displayName'      => $user->display_name,
            'previousMonthUrl' => route('admin.attendance.staff.list', [
                'id' => $user->id,
                'month' => $currentMonth->copy()->subMonth()->format('Y-m'),
            ]),
            'nextMonthUrl' => route('admin.attendance.staff.list', [
                'id' => $user->id,
                'month' => $currentMonth->copy()->addMonth()->format('Y-m'),
            ]),
            'csvExportUrl' => route('admin.attendance.staff.csv', [
                'id' => $user->id,
                'month' => $currentMonth->format('Y-m'),
            ]),
        ]);
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::where('role', 'user')->findOrFail($id);

        $monthParam = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $monthParam);
        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate   = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->work_date)->format('Y-m-d'));

        // CSV出力用のレスポンス生成
        $response = new StreamedResponse(function () use ($attendances, $startDate, $endDate) {
            $stream = fopen('php://output', 'w');

            stream_filter_prepend($stream, 'convert.iconv.UTF-8/CP932');

            fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計']);

            $day = $startDate->copy();
            while ($day->lte($endDate)) {
                $dateKey = $day->format('Y-m-d');
                $attendance = $attendances->get($dateKey);

                fputcsv($stream, [
                    $day->format('Y/m/d') . ' (' . self::WEEKDAY_LABELS[$day->dayOfWeek] . ')',
                    $attendance?->clock_in?->format('H:i') ?? '',
                    $attendance?->clock_out?->format('H:i') ?? '',
                    $attendance?->total_break_time
                        ? $this->formatMinutes($attendance->total_break_time)
                        : '',
                    $attendance?->total_work_time
                        ? $this->formatMinutes($attendance->total_work_time)
                        : '',
                ]);

                $day->addDay();
            }

            fclose($stream);
        });

        $fileName = sprintf(
            'attendance_%s_%s.csv',
            $currentMonth->format('Ym'),
            $user->name
        );

        $response->headers->set('Content-Type', 'text/csv; charset=Shift-JIS');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }
}
