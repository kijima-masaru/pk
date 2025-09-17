@extends('layouts.app')

@section('title', '一括置換結果')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">一括置換結果</h4>
                </div>
                <div class="card-body">
                    <!-- 統計情報 -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary">{{ $totalFiles }}</h5>
                                    <p class="card-text">処理ファイル数</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $totalSuccess }}</h5>
                                    <p class="card-text">成功</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $totalError }}</h5>
                                    <p class="card-text">エラー</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $columnIndex }}</h5>
                                    <p class="card-text">処理列</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 成功メッセージ -->
                    @if($totalSuccess > 0)
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> 一括処理が完了しました</h5>
                        <p class="mb-0">
                            {{ $totalSuccess }}個のファイルが正常に処理されました。
                            @if($totalError > 0)
                                {{ $totalError }}個のファイルでエラーが発生しました。
                            @endif
                        </p>
                    </div>
                    @endif

                    <!-- エラーメッセージ -->
                    @if($totalError > 0)
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> エラーが発生しました</h5>
                        <p class="mb-0">{{ $totalError }}個のファイルでエラーが発生しました。詳細は下記の結果表をご確認ください。</p>
                    </div>
                    @endif

                    <!-- 処理結果詳細 -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">処理結果詳細</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ファイル名</th>
                                            <th>ステータス</th>
                                            <th>成功件数</th>
                                            <th>エラー件数</th>
                                            <th>メッセージ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($results as $result)
                                        <tr class="{{ $result['status'] === 'success' ? 'table-success' : 'table-danger' }}">
                                            <td>
                                                <i class="fas fa-file-csv text-success me-2"></i>
                                                {{ $result['file'] }}
                                            </td>
                                            <td>
                                                @if($result['status'] === 'success')
                                                    <span class="badge bg-success">成功</span>
                                                @else
                                                    <span class="badge bg-danger">エラー</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($result['success_count'] > 0)
                                                    <span class="badge bg-primary">{{ $result['success_count'] }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($result['error_count'] > 0)
                                                    <span class="badge bg-warning">{{ $result['error_count'] }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $result['message'] }}</small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- アクションボタン -->
                    <div class="text-center mt-4">
                        <a href="{{ route('csv-replace.batch') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> 一括処理に戻る
                        </a>
                        <a href="{{ route('csv-replace.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-upload"></i> 個別アップロード
                        </a>
                    </div>
                </div>
            </div>

            <!-- 処理内容の説明 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">処理内容の説明</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>処理された内容</h6>
                            <ul>
                                <li>各CSVファイルの列{{ $columnIndex + 1 }}（インデックス: {{ $columnIndex }}）を処理</li>
                                <li>技名を正規化してmoveテーブルからIDを検索</li>
                                <li>2行セットでIDに置換（置換したIDのみ残す）</li>
                                <li>元のファイルを直接上書き保存</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>正規化された技名</h6>
                            <ul>
                                <li><code>New</code> → 除去</li>
                                <li><code>[遺伝経路]</code> → 除去</li>
                                <li><code>色名</code> → 碧、藍、赤、青など除去</li>
                                <li><code>[その他]</code> → 括弧内文字を除去</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// テーブルの行をクリックした時の詳細表示
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            const cells = this.querySelectorAll('td');
            if (cells.length >= 5) {
                const fileName = cells[0].textContent.trim();
                const status = cells[1].textContent.trim();
                const successCount = cells[2].textContent.trim();
                const errorCount = cells[3].textContent.trim();
                const message = cells[4].textContent.trim();
                
                alert(`ファイル: ${fileName}\nステータス: ${status}\n成功: ${successCount}\nエラー: ${errorCount}\nメッセージ: ${message}`);
            }
        });
        
        // ホバー効果
        row.style.cursor = 'pointer';
    });
});
</script>
@endsection
