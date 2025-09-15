@extends('layouts.auth')

@section('title', '新規登録 - ポケモン対戦サポート')

@section('content')
<div class="text-center mb-4">
    <h4>新規アカウント作成</h4>
    <p class="text-muted">アカウントを作成してポケモン対戦を始めよう！</p>
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

<form method="POST" action="{{ route('register') }}">
    @csrf
    
    <div class="mb-3">
        <label for="name" class="form-label">
            <i class="fas fa-user me-2"></i>ユーザー名
        </label>
        <input type="text" 
               class="form-control @error('name') is-invalid @enderror" 
               id="name" 
               name="name" 
               value="{{ old('name') }}" 
               required 
               autocomplete="name" 
               autofocus
               placeholder="トレーナー名を入力">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

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
               autocomplete="new-password"
               placeholder="8文字以上のパスワード">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                8文字以上のパスワードを設定してください
            </small>
        </div>
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">
            <i class="fas fa-lock me-2"></i>パスワード確認
        </label>
        <input type="password" 
               class="form-control" 
               id="password_confirmation" 
               name="password_confirmation" 
               required 
               autocomplete="new-password"
               placeholder="パスワードを再入力">
    </div>

    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-success btn-lg">
            <i class="fas fa-user-plus me-2"></i>アカウント作成
        </button>
    </div>

    <hr class="my-4">

    <div class="text-center">
        <p class="mb-0">既にアカウントをお持ちの方は</p>
        <a href="{{ route('login') }}" class="btn btn-outline-primary">
            <i class="fas fa-sign-in-alt me-2"></i>ログイン
        </a>
    </div>
</form>
@endsection
