@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__wrapper">
    <div class="list">
        <h1 class="list__title">勤怠一覧</h1>

        {{-- 月ページャー --}}
        <div class="list__month-wrapper">
            <a class="list__last-month"
                href="{{ route('attendance.list', ['month' => $current->copy()->subMonth()->format('Y-m')]) }}">
                <img class="last-month__image" src="{{ asset('images/arrow.png') }}" alt="arrow">前月
            </a>
            <div class="list__month">
                <img class="list__month-image" src="{{ asset('images/calendar.png') }}" alt="calendar">
                <p class="list__month-text">{{ $current->format('Y/m') }}</p>
            </div>
            <a class="list__next-month"
                href="{{ route('attendance.list', ['month' => $current->copy()->addMonth()->format('Y-m')]) }}">
                翌月<img class="next-month__image" src="{{ asset('images/arrow.png') }}" alt="arrow">
            </a>
        </div>


        <table class="list__table">
            <tr class="list__table-row">
                <th class="list__table-item--date">日付</th>
                <th class="list__table-item">出勤</th>
                <th class="list__table-item">退勤</th>
                <th class="list__table-item">休憩</th>
                <th class="list__table-item">合計</th>
                <th class="list__table-item--detail">詳細</th>
            </tr>

            @foreach ($records as $record)
            <tr class="list__table-row">
                <td class="list__table-item--date">{{ $record['date_str'] }} ({{ $record['weekday'] }})</td>
                <td class="list__table-item">{{ $record['clock_in'] }}</td>
                <td class="list__table-item">{{ $record['clock_out'] }}</td>
                <td class="list__table-item">{{ $record['total_break_time'] }}</td>
                <td class="list__table-item">{{ $record['total_work_time'] }}</td>
                <td class="list__table-item--detail">
                    @if ($record['detail_url'])
                    <a class="list__table-link" href="{{ $record['detail_url'] }}">詳細</a>
                    @else
                    詳細
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection