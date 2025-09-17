<div class="sidebar">
    <div class="p-3">
        <div class="text-center mb-4">
            <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                <i class="fas fa-dragon text-primary" style="font-size: 24px;"></i>
            </div>
        </div>
        
        <nav class="nav flex-column">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="fas fa-home me-2"></i>ホーム
            </a>
            <a href="{{ route('pokemon.index') }}" class="nav-link {{ request()->routeIs('pokemon.*') ? 'active' : '' }}">
                <i class="fas fa-paw me-2"></i>ポケモン管理
            </a>
            <a href="{{ route('ai-optimization') }}" class="nav-link {{ request()->routeIs('ai-optimization') ? 'active' : '' }}">
                <i class="fas fa-robot me-2"></i>AI最適化
            </a>
            <a href="{{ route('bulk-data') }}" class="nav-link {{ request()->routeIs('bulk-data') ? 'active' : '' }}">
                <i class="fas fa-database me-2"></i>データ一括保存
            </a>
            <a href="{{ route('csv-replace.index') }}" class="nav-link {{ request()->routeIs('csv-replace.index') || request()->routeIs('csv-replace.process') || request()->routeIs('csv-replace.preview') || request()->routeIs('csv-replace.download') ? 'active' : '' }}">
                <i class="fas fa-exchange-alt me-2"></i>CSV置換ツール
            </a>
            <a href="{{ route('csv-replace.batch') }}" class="nav-link {{ request()->routeIs('csv-replace.batch') || request()->routeIs('csv-replace.batch-process') ? 'active' : '' }}">
                <i class="fas fa-layer-group me-2"></i>CSV一括置換
            </a>
        </nav>
    </div>
</div>
