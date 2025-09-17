@extends('layouts.app')

@section('title', 'CSV一括置換ツール')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">CSV一括置換ツール</h4>
                            <small class="text-muted">ok_moveフォルダ内のタブ区切りCSVファイルを一括で処理します</small>
                        </div>
                        <a href="{{ route('csv-replace.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-upload"></i> 個別アップロード
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="batchForm" action="{{ route('csv-replace.batch-process') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="column_index" class="form-label">置換する列のインデックス</label>
                            <input type="number" class="form-control" id="column_index" name="column_index" min="0" value="1" required>
                            <div class="form-text">0から始まる列番号を入力してください（例：1列目は0、2列目は1）</div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>処理対象ファイル</h5>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                                        <i class="fas fa-check-square"></i> 全て選択
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                                        <i class="fas fa-square"></i> 全て解除
                                    </button>
                                </div>
                            </div>
                            
                            @if(count($csvFiles) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <th width="50">
                                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                                </th>
                                                <th>ファイル名</th>
                                                <th>サイズ</th>
                                                <th>更新日時</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($csvFiles as $file)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_files[]" value="{{ $file['path'] }}" class="form-check-input file-checkbox">
                                                </td>
                                                <td>
                                                    <i class="fas fa-file-csv text-success me-2"></i>
                                                    {{ $file['name'] }}
                                                </td>
                                                <td>{{ number_format($file['size']) }} bytes</td>
                                                <td>{{ date('Y-m-d H:i:s', $file['modified']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    ok_moveフォルダ内にCSVファイルが見つかりません。
                                </div>
                            @endif
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> 注意事項</h6>
                            <ul class="mb-0">
                                <li>選択したファイルは<strong>直接書き換え</strong>されます</li>
                                <li>元のファイルは復元できません</li>
                                <li>処理前にバックアップを取ることをお勧めします</li>
                                <li>2行ごとにセットで処理されます（技名行と詳細行）</li>
                            </ul>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg" id="processBtn" disabled>
                                <i class="fas fa-exchange-alt"></i> 一括置換実行
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 処理内容の説明 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">処理内容</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>処理の流れ</h6>
                            <ol>
                                <li>選択したCSVファイルを読み込み</li>
                                <li>指定した列の技名を正規化</li>
                                <li>moveテーブルからIDを検索</li>
                                <li>2行セットでIDに置換（置換したIDのみ残す）</li>
                                <li>元のファイルを上書き保存</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h6>正規化ルール</h6>
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
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const fileCheckboxes = document.querySelectorAll('.file-checkbox');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const processBtn = document.getElementById('processBtn');
    const batchForm = document.getElementById('batchForm');

    // 全選択チェックボックスの制御
    selectAllCheckbox.addEventListener('change', function() {
        fileCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateProcessButton();
    });

    // 個別チェックボックスの制御
    fileCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateProcessButton();
        });
    });

    // 全選択ボタン
    selectAllBtn.addEventListener('click', function() {
        fileCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        selectAllCheckbox.checked = true;
        updateProcessButton();
    });

    // 全解除ボタン
    deselectAllBtn.addEventListener('click', function() {
        fileCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAllCheckbox.checked = false;
        updateProcessButton();
    });

    // 全選択チェックボックスの状態更新
    function updateSelectAllState() {
        const checkedCount = document.querySelectorAll('.file-checkbox:checked').length;
        selectAllCheckbox.checked = checkedCount === fileCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < fileCheckboxes.length;
    }

    // 処理ボタンの有効/無効制御
    function updateProcessButton() {
        const checkedCount = document.querySelectorAll('.file-checkbox:checked').length;
        processBtn.disabled = checkedCount === 0;
    }

    // フォーム送信時の確認
    batchForm.addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.file-checkbox:checked').length;
        if (checkedCount === 0) {
            e.preventDefault();
            alert('処理対象のファイルを選択してください。');
            return;
        }

        if (!confirm(`${checkedCount}個のファイルを一括処理します。元のファイルは上書きされます。続行しますか？`)) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
