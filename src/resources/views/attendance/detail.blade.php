@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail__wrapper">
    <div class="detail">
        <h1 class="detail__title">勤怠詳細</h1>

        <form class="form" action="">
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
                        <input class="detail-table__input" type="text" value="{{ $data['clock_in'] }}">
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="text" value="{{ $data['clock_out'] }}">
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                @foreach ($data['breaks'] as $index => $break)
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="text" value="{{ $break['break_start'] }}">
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="text" value="{{ $break['break_end'] }}">
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                @endforeach
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩{{ count($data['breaks']) + 1 }}</th>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="text" value="">
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="text" value="">
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">備考</th>
                    <td class="detail-table__item" colspan="3">
                        <textarea class="detail-table__textarea"></textarea>
                    </td>
                </tr>
            </table>
            <div class="form__button">
                <button class="form__button-submit">修正</button>
            </div>
        </form>
    </div>
</div>
@endsection