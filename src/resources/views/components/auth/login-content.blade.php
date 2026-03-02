<form class="form" action="{{ $action }}" method="post">
    @csrf
    <div class="form__group">
        <div class="form__group-title">
            <span class="form__label--item">メールアドレス</span>
        </div>
        <div class="form__group-content">
            <input class="form__input" type="email" name="email" value="{{ old('email') }}">
            <div class="form__error">
                @error('email')
                    {{ $message }}
                @enderror
            </div>
        </div>
    </div>
    <div class="form__group">
        <div class="form__group-title">
            <span class="form__label--item">パスワード</span>
        </div>
        <div class="form__group-content">
            <input class="form__input" type="password" name="password">
            <div class="form__error">
                @error('password')
                    {{ $message }}
                @enderror
            </div>
        </div>
    </div>
    <div class="form__button">
        <button class="form__button-submit" type="submit">
            {{ $buttonText }}
        </button>
    </div>
</form>