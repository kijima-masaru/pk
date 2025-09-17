@extends('layouts.app')

@section('title', 'CSV置換ツール')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">CSV置換ツール</h4>
                            <small class="text-muted">ポケモンの技名をIDに置換します</small>
                        </div>
                        <a href="{{ route('csv-replace.batch') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-layer-group"></i> 一括処理
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="csvReplaceForm" action="{{ route('csv-replace.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="csv_file" class="form-label">CSVファイルを選択</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                            <div class="form-text">タブ区切りのCSVファイルまたはテキストファイルを選択してください</div>
                        </div>

                        <div class="mb-4">
                            <label for="column_index" class="form-label">置換する列のインデックス</label>
                            <input type="number" class="form-control" id="column_index" name="column_index" min="0" value="1" required>
                            <div class="form-text">0から始まる列番号を入力してください（例：1列目は0、2列目は1）</div>
                        </div>

                        <div class="mb-4">
                            <button type="button" class="btn btn-outline-primary" id="previewBtn">
                                <i class="fas fa-eye"></i> プレビュー
                            </button>
                        </div>

                        <!-- プレビュー結果 -->
                        <div id="previewResult" class="mb-4" style="display: none;">
                            <h6>プレビュー結果</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="previewTable">
                                    <thead class="table-light">
                                        <tr id="previewHeader"></tr>
                                    </thead>
                                    <tbody id="previewBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-exchange-alt"></i> 置換実行
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 使用例 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">使用例</h5>
                </div>
                <div class="card-body">
                    <p>以下のようなCSVファイルの場合：</p>
                    <pre class="bg-light p-3 rounded"><code>基本	たいあたり
ノーマル	物理	40	100	35	○	通常攻撃。
基本	なきごえ
ノーマル	変化	-	100	40	×	相手全体が対象。
基本	つるのムチ
くさ	物理	45	100	25	○	通常攻撃。</code></pre>
                    
                    <p>列インデックス「1」を指定すると、<strong>2行ごとにセット</strong>で処理されます：</p>
                    <ul>
                        <li>1行目: <code>基本	たいあたり</code> → <code>123</code></li>
                        <li>2行目: <code>ノーマル	物理	40	100	35	○	通常攻撃。</code> → <code>123</code></li>
                        <li>3行目: <code>基本	なきごえ</code> → <code>456</code></li>
                        <li>4行目: <code>ノーマル	変化	-	100	40	×	相手全体が対象。</code> → <code>456</code></li>
                        <li>以下同様...（置換したIDのみが残ります）</li>
                    </ul>
                    
                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-info-circle"></i> 技名の正規化</h6>
                        <p class="mb-0">以下の接尾辞は自動的に除去されて検索されます：</p>
                        <ul class="mb-0">
                            <li><code>New</code> → 「アシッドボムNew」→「アシッドボム」</li>
                            <li><code>[遺伝経路]</code> → 「ねをはる[遺伝経路]」→「ねをはる」</li>
                            <li><code>色名</code> → 「ほえる碧New」→「ほえる」</li>
                            <li><code>[その他]</code> → 括弧内の文字も除去</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('previewBtn');
    const csvFileInput = document.getElementById('csv_file');
    const previewResult = document.getElementById('previewResult');
    const previewTable = document.getElementById('previewTable');
    const previewHeader = document.getElementById('previewHeader');
    const previewBody = document.getElementById('previewBody');

    previewBtn.addEventListener('click', function() {
        const file = csvFileInput.files[0];
        if (!file) {
            alert('CSVファイルを選択してください。');
            return;
        }

        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('{{ route("csv-replace.preview") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.preview && data.preview.length > 0) {
                // ヘッダーを作成
                previewHeader.innerHTML = '';
                for (let i = 0; i < data.total_columns; i++) {
                    const th = document.createElement('th');
                    th.textContent = `列 ${i}`;
                    th.className = i == document.getElementById('column_index').value ? 'table-warning' : '';
                    previewHeader.appendChild(th);
                }

                // ボディを作成
                previewBody.innerHTML = '';
                data.preview.forEach(row => {
                    const tr = document.createElement('tr');
                    row.forEach((cell, index) => {
                        const td = document.createElement('td');
                        td.textContent = cell || '';
                        td.className = index == document.getElementById('column_index').value ? 'table-warning' : '';
                        tr.appendChild(td);
                    });
                    previewBody.appendChild(tr);
                });

                previewResult.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('プレビューの取得に失敗しました。');
        });
    });

    // 列インデックスが変更された時のハイライト更新
    document.getElementById('column_index').addEventListener('change', function() {
        if (previewResult.style.display !== 'none') {
            previewBtn.click(); // プレビューを再実行
        }
    });
});
</script>
@endsection
