<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'scheduled_break_start',
        'scheduled_break_end'
    ];

    protected $casts = [
        'scheduled_break_start' => 'datetime',
        'scheduled_break_end' => 'datetime',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
