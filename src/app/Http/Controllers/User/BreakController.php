<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\BreakService;

class BreakController extends Controller
{
    private $breakService;

    public function __construct(BreakService $breakService)
    {
        $this->breakService = $breakService;
    }

    public function breakStart()
    {
        $this->breakService->breakStart(auth()->user());

        return redirect()->route('clock');
    }

    public function breakEnd()
    {
        $this->breakService->breakEnd(auth()->user());

        return redirect()->route('clock');
    }
}
