@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-list.css') }}">
@endsection

@section('content')
    <div class="list__wrapper">
        <div class="list">
            <h1 class="list__title">{{ $displayDate }}の勤怠</h1>

            {{-- 日ページャー --}}
            <div class="list__day-wrapper">
                <a class="list__last-day" href="{{ $previousDayUrl }}">
                    <img class="last-day__image" src="{{ asset('images/arrow.png') }}" alt="arrow">前日
                </a>
                <div class="list__day">
                    <img class="list__day-image" src="{{ asset('images/calendar.png') }}" alt="calendar">
                    <p class="list__day-text">{{ $displayDateSlash }}</p>
                </div>
                <a class="list__next-day" href="{{ $nextDayUrl }}">
                    翌日<img class="next-day__image" src="{{ asset('images/arrow.png') }}" alt="arrow">
                </a>
            </div>

            <table class="list-table">
                <tr class="list-table__row">
                    <th class="list-table__item-name">名前</th>
                    <th class="list-table__item">出勤</th>
                    <th class="list-table__item">退勤</th>
                    <th class="list-table__item">休憩</th>
                    <th class="list-table__item">合計</th>
                    <th class="list-table__item-detail">詳細</th>
                </tr>

                @foreach ($records as $record)
                <tr class="list-table__row">
                    <td class="list-table__item-name">{{ $record['user_name'] }}</td>
                    <td class="list-table__item">{{ $record['clock_in'] }}</td>
                    <td class="list-table__item">{{ $record['clock_out'] }}</td>
                    <td class="list-table__item">{{ $record['total_break_time'] }}</td>
                    <td class="list-table__item">{{ $record['total_work_time'] }}</td>
                    <td class="list-table__item-detail">
                        @if ($record['detail_url'])
                        <a class="list-table__link" href="{{ $record['detail_url'] }}">詳細</a>
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