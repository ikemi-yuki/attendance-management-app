<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BreakService;

class BreakController extends Controller
{
    private $breakService;

    public function __construct(BreakService $breakService)
    {
        $this->breakService = $breakService;
    }

    public function start()
    {
        $this->breakService->start();

        return redirect()->route('clock');
    }

    public function end()
    {
        $this->breakService->end();

        return redirect()->route('clock');
    }
}
