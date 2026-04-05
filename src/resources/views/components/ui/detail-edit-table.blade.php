<form class="form" action="{{ $action }}" method="post">
    {{ $method }}
    @csrf
    <div class="table">
        <table class="table__inner">
            <tr class="table__row">
                <th class="table__header">名前</th>
                <td class="table__data--name">{{ $attendance->user->name }}</td>
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
                    <div class="table__data-content">
                        <input class="table__data-input" type="time" name="clock_in" value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                        <span class="table__data-symbol">～</span>
                        <input class="table__data-input" type="time" name="clock_out" value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
                    </div>
                    <div class="error">
                        @error('clock_in')
                            {{ $message }}
                        @enderror
                    </div>
                    <div class="error">
                        @error('clock_out')
                            {{ $message }}
                        @enderror
                    </div>
                </td>
            </tr>
            @foreach($breaks as $break)
                <tr class="table__row">
                    <th class="table__header">{{ $loop->first ? '休憩' : '休憩'.$loop->iteration }}</th>
                    <td class="table__data">
                        <div class="table__data-content">
                            <input class="table__data-input" type="time" name="breaks[{{ $break->id }}][break_start]" value="{{ old("breaks.$break->id.break_start", optional($break->break_start)->format('H:i')) }}">
                            <span class="table__data-symbol">～</span>
                            <input class="table__data-input" type="time" name="breaks[{{ $break->id }}][break_end]" value="{{ old("breaks.$break->id.break_end", optional($break->break_end)->format('H:i')) }}">
                        </div>
                        <div class="error">
                            @error("breaks.$break->id.break_start")
                                {{ $message }}
                            @enderror
                        </div>
                        <div class="error">
                            @error("breaks.$break->id.break_end")
                                {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="table__row">
                <th class="table__header">{{ $breaks->count() === 0 ? '休憩' : '休憩' . ($breaks->count() + 1) }}</th>
                <td class="table__data">
                    <div class="table__data-content">
                        <input class="table__data-input" type="time" name="breaks[new][break_start]" value="{{ old('breaks.new.break_start') }}">
                        <span class="table__data-symbol">～</span>
                        <input class="table__data-input" type="time" name="breaks[new][break_end]" value="{{ old('breaks.new.break_end') }}">
                    </div>
                    <div class="error">
                        @error("breaks.new.break_start")
                            {{ $message }}
                        @enderror
                    </div>
                    <div class="error">
                        @error("breaks.new.break_end")
                            {{ $message }}
                        @enderror
                    </div>
                </td>
            </tr>
            <tr class="table__row">
                <th class="table__header">備考</th>
                <td class="table__data">
                    <textarea class="table__data-textarea" name="note">{{ old('note', $attendance->note) }}</textarea>
                    <div class="error">
                        @error('note')
                            {{ $message }}
                        @enderror
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="form__button">
        <button class="form__button-submit" type="submit">修正</button>
    </div>
</form>