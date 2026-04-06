@props([
    'previousUrl',
    'previousText',
    'label',
    'nextUrl',
    'nextText',
])

<div class="attendance-list__table-header">
    <a class="attendance-list__date-link" href="{{ $previousUrl }}">← {{ $previousText }}</a>
    <span class="attendance-list__date">
        <img class="calendar-icon" src="{{ asset('images/logos/calendar.png') }}" alt="カレンダー">
        {{ $label }}
    </span>
    <a class="attendance-list__date-link" href="{{ $nextUrl }}">{{ $nextText }} →</a>
</div>