@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail__wrapper">
    <div class="detail">
        <h1 class="detail__title">勤怠詳細</h1>
        @if (session('message'))
            <p class="success">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
        <div class="error">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form class="form" action="{{ route('correction.store', $attendance->id) }}" method="POST" novalidate>
            @csrf
            <table class="detail-table">
                <tr class="detail-table__row">
                    <th class="detail-table__header">名前</th>
                    <td class="detail-table__item">{{ $data['name'] }}</td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">日付</th>
                    <td class="detail-table__item">{{ $data['year'] }}</td>
                    <td class="detail-table__tilde"></td>
                    <td class="detail-table__item">{{ $data['month_day'] }}</td>
                    <td class="detail-table__item"></td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">出勤・退勤</th>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="time" name="clock_in" value="{{ old('clock_in', $data['clock_in']) }}" {{
                            $data['readonly'] ? 'readonly' : '' }}>
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="time" name="clock_out" value="{{ old('clock_out', $data['clock_out'] )}}" {{
                            $data['readonly'] ? 'readonly' : '' }}>
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                @foreach ($data['breaks'] as $index => $break)
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="time" name="break_start" value="{{ old('break_start', $break['break_start']) }}" {{
                            $data['readonly'] ? 'readonly' : '' }}>
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="time" name="break_end" value="{{ old('break_end', $break['break_end']) }}" {{
                            $data['readonly'] ? 'readonly' : '' }}>
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                @endforeach
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩{{ count($data['breaks']) + 1 }}</th>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="time" name="break_start" value="">
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="time" name="break_end" value="">
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">備考</th>
                    <td class="detail-table__item" colspan="3">
                        <textarea class="detail-table__textarea" name="note" {{ $data['readonly'] ? 'readonly' : '' }}>{{ old('note') }}</textarea>
                    </td>
                </tr>
            </table>

            @if ($data['readonly'])
            <p class="detail__message">*承認待ちのため修正はできません。</p>
            @else
            <div class="form__button">
                <button class="form__button-submit" type="submit">修正</button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection