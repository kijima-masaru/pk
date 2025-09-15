@extends('layouts.app')

@section('title', 'ダッシュボード - ポケモン対戦サポート')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-tachometer-alt me-2 text-primary"></i>ダッシュボード
            </h2>
            <span class="text-muted">{{ now()->format('Y年m月d日') }}</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>ダッシュボード</strong><br>
            現在、ホーム画面と同じ内容を表示しています。今後、ダッシュボード専用の機能を追加予定です。
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 text-center">
        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-home me-2"></i>ホーム画面へ移動
        </a>
    </div>
</div>
@endsection
