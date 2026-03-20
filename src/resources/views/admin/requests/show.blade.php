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
        <x-ui.detail-view-table
            :attendance="$attendanceRequest->attendance"
            :attendanceRequest="$attendanceRequest"
            :requestBreaks="$attendanceRequest->requestBreaks"
        />
        @if($hasPendingRequest && $attendanceRequest)
            <form class="form" action="{{ route('admin.request.approve', ['attendance_correct_request_id' => $attendanceRequest->id] ) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="form__button">
                    <button class="form__button-submit" type="submit">承認</button>
                </div>
            </form>
        @else
            <p class="approve-message">承認済み</p>
        @endif
    </div>
@endsection