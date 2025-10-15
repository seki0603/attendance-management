<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/verify-email-page.css') }}">
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <img class="header__logo" src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </div>
    </header>

    <main>
        <div class="content">
            <p class="content__text">メール内のリンクをクリックしてください。<br>
                認証が完了すると自動的に打刻画面へ遷移します。</p>

            <p class="content__info">メールが届かない場合</p>
            <form action="{{ route('verification.send') }}" method="POST">
                @csrf
                <button class="content__send" type="submit">認証メールを再送する</button>
            </form>
        </div>
    </main>

</body>

</html>