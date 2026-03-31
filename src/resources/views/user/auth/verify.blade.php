@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pages/verify.css') }}">
@endsection

@section('content')
    <div class="verify">
        <p class="verify__text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>
        <a class="verify__button" href="{{ $verificationUrl }}">認証はこちらから</a>
        <form class="verify__form" method="post" action="{{ route('verification.send') }}">
            @csrf
            <button class="verify__form-link" type="submit">認証メールを再送する</button>
        </form>
    </div>
@endsection