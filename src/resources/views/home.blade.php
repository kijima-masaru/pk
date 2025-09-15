@extends('layouts.app')

@section('title', 'ホーム - ポケモン対戦サポート')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-home me-2 text-primary"></i>ホーム
            </h2>
            <span class="text-muted">{{ now()->format('Y年m月d日') }}</span>
        </div>
    </div>
</div>

<div class="row">
    <!-- ウェルカムカード -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="card-title text-primary mb-2">
                            <i class="fas fa-star me-2"></i>ようこそ、{{ Auth::user()->name }}さん！
                        </h3>
                        <p class="card-text text-muted mb-3">
                            ポケモン対戦サポートシステムへようこそ。ここでは、あなたのポケモンとパーティを管理し、
                            対戦でのダメージ計算をサポートします。
                        </p>
                        <div class="d-flex gap-2">
                            <span class="badge bg-primary">ポケモン管理</span>
                            <span class="badge bg-success">パーティ作成</span>
                            <span class="badge bg-info">ダメージ計算</span>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                            <i class="fas fa-dragon text-primary" style="font-size: 48px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- クイックアクション -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>クイックアクション
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('pokemon.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>ポケモンを登録
                    </a>
                    <a href="{{ route('pokemon.index') }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-list me-2"></i>ポケモン一覧
                    </a>
                    <button class="btn btn-outline-success btn-lg" disabled>
                        <i class="fas fa-users me-2"></i>パーティを作成
                    </button>
                    <button class="btn btn-outline-info btn-lg" disabled>
                        <i class="fas fa-calculator me-2"></i>ダメージ計算
                    </button>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        パーティ作成とダメージ計算は今後実装予定です
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- 統計情報 -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>統計情報
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h3 class="text-primary mb-1">{{ $pokemonCount }}</h3>
                            <small class="text-muted">登録ポケモン</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-success mb-1">{{ $partyCount }}</h3>
                        <small class="text-muted">作成パーティ</small>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        最終ログイン: {{ Auth::user()->updated_at->format('m/d H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- お知らせ -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bullhorn me-2"></i>お知らせ
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>ポケモン登録機能が利用可能です！</strong><br>
                    ポケモンの詳細な情報（努力値、性格、特性、技など）を登録・管理できます。
                    パーティ管理とダメージ計算機能は今後追加予定です。
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
