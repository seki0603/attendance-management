@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff-list.css') }}">
@endsection

@section('content')
    <div class="list__wrapper">
        <div class="list">
            <h1 class="list__title">スタッフ一覧</h1>

            <table class="list-table">
                <tr class="list-table__row">
                    <th class="list-table__item"></th>
                    <th class="list-table__item">名前</th>
                    <th class="list-table__item-email">メールアドレス</th>
                    <th class="list-table__item">月次勤怠</th>
                    <th class="list-table__item"></th>
                </tr>

                @foreach ($records as $record)
                <tr class="list-table__row">
                    <td class="list-table__item"></td>
                    <td class="list-table__item">{{ $record['name'] }}</td>
                    <td class="list-table__item-email">{{ $record['email'] }}</td>
                    <td class="list-table__item">
                        <a class="list-table__link" href="{{ $record['monthly_url'] }}">詳細</a>
                    </td>
                    <td class="list-table__item"></td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection