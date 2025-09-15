<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ポケモン対戦サポート')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 8px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.2);
        }
        .main-content {
            padding: 0;
        }
        .header {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
        }
        .content-area {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- サイドバー -->
            <div class="col-md-3 col-lg-2 px-0">
                @include('components.sidebar')
            </div>
            
            <!-- メインコンテンツ -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- ヘッダー -->
                @include('components.header')
                
                <!-- コンテンツエリア -->
                <div class="content-area">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
