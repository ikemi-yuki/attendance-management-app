<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_request_id',
        'attendance_break_id',
        'requested_break_start',
        'requested_break_end'
    ];

    protected $casts = [
        'requested_break_start' => 'datetime',
        'requested_break_end' => 'datetime',
    ];

    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }

    public function attendanceBreak()
    {
        return $this->belongsTo(AttendanceBreak::class);
    }
}
