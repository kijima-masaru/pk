@extends('layouts.auth')

@section('title', 'ログイン - ポケモン対戦サポート')

@section('content')
<div class="text-center mb-4">
    <h4>ログイン</h4>
    <p class="text-muted">アカウントにログインしてください</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf
    
    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="fas fa-envelope me-2"></i>メールアドレス
        </label>
        <input type="email" 
               class="form-control @error('email') is-invalid @enderror" 
               id="email" 
               name="email" 
               value="{{ old('email') }}" 
               required 
               autocomplete="email" 
               autofocus
               placeholder="example@pokemon.com">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="fas fa-lock me-2"></i>パスワード
        </label>
        <input type="password" 
               class="form-control @error('password') is-invalid @enderror" 
               id="password" 
               name="password" 
               required 
               autocomplete="current-password"
               placeholder="パスワードを入力">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label" for="remember">
            ログイン状態を保持する
        </label>
    </div>

    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-sign-in-alt me-2"></i>ログイン
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('password.request') }}" class="text-decoration-none">
            パスワードを忘れた方はこちら
        </a>
    </div>

    <hr class="my-4">

    <div class="text-center">
        <p class="mb-0">アカウントをお持ちでない方は</p>
        <a href="{{ route('register') }}" class="btn btn-outline-primary">
            <i class="fas fa-user-plus me-2"></i>新規登録
        </a>
    </div>
</form>
@endsection
