<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'total_break_time',
        'total_work_time'
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function status()
    {
        return $this->hasOne(AttendanceStatus::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    // 曜日表示のアクセサ
    public function getJpWeekdayAttribute()
    {
        if (!$this->work_date) return '';

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[\Carbon\Carbon::parse($this->work_date)->dayOfWeek];
        return " ({$weekday}) ";
    }
}
