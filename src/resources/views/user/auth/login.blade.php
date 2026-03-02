@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
@endsection

@section('content')
    <div class="login-form__content">
        <div class="login-form__header">
            <h2 class="login-form__header-title">ログイン</h2>
        </div>
        <x-auth.login-content
            :action="route('login')"
            buttonText="ログインする"
        />
        <div class="register__link">
            <a class="register__button-submit" href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    </div>
@endsection
