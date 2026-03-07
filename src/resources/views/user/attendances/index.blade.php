@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/components/page-title.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/table-header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/list-table.css') }}">
@endpush

@section('page')
    <div class="page-inner">
        <x-ui.page-title>
            勤怠一覧
        </x-ui.page-title>
        <x-attendance.table-header
            :previousUrl="$previousUrl"
            previousText="前月"
            :nextUrl="$nextUrl"
            nextText="翌月"
            :label="$month->isoFormat('Y/MM')"
        />
        <x-ui.list-table
            :headers="['日付', '出勤', '退勤', '休憩', '合計', '詳細']"
            :rows="$rows"
        />
    </div>
@endsection