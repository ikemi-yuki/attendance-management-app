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
        <x-attendances.table-header
            :previousUrl="$previousUrl"
            previousText="前日"
            :nextUrl="$nextUrl"
            nextText="翌日"
            :label="$date->isoFormat('Y/MM/DD')"
        />
        <div class="table">
            <x-ui.list-table>
                <x-slot name="header">
                    <th class="table__header">名前</th>
                    <th class="table__header">出勤</th>
                    <th class="table__header">退勤</th>
                    <th class="table__header">休憩</th>
                    <th class="table__header">合計</th>
                    <th class="table__header">詳細</th>
                </x-slot>
                @foreach ($rows as $row)
                    <tr class="table__row">
                        <td class="table__data">{{ $row->name() }}</td>
                        <td class="table__data">{{ $row->clockIn() }}</td>
                        <td class="table__data">{{ $row->clockOut() }}</td>
                        <td class="table__data">{{ $row->breakTime() }}</td>
                        <td class="table__data">{{ $row->workTime() }}</td>
                        <td class="table__data">
                            <a class="table__link" href="{{ $row->detailUrl() }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </x-ui.list-table>
        </div>
    </div>
@endsection