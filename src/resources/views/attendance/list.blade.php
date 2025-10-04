@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
@endsection

@section('content')
<div class="list__wrapper">
    <h1 class="list__title">勤怠一覧</h1>

    {{-- 月ページャー --}}
    <div class="list__month-nav">
        <a class="list__last-month"
            href="{{ route('attendance.list', ['month' => $current->copy()->subMonth()->format('Y-m')]) }}">
            <span>←</span>前月
        </a>
        <p class="list__month">{{ $current->format('Y年m月') }}</p>
        <a class="list__next-month"
            href="{{ route('attendance.list', ['month' => $current->copy()->addMonth()->format('Y-m')]) }}">
            翌月<span>→</span>
        </a>
    </div>
</div>

<table class="list__table">
    <tr class="list__table-row">
        <th class="list__table-header">日付</th>
        <th class="list__table-header">出勤</th>
        <th class="list__table-header">退勤</th>
        <th class="list__table-header">休憩</th>
        <th class="list__table-header">合計</th>
        <th class="list__table-header">詳細</th>
    </tr>

    @foreach ($records as $record)
    <tr class="list__table-row">
        <td class="list__table-item">
            {{ $record['date_str'] }}({{ $record['weekday'] }})
        </td>
        <td class="list__table-item">{{ $record['clock_in'] }}</td>
        <td class="list__table-item">{{ $record['clock_out'] }}</td>
        <td class="list__table-item">{{ $record['total_break_time'] }}</td>
        <td class="list__table-item">{{ $record['total_work_time'] }}</td>
        <td class="list__table-item">
            @if ($record['detail_url'])
            <a href="{{ $record['detail_url'] }}">詳細</a>
            @else
            <p>詳細</p>
            @endif
        </td>
    </tr>
    @endforeach
</table>
</div>
@endsection