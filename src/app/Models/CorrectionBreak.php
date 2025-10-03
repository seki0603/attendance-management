<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'break_start',
        'break_end'
    ];

    // リレーション
    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class);
    }
}
