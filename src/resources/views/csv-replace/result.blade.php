@extends('layouts.app')

@section('title', 'CSV置換結果')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">CSV置換結果</h4>
                </div>
                <div class="card-body">
                    <!-- 統計情報 -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary">{{ $totalRows }}</h5>
                                    <p class="card-text">総行数</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $successCount }}</h5>
                                    <p class="card-text">成功</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $errorCount }}</h5>
                                    <p class="card-text">エラー</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $totalRows - $successCount - $errorCount }}</h5>
                                    <p class="card-text">スキップ</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ダウンロードボタン -->
                    <div class="text-center mb-4">
                        <a href="{{ route('csv-replace.download', $outputFileName) }}" class="btn btn-success btn-lg">
                            <i class="fas fa-download"></i> 置換後のCSVファイルをダウンロード
                        </a>
                    </div>

                    <!-- 置換結果詳細 -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">置換結果詳細</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>行番号</th>
                                            <th>元の値</th>
                                            <th>検索用技名</th>
                                            <th>置換後</th>
                                            <th>ステータス</th>
                                            <th>備考</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($replaceResults as $result)
                                        <tr class="{{ $result['status'] === 'success' ? 'table-success' : 'table-danger' }}">
                                            <td>{{ $result['row'] }}</td>
                                            <td>{{ $result['original'] }}</td>
                                            <td>
                                                @if(isset($result['normalized']) && $result['normalized'] !== $result['original'])
                                                    <span class="text-info">{{ $result['normalized'] }}</span>
                                                    <small class="text-muted d-block">(正規化済み)</small>
                                                @else
                                                    <span class="text-muted">{{ $result['original'] }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($result['status'] === 'success')
                                                    <span class="badge bg-primary">{{ $result['replaced'] }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($result['status'] === 'success')
                                                    <span class="badge bg-success">成功</span>
                                                @else
                                                    <span class="badge bg-danger">エラー</span>
                                                    <small class="text-muted d-block">{{ $result['error'] ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($result['note']))
                                                    <small class="text-info">{{ $result['note'] }}</small>
                                                @endif
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
                        <a href="{{ route('csv-replace.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> 新しいファイルを処理
                        </a>
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
            if (cells.length >= 4) {
                const rowNumber = cells[0].textContent;
                const original = cells[1].textContent;
                const replaced = cells[2].textContent;
                const status = cells[3].textContent;
                
                alert(`行番号: ${rowNumber}\n元の値: ${original}\n置換後: ${replaced}\nステータス: ${status}`);
            }
        });
        
        // ホバー効果
        row.style.cursor = 'pointer';
    });
});
</script>
@endsection
