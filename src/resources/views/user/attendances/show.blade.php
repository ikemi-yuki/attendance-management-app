@extends('layouts.user')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/components/page-title.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/detail-table.css') }}">
@endpush

@section('page')
    <div class="page-inner">
        <x-ui.page-title>
            勤怠詳細
        </x-ui.page-title>
        @if($hasPendingRequest && $attendanceRequest)
            <x-ui.detail-view-table
                :attendance="$attendance"
                :attendanceRequest="$attendanceRequest"
                :requestBreaks="$attendanceRequest->requestBreaks"
            />
            <p class="pending-message">*承認待ちのため修正はできません。</p>
        @else
            <x-ui.detail-edit-table
                :action="route('attendance.store', ['id' => $attendance->id])"
                method=""
                :attendance="$attendance"
                :breaks="$attendance->breaks"
            />
        @endif
    </div>
@endsection