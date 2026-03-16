@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/components/page-title.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/list-table.css') }}">
@endpush

@section('page')
    <div class="page-inner">
        <x-ui.page-title>
            スタッフ一覧
        </x-ui.page-title>
        <div class="staff-table">
            <x-ui.list-table>
                <x-slot name="header">
                    <th class="staff-table__header">名前</th>
                    <th class="staff-table__header">メールアドレス</th>
                    <th class="staff-table__header">月次勤怠</th>
                </x-slot>
                @foreach ($rows as $row)
                    <tr class="table__row">
                        <td class="staff-table__data">{{ $row->name() }}</td>
                        <td class="staff-table__data">{{ $row->email() }}</td>
                        <td class="staff-table__data">
                            <a class="table__link" href="{{ $row->monthlyAttendanceUrl() }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </x-ui.list-table>
        </div>
    </div>
@endsection