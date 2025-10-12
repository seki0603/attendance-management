@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/correction-list.css') }}">
@endsection

@section('content')
<div class="list__wrapper">
    <div class="list">
        <h1 class="list__title">申請一覧</h1>

        <div class="tab">
            <a href="{{ route('admin.correction.list', ['tab' => 'waiting']) }}"
                class="tab__link {{ $tab === 'waiting' ? 'tab__link--active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('admin.correction.list', ['tab' => 'completed'])}}"
                class="tab__link {{ $tab === 'completed' ? 'tab__link--active' : '' }}">
                承認済み
            </a>
        </div>

        <table class="list-table">
            <tr class="list-table__row">
                <th class="list-table__item-status">状態</th>
                <th class="list-table__item-name">名前</th>
                <th class="list-table__item">対象日時</th>
                <th class="list-table__item">申請理由</th>
                <th class="list-table__item">申請日時</th>
                <th class="list-table__item-detail">詳細</th>
            </tr>

            @foreach ($records as $record)
            <tr class="list-table__row">
                <td class="list-table__item-status">{{ $record['status'] }}</td>
                <td class="list-table__item-name">{{ $record['name'] }}</td>
                <td class="list-table__item">{{ $record['work_date'] }}</td>
                <td class="list-table__item">{{ $record['note'] }}</td>
                <td class="list-table__item">{{ $record['created_at'] }}</td>
                <td class="list-table__item-detail">
                    <a class="list-table__link" href="{{ $record['detail_url'] }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection