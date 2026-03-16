<?php

namespace App\ViewModels;

use App\Models\User;

class AdminStaffRowViewModel
{
    public function __construct(
        private User $user
    ) {}

    public function name(): string
    {
        return $this->user->name;
    }

    public function email(): string
    {
        return $this->user->email;
    }


    public function monthlyAttendanceUrl(): string
    {
        return route('admin.attendance.monthly', ['id' => $this->user->id]);
    }
}