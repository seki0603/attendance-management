@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<form class="form" action="{{ route('attendance.store') }}" method="POST" novalidate>
    @csrf
    <p class="form__status">{{ $viewData['status'] }}</p>
    <h2 class="form__date">
        {{ $viewData['date'] }}{{ $viewData['weekday']}}
    </h2>
    <h1 class="form__time">{{ $viewData['time'] }}</h1>

    @if ($viewData['status'] === '勤務外')
    <button class="form__attendance-button" type="submit" name="clock_in" value="{{ now()->format('H:i') }}">出勤</button>
    @elseif ($viewData['status'] === '出勤中')
    <button class="form__attendance-button" type="submit" name="clock_out"
        value="{{ now()->format('H:i') }}">退勤</button>
    <button class="form__break-button" type="submit" name="break_start" value="{{ now()->format('H:i') }}">休憩入</button>
    @elseif ($viewData['status'] === '休憩中')
    <button class="form__break-button" type="submit" name="break_end" value="{{ now()->format('H:i') }}">休憩戻</button>
    @elseif ($viewData['status'] === '退勤済')
    <p class="form__message">お疲れ様でした。</p>
    @endif
</form>
@endsection