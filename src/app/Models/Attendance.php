<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'note'
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function attendanceCorrectRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }

    public function pendingRequest()
    {
        return $this->hasOne(AttendanceCorrectRequest::class)
            ->where('status', AttendanceCorrectRequest::STATUS_PENDING);
    }

    public function getTotalBreakSecondsAttribute(): int
    {
        return $this->breaks
        ->whereNotNull('break_start')
        ->whereNotNull('break_end')
        ->sum(fn ($break) =>
            $break->break_start->diffInSeconds($break->break_end)
        );
    }

    public function getWorkSecondsAttribute(): int
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }
        return $this->clock_in->diffInSeconds($this->clock_out) - $this->total_break_seconds;
    }
}
