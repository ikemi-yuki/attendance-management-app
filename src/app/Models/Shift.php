<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'scheduled_clock_in',
        'scheduled_clock_out',
        'note'
    ];

    protected $casts = [
        'work_date' => 'date',
        'scheduled_clock_in' => 'datetime',
        'scheduled_clock_out' => 'datetime',
    ];

    public function attendances()
    {
        return $this->hasOne(Attendance::class);
    }

    public function breaks()
    {
        return $this->hasMany(ShiftBreak::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
