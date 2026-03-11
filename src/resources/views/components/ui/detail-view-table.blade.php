<div class="table">
    <table class="table__inner">
        <tr class="table__row">
            <th class="table__header">名前</th>
            <td class="table__data--name">
                {{ $attendance->user->name }}
            </td>
        </tr>
        <tr class="table__row">
            <th class="table__header">日付</th>
            <td class="table__data--date">
                <p class="table__data-item">{{ $attendance->work_date->isoFormat('YYYY年') }}</p>
                <p class="table__data-item">{{ $attendance->work_date->isoFormat('M月D日') }}</p>
            </td>
        </tr>
        <tr class="table__row">
            <th class="table__header">出勤・退勤</th>
            <td class="table__data">
                <div class="table__data--time">
                    <p class="table__data-item">{{ optional($attendanceRequest->requested_clock_in)->format('H:i') }}</p>
                    <span class="table__data-symbol">～</span>
                    <p class="table__data-item">{{ optional($attendanceRequest->requested_clock_out)->format('H:i') }}</p>
                </div>
            </td>
        </tr>
        @foreach($requestBreaks as $requestBreak)
            <tr class="table__row">
                <th class="table__header">{{ $loop->first ? '休憩' : '休憩'.$loop->iteration }}</th>
                <td class="table__data">
                    <div class="table__data--time">
                        <p class="table__data-item">{{ optional($requestBreak->requested_break_start)->format('H:i') }}</p>
                        <span class="table__data-symbol">～</span>
                        <p class="table__data-item">{{ optional($requestBreak->requested_break_end)->format('H:i') }}</p>
                    </div>
                </td>
            </tr>
        @endforeach
        <tr class="table__row">
            <th class="table__header">備考</th>
            <td class="table__data--text">
                <p class="table__data-item">{{ $attendanceRequest->requested_note }}</p>
            </td>
        </tr>
    </table>
</div>