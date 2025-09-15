<div class="header d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <h4 class="mb-0 text-primary">
            <i class="fas fa-dragon me-2"></i>ポケモン対戦サポート
        </h4>
    </div>
    
    <div class="d-flex align-items-center">
        <span class="me-3 text-muted">
            <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}さん
        </span>
        
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i>ログアウト
            </button>
        </form>
    </div>
</div>
