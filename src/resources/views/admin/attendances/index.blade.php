@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/components/page-title.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/table-header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/list-table.css') }}">
@endpush

@section('page')
    <div class="page-inner">
        <x-ui.page-title>
            {{ $date->isoFormat('YYYY年M月D日') }}の勤怠
        </x-ui.page-title>
        <x-attendance.table-header
            :previousUrl="$previousUrl"
            previousText="前日"
            :nextUrl="$nextUrl"
            nextText="翌日"
            :label="$date->isoFormat('Y/MM/DD')"
        />
        <x-ui.list-table
            :headers="['名前', '出勤', '退勤', '休憩', '合計', '詳細']"
            :rows="$rows"
        />
    </div>
@endsection