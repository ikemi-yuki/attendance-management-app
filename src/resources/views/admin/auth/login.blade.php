@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
@endsection

@section('content')
    <div class="login-form__content">
        <div class="login-form__header">
            <h2 class="login-form__header-title">管理者ログイン</h2>
        </div>
        <x-auth.login-content
            :action="route('admin.login')"
            buttonText="管理者ログインする"
        />
    </div>
@endsection