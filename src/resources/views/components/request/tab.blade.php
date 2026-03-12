@props([
    'status' => 'pending'
])

<div class="tabs">
    <a href="{{ route('request.list',['status' => 'pending']) }}" class="status__tab{{ $status === 'pending' ? '--active' : '' }}">
        承認待ち
    </a>
    <a href="{{ route('request.list',['status' => 'approved']) }}" class="status__tab{{ $status === 'approved' ? '--active' : '' }}">
        承認済み
    </a>
</div>