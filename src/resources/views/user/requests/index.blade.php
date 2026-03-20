@extends('layouts.user')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/components/page-title.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/tab.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/list-table.css') }}">
@endpush

@section('page')
    <div class="page-inner">
        <x-ui.page-title>
            申請一覧
        </x-ui.page-title>
        <x-request.tab :status="$status ?? 'pending'" />
        <div class="table">
            <x-ui.list-table>
                <x-slot name="header">
                    <th class="request-table__header">状態</th>
                    <th class="request-table__header">名前</th>
                    <th class="request-table__header">対象日時</th>
                    <th class="request-table__header">申請理由</th>
                    <th class="request-table__header">申請日時</th>
                    <th class="request-table__header">詳細</th>
                </x-slot>
                @foreach ($rows as $row)
                    <tr class="table__row">
                        <td class="request-table__data">{{ $row->status() }}</td>
                        <td class="request-table__data">{{ $row->name() }}</td>
                        <td class="request-table__data">{{ $row->targetDate() }}</td>
                        <td class="request-table__data">{{ $row->reason() }}</td>
                        <td class="request-table__data">{{ $row->requestedAt() }}</td>
                        <td class="request-table__data">
                            <a class="table__link" href="{{ $row->detailUrl() }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </x-ui.list-table>
        </div>
    </div>
@endsection