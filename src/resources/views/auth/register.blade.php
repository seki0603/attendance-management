<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a href="">
                <img class="header__logo" src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
            </a>
        </div>
    </header>

    <main>
        <div class="content">
            <h1 class="content__title">会員登録</h1>
            <form class="form" action="{{ route('register.store') }}" method="POST" novalidate>
                @csrf
                <div class="form__item">
                    <label class="form__item-label">名前</label>
                    <input class="form__item-input" type="text" name="name" value="{{ old('name') }}">
                    <div class="error">
                        @error('name')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="form__item">
                    <label class="form__item-label">メールアドレス</label>
                    <input class="form__item-input" type="email" name="email" value="{{ old('email') }}">
                    <div class="error">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="form__item">
                    <label class="form__item-label">パスワード</label>
                    <input class="form__item-input" type="password" name="password" value="{{ old('password') }}">
                    <div class="error">
                        @error('password')
                        @if ($message !== 'パスワードと一致しません')
                        {{ $message }}
                        @endif
                        @enderror
                    </div>
                </div>
                <div class="form__item">
                    <label class="form__item-label">パスワード確認</label>
                    <input class="form__item-input" type="password" name="password_confirmation"
                        value="{{ old('password_confirmation') }}">
                    <div class="error">
                        @error('password')
                        @if ($message === 'パスワードと一致しません')
                        {{ $message }}
                        @endif
                        @enderror
                    </div>
                </div>
                <button class="form__button" type="submit">登録する</button>
            </form>
            <a class="content__link" href="{{ route('login') }}">ログインはこちら</a>
        </div>
    </main>
</body>

</html>