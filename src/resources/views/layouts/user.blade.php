<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                <a href="{{ route('attendance.index') }}">
                    <img class="header__logo-img" src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
                </a>
            </div>

            <div class="header__link-wrapper">
                <a class="header__link" href="{{ route('attendance.index') }}">勤怠</a>
                <a class="header__link" href="{{ route('attendance.list') }}">勤怠一覧</a>
                <a class="header__link" href="{{ route('correction.list')}}">申請</a>
                <form class="header__button" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="header__button-logout" type="submit">ログアウト</button>
                </form>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

@yield('script')
</body>
</html>