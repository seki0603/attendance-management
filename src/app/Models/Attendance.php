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

    // アクセサ
    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->work_date)->format('m/d(D)');
    }
}
