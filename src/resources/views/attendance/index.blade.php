@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<form class="form" action="{{ route('attendance.store') }}" method="POST" novalidate>
    @csrf
    <p class="form__status">{{ $status }}</p>
    <h2 class="form__date">
        {{ $now->format('Y年m月d日') }}
        {{ $attendance ? $attendance->jp_weekday : '(' . ['日','月','火','水','木','金','土'][$now->dayOfWeek] . ')' }}
    </h2>
    <h1 class="form__time">{{ $now->format('H:i') }}</h1>

    @if ($status === '勤務外')
    <button class="form__attendance-button" type="submit" name="clock_in" value="{{ now()->format('H:i') }}">出勤</button>
    @elseif ($status === '出勤中')
    <button class="form__attendance-button" type="submit" name="clock_out"
        value="{{ now()->format('H:i') }}">退勤</button>
    <button class="form__break-button" type="submit" name="break_start" value="{{ now()->format('H:i') }}">休憩入</button>
    @elseif ($status === '休憩中')
    <button class="form__break-button" type="submit" name="break_end" value="{{ now()->format('H:i') }}">休憩戻</button>
    @elseif ($status === '退勤済')
    <p class="form__message">お疲れ様でした。</p>
    @endif
</form>
@endsection