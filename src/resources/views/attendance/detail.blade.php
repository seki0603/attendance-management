@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail__wrapper">
    <div class="detail">
        <h1 class="detail__title">勤怠詳細</h1>
        @if (session('message'))
        <p class="success">{{ session('message') }}</p>
        @endif
        @if ($errors->any())
            <ul class="error-list">
                @foreach ($errors->all() as $error)
                <li class="error">{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form class="form" action="{{ route('correction.store', $attendance->id) }}" method="POST" novalidate>
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
                        @if ($viewData['is_pending'])
                        {{ old('clock_in', $viewData['clock_in']) }}
                        @else
                        <input class="detail-table__input" type="time" name="clock_in"
                            value="{{ old('clock_in', $viewData['clock_in']) }}">
                        @endif
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        @if ($viewData['is_pending'])
                        {{ old('clock_out', $viewData['clock_out'] )}}
                        @else
                        <input class="detail-table__input" type="time" name="clock_out"
                            value="{{ old('clock_out', $viewData['clock_out'] )}}">
                        @endif
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                @foreach ($viewData['breaks'] as $index => $break)
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                    <td class="detail-table__item">
                        @if ($viewData['is_pending'])
                        {{ $break['break_start'] }}
                        @else
                        <input class="detail-table__input" type="time" name="break_start_{{ $index + 1 }}"
                            value="{{ $break['break_start'] }}">
                        @endif
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        @if ($viewData['is_pending'])
                        {{ $break['break_end'] }}
                        @else
                        <input class="detail-table__input" type="time" name="break_end_{{ $index + 1 }}"
                            value="{{ $break['break_end'] }}">
                        @endif
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                @endforeach

                @if (!$viewData['is_pending'])
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩{{ $viewData['next_index'] }}</th>
                    <td class="detail-table__item">
                        @if ($viewData['is_pending'])
                        {{ $viewData['next_break']['break_start'] }}
                        @else
                        <input class="detail-table__input" type="time" name="break_start_{{ $viewData['next_index'] }}"
                            value="{{ $viewData['next_break']['break_start'] }}">
                        @endif
                    </td>
                    <td class="detail-table__tilde">～</td>
                    <td class="detail-table__item">
                        @if ($viewData['is_pending'])
                        {{ $viewData['next_break']['break_end'] }}
                        @else
                        <input class="detail-table__input" type="time" name="break_end_{{ $viewData['next_index'] }}"
                            value="{{ $viewData['next_break']['break_end'] }}">
                        @endif
                    </td>
                    <td class="detail-table__item"></td>
                </tr>
                @endif
                <tr class="detail-table__row">
                    <th class="detail-table__header">備考</th>
                    @if ($viewData['is_pending'])
                    <td class="detail-table__item">
                        {{ $viewData['note'] }}
                    </td>
                    @else
                    <td class="detail-table__item" colspan="3">
                        <textarea class="detail-table__textarea" name="note">{{ $viewData['note'] }}</textarea>
                        @endif
                    </td>
                </tr>
            </table>

            @if ($viewData['is_pending'])
            <p class="status__message">*承認待ちのため修正はできません。</p>
            @else
            <div class="form__button">
                <button class="form__button-submit" type="submit">修正</button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener("DOMContentLoaded", () => {
    const timeInputs = document.querySelectorAll('.detail-table__input[type="time"]');

    timeInputs.forEach(input => {
        const hideIfEmpty = () => {
            if (!input.value) {
                input.style.color = "transparent";
            } else {
                input.style.color = "#000";
            }
        };

        hideIfEmpty();
        input.addEventListener("focus", () => {
            input.style.color = "#000";
        });

        input.addEventListener("input", () => {
            input.style.color = "#000";
        });

        input.addEventListener("blur", hideIfEmpty);
    });
});
</script>
@endsection