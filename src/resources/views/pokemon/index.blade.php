@extends('layouts.app')

@section('title', 'ポケモン一覧 - ポケモン対戦サポート')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-list me-2 text-primary"></i>ポケモン一覧
            </h2>
            <a href="{{ route('pokemon.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>新しいポケモンを登録
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($myPokemons->count() > 0)
    <div class="row">
        @foreach($myPokemons as $myPokemon)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-paw me-2"></i>{{ $myPokemon->name }}
                            </h5>
                            <span class="badge bg-light text-dark">Lv.{{ $myPokemon->level }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>ポケモン:</strong><br>
                                <span class="text-primary">{{ $myPokemon->pokemon->name }}</span>
                            </div>
                            <div class="col-6">
                                <strong>性格:</strong><br>
                                <span class="text-success">{{ $myPokemon->personality->name }}</span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>特性:</strong><br>
                                <span class="text-info">{{ $myPokemon->characteristics->name }}</span>
                            </div>
                            <div class="col-6">
                                <strong>持ち物:</strong><br>
                                @if($myPokemon->goods)
                                    <span class="text-warning">{{ $myPokemon->goods->name }}</span>
                                @else
                                    <span class="text-muted">なし</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>努力値:</strong>
                            <div class="row text-center mt-2">
                                <div class="col-2">
                                    <small class="text-muted">HP</small><br>
                                    <span class="badge bg-danger">{{ $myPokemon->H_effort_values }}</span>
                                </div>
                                <div class="col-2">
                                    <small class="text-muted">攻撃</small><br>
                                    <span class="badge bg-warning">{{ $myPokemon->A_effort_values }}</span>
                                </div>
                                <div class="col-2">
                                    <small class="text-muted">防御</small><br>
                                    <span class="badge bg-info">{{ $myPokemon->B_effort_values }}</span>
                                </div>
                                <div class="col-2">
                                    <small class="text-muted">特攻</small><br>
                                    <span class="badge bg-success">{{ $myPokemon->C_effort_values }}</span>
                                </div>
                                <div class="col-2">
                                    <small class="text-muted">特防</small><br>
                                    <span class="badge bg-primary">{{ $myPokemon->D_effort_values }}</span>
                                </div>
                                <div class="col-2">
                                    <small class="text-muted">素早さ</small><br>
                                    <span class="badge bg-secondary">{{ $myPokemon->S_effort_values }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>実数値:</strong>
                            <div class="row text-center mt-2">
                                <div class="col-2">
                                    <small class="text-muted">HP</small><br>
                                    <span class="fw-bold text-danger">{{ $myPokemon->H_real_values }}</span>
                                </div>
                                <div class="col-2">
                                    <small class="text-muted">攻撃</small><br>
                                    <span class="fw-bold text-warning">{{ $myPokemon->A_real_values }}</span>
                                </div>
                                <div class="col-2">
                                    <small class="text-muted">防御</small><br>
                                    <span class="fw-bold text-info">{{ $myPokemon->B_real_values }}</span>
                                </div>
                                <div class="col-2">
                                    <small class="text-muted">特攻</small><br>
                                    <span class="fw-bold text-success">{{ $myPokemon->C_real_values }}</span>
                                </div>
                                <div class="col-2">
                                    <small class="text-muted">特防</small><br>
                                    <span class="fw-bold text-primary">{{ $myPokemon->D_real_values }}</span>
                                </div>
                                <div class="col-2">
                                    <small class="text-muted">素早さ</small><br>
                                    <span class="fw-bold text-secondary">{{ $myPokemon->S_real_values }}</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($myPokemon->move1 || $myPokemon->move2 || $myPokemon->move3 || $myPokemon->move4)
                            <div class="mb-3">
                                <strong>技:</strong>
                                <div class="mt-2">
                                    @if($myPokemon->move1)
                                        <span class="badge bg-dark me-1 mb-1">{{ $myPokemon->move1->name }}</span>
                                    @endif
                                    @if($myPokemon->move2)
                                        <span class="badge bg-dark me-1 mb-1">{{ $myPokemon->move2->name }}</span>
                                    @endif
                                    @if($myPokemon->move3)
                                        <span class="badge bg-dark me-1 mb-1">{{ $myPokemon->move3->name }}</span>
                                    @endif
                                    @if($myPokemon->move4)
                                        <span class="badge bg-dark me-1 mb-1">{{ $myPokemon->move4->name }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                登録日: {{ $myPokemon->created_at->format('Y/m/d H:i') }}
                            </small>
                            <div>
                                <button class="btn btn-sm btn-outline-primary" onclick="editPokemon({{ $myPokemon->id }})" title="編集">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deletePokemon({{ $myPokemon->id }}, '{{ $myPokemon->name }}')" title="削除">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-paw fa-5x text-muted"></i>
        </div>
        <h4 class="text-muted">まだポケモンが登録されていません</h4>
        <p class="text-muted">最初のポケモンを登録してみましょう！</p>
        <a href="{{ route('pokemon.create') }}" class="btn btn-success btn-lg">
            <i class="fas fa-plus me-2"></i>ポケモンを登録
        </a>
    </div>
@endif

<script>
function editPokemon(id) {
    // 編集機能は今後実装予定
    alert('編集機能は今後実装予定です。');
}

function deletePokemon(id, name) {
    if (confirm(`「${name}」を削除しますか？\n\n削除したポケモンは復元できません。`)) {
        // ローディング状態を開始
        setLoadingState(true);
        
        // CSRFトークンを取得
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // 削除リクエストを送信
        fetch(`/pokemon/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => {
            if (response.ok) {
                // 削除成功時はページをリロード
                window.location.reload();
            } else {
                // エラー時はアラートを表示
                alert('削除に失敗しました。もう一度お試しください。');
                setLoadingState(false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('削除中にエラーが発生しました。');
            setLoadingState(false);
        });
    }
}

function setLoadingState(isLoading) {
    // 全てのボタンを無効化/有効化
    const buttons = document.querySelectorAll('button, a');
    buttons.forEach(button => {
        button.disabled = isLoading;
        if (isLoading) {
            button.style.pointerEvents = 'none';
            button.style.opacity = '0.6';
        } else {
            button.style.pointerEvents = 'auto';
            button.style.opacity = '1';
        }
    });
    
    // 削除ボタンにローディング表示を追加
    const deleteButtons = document.querySelectorAll('button[onclick*="deletePokemon"]');
    deleteButtons.forEach(button => {
        if (isLoading) {
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.title = '削除中...';
        } else {
            button.innerHTML = '<i class="fas fa-trash"></i>';
            button.title = '削除';
        }
    });
    
    // ページ全体にローディングオーバーレイを表示
    if (isLoading) {
        // ローディングオーバーレイを作成
        let overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                pointer-events: none;
            `;
            
            const loadingContent = document.createElement('div');
            loadingContent.style.cssText = `
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            `;
            loadingContent.innerHTML = `
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                <div>削除中...</div>
            `;
            
            overlay.appendChild(loadingContent);
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    } else {
        // ローディングオーバーレイを非表示
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
}
</script>
@endsection
