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
        </nav>
    </div>
</div>
