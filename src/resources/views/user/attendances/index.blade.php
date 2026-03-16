@extends('layouts.user')

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
        <div class="table">
            <x-ui.list-table>
                <x-slot name="header">
                    <th class="table__header">日付</th>
                    <th class="table__header">出勤</th>
                    <th class="table__header">退勤</th>
                    <th class="table__header">休憩</th>
                    <th class="table__header">合計</th>
                    <th class="table__header">詳細</th>
                </x-slot>
                @foreach ($rows as $row)
                    <tr class="table__row">
                        <td class="table__data">{{ $row->date() }}</td>
                        <td class="table__data">{{ $row->clockIn() }}</td>
                        <td class="table__data">{{ $row->clockOut() }}</td>
                        <td class="table__data">{{ $row->breakTime() }}</td>
                        <td class="table__data">{{ $row->workTime() }}</td>
                        <td class="table__data">
                            <a class="table__link{{ !$row->detailUrl() ? '--disabled' : '' }}" href="{{ $row->detailUrl() ?? '#' }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </x-ui.list-table>
        </div>
    </div>
@endsection