<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'requested_clock_in',
        'requested_clock_out',
        'requested_note',
        'status',
        'approved_by',
        'requested_at',
        'approved_at',
    ];

    protected $casts = [
        'requested_clock_in' => 'datetime',
        'requested_clock_out' => 'datetime',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function requestBreaks()
    {
        return $this->hasMany(AttendanceCorrectRequestBreak::class);
    }
}
