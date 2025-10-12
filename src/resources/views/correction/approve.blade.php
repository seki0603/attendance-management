@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/approve.css') }}">
@endsection

@section('content')
<div class="detail__wrapper">
    <div class="detail">
        <h1 class="detail__title">勤怠詳細</h1>
        @if (session('message'))
        <p class="success">{{ session('message') }}</p>
        @endif

        {{-- <form class="form" action="{{ route('admin.correction.approve.update', $request->id) }}" method="POST" novalidate> --}}
            @method('put')
            @csrf
            <table class="detail-table">
                <tr class="detail-table__row">
                    <th class="detail-table__header">名前</th>
                    <td class="detail-table__item">{{ $viewData['name'] }}</td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">日付</th>
                    <td class="detail-table__item">{{ $viewData['year'] }}</td>
                    <td class="detail-table__tilde"></td>
                    <td class="detail-table__item">{{ $viewData['month_day'] }}</td>
                    <td class="detail-table__item"></td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">出勤・退勤</th>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="time" name="clock_in"
                            value="{{ old('clock_in', $viewData['clock_in']) }}" readonly>
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="time" name="clock_out"
                            value="{{ old('clock_out', $viewData['clock_out'] )}}" readonly>
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                @foreach ($viewData['breaks'] as $index => $break)
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="time" name="break_start_{{ $index + 1 }}"
                            value="{{ $break['break_start'] }}" readonly>
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        <input class="detail-table__input" type="time" name="break_end_{{ $index + 1 }}"
                            value="{{ $break['break_end'] }}" readonly>
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                @endforeach

                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩{{ $viewData['next_index'] }}</th>

                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">備考</th>
                    <td class="detail-table__item" colspan="3">
                        <input class="detail-table__textarea" name="note" value="{{ old('note', $viewData['note']) }}" readonly>
                    </td>
                </tr>
            </table>

            <div class="form__button">
                <button class="form__button-submit" type="submit">承認</button>
            </div>
        {{-- </form> --}}
    </div>
</div>
@endsection