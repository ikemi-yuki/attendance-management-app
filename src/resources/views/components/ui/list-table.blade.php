<div class="table">
    <table class="table__inner">
        <tr class="table__row">
            @foreach ($headers as $header)
                <th class="table__header">{{ $header }}</th>
            @endforeach
        </tr>
        @foreach ($rows as $row)
            <tr class="table__row">
                @foreach ($row['cells'] as $cell)
                    <td class="table__data">{{ $cell }}</td>
                @endforeach
                <td class="table__data">
                    <a class="table__link"{{ !$row['link'] ? 'table__link--disabled' : '' }}" href="{{ $row['link'] ?? '#' }}">
                    詳細
                    </a>
                </td>
            </tr>
        @endforeach
    </table>
</div>