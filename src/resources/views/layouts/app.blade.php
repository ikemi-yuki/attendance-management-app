<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')

    @stack('styles')
</head>
<body>
    <header class="header">
        <div class="header__container">
            <h1 class=header__logo>
                <img class="header__logo-img" src="{{ asset('images/logos/COACHTECH-header-logo.png') }}" alt="COACHTECH">
            </h1>
            @yield('nav')
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>