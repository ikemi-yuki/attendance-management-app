<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\ViewModels\AdminStaffRowViewModel;

class StaffController extends Controller
{
    public function index()
    {
        $rows = User::staff()
            ->get()
            ->map(fn ($user) => new AdminStaffRowViewModel($user)
        );

        return view('admin.staff.index', ['rows' => $rows,]);
    }
}
