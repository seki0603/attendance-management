<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <img class="header__logo" src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </div>
    </header>

    <main>
        <div class="content">
            <p class="content__text">登録していただいたメールアドレスに認証メールを送付しました。<br>
                メース認証を完了してください。</p>
            <a href="{{ route('verification.site') }}">
                <button class="content__button" type="submit">認証はこちらから</button>
            </a>
            <form action="{{ route('verification.send') }}" method="POST">
                @csrf
                <button class="content__send" type="submit">認証メールを再送する</button>
            </form>
        </div>
    </main>

</body>

</html>