@extends('layouts.admin')

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
            <p class="pending-message">
                *承認待ちのため修正はできません。
            </p>
        @else
            <x-ui.detail-edit-table
                :action="route('admin.attendance.update', ['id' => $attendance->id])"
                :method="method_field('PATCH')"
                :attendance="$attendance"
                :breaks="$attendance->breaks"
            />
        @endif
    </div>
@endsection