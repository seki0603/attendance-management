@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/correction-list.css') }}">
@endsection

@section('content')
<div class="list__wrapper">
    <div class="list">
        <h1 class="list__title">申請一覧</h1>

        <div class="tab">
            <a href=""
                class="tab__link {{ request('tab') === 'waiting' ? 'tab__link--active' : '' }}">
                承認待ち
            </a>
            <a href=""
                class="tab__link {{ request('tab') === 'completed' ? 'tab__link--active' : '' }}">
                承認済み
            </a>
        </div>

        <table class="list-table">
            <tr class="list-table__row">
                <th class="list-table__item-status">状態</th>
                <th class="list-table__item">名前</th>
                <th class="list-table__item">対象日時</th>
                <th class="list-table__item">申請理由</th>
                <th class="list-table__item">申請日時</th>
                <th class="list-table__item">詳細</th>
            </tr>

            <tr class="list-table__row">
                <td class="list-table__item-status"></td>
                <td class="list-table__item"></td>
                <td class="list-table__item"></td>
                <td class="list-table__item"></td>
                <td class="list-table__item"></td>
                <td class="list-table__item">
                    <a class="list-table__link" href="">詳細</a>
                </td>
            </tr>
        </table>
        </div>
        </div>
@endsection