@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('nav')
    <div class="header-nav">
        <nav class="nav">
            <ul class="nav__list">
                <li class="nav__item">
                    <a class="nav__link" href="">勤怠一覧</a>
                </li>
                <li class="nav__item">
                    <a class="nav__link" href="">スタッフ一覧</a>
                </li>
                <li class="nav__item">
                    <a class="nav__link" href="">申請一覧</a>
                </li>
                <li class="nav__item">
                    <form class="nav__item-form" action="{{ route('admin.logout') }}" method="post">
                        @csrf
                        <input class="nav__link--logout" type="submit" value="ログアウト">
                    </form>
                </li>
            </ul>
        </nav>
    </div>
@endsection

@section('content')
    @yield('page')

    @stack('scripts')
@endsection