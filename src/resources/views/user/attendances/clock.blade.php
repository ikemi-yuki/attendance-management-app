@extends('layouts.user')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/clock.css') }}">
@endpush

@section('page')
    <div class="clock">
        <p class="clock__condition">
            @if($status === 'before')
                勤務外
            @elseif($status === 'working')
                出勤中
            @elseif($status === 'on_break')
                休憩中
            @elseif($status === 'finished')
                退勤済
            @endif
        </p>
        <p class="clock__date">{{ now()->isoFormat('YYYY年M月D日(ddd)') }}</p>
        <p class="clock__time">{{ now()->isoFormat('HH:mm') }}</p>
        <div class="clock__form-wrapper">
            @if($status === 'before')
                <form class="clock-in__form" action="{{ route('attendance.clockIn') }}" method="post">
                    @csrf
                    <input class="clock-in__form-input" type="submit" value="出勤">
                </form>
            @elseif($status === 'working')
                <form class="clock-out__form" action="{{ route('attendance.clockOut') }}" method="post">
                    @csrf
                    <input class="clock-out__form-input" type="submit" value="退勤">
                </form>
                <form class="break-start__form" action="{{ route('attendance.breakStart') }}" method="post">
                    @csrf
                    <input class="break-start__form-input" type="submit" value="休憩入">
                </form>
            @elseif($status === 'on_break')
                <form class="break-end__form" action="{{ route('attendance.breakEnd') }}" method="post">
                    @csrf
                    <input class="break-end__form-input" type="submit" value="休憩戻">
                </form>
            @elseif($status === 'finished')
                <p class="clock-out__text">お疲れ様でした。</p>
            @endif
        </div>
    </div>
@endsection