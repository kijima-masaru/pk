@extends('layouts.app')

@section('title', 'データ一括保存 - ポケモン対戦サポート')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-database me-2 text-primary"></i>データ一括保存
            </h2>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-upload me-2"></i>JSONファイルからデータベースへ一括保存
                </h5>
            </div>
            <div class="card-body p-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('bulk-data.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="table" class="form-label">
                                <i class="fas fa-table me-2"></i>保存先テーブル
                            </label>
                            <select class="form-select @error('table') is-invalid @enderror" 
                                    id="table" 
                                    name="table" 
                                    required>
                                <option value="">テーブルを選択してください</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table }}" {{ old('table') == $table ? 'selected' : '' }}>
                                        {{ $table }}
                                    </option>
                                @endforeach
                            </select>
                            @error('table')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label">
                                <i class="fas fa-file-code me-2"></i>JSONファイル
                            </label>
                            
                            <!-- ファイル選択のタブ -->
                            <ul class="nav nav-tabs mb-3" id="fileTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">
                                        <i class="fas fa-upload me-1"></i>ファイルアップロード
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="existing-tab" data-bs-toggle="tab" data-bs-target="#existing" type="button" role="tab">
                                        <i class="fas fa-folder me-1"></i>既存ファイル
                                    </button>
                                </li>
                            </ul>
                            
                            <!-- タブコンテンツ -->
                            <div class="tab-content" id="fileTabsContent">
                                <!-- アップロードタブ -->
                                <div class="tab-pane fade show active" id="upload" role="tabpanel">
                                    <input type="file" 
                                           class="form-control @error('json_file') is-invalid @enderror" 
                                           id="json_file" 
                                           name="json_file" 
                                           accept=".json">
                                    @error('json_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            JSONファイル（最大10MB）を選択してください
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- 既存ファイルタブ -->
                                <div class="tab-pane fade" id="existing" role="tabpanel">
                                    <select class="form-select @error('existing_file') is-invalid @enderror" 
                                            id="existing_file" 
                                            name="existing_file">
                                        <option value="">既存ファイルを選択してください</option>
                                        @foreach($existingJsonFiles as $file)
                                            <option value="{{ $file }}" {{ old('existing_file') == $file ? 'selected' : '' }}>
                                                {{ $file }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('existing_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            public/json/basic_deta/ フォルダ内のファイルから選択
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-upload me-2"></i>データを一括保存
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>使用方法
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">JSONファイルの形式</h6>
                        <div id="json-examples">
                            <!-- デフォルトの例 -->
                            <pre class="bg-light p-3 rounded" id="default-example"><code>[
  {
    "id": 1,
    "name": "サンプルデータ",
    "description": "説明文"
  },
  {
    "id": 2,
    "name": "サンプルデータ2",
    "description": "説明文2"
  }
]</code></pre>

                            <!-- types テーブルの例 -->
                            <pre class="bg-light p-3 rounded d-none" id="types-example"><code>[
  {
    "id": 1,
    "name": "ノーマル"
  },
  {
    "id": 2,
    "name": "ほのお"
  },
  {
    "id": 3,
    "name": "みず"
  }
]</code></pre>

                            <!-- characteristics テーブルの例 -->
                            <pre class="bg-light p-3 rounded d-none" id="characteristics-example"><code>[
  {
    "id": 1,
    "name": "あついしぼう"
  },
  {
    "id": 2,
    "name": "いかく"
  },
  {
    "id": 3,
    "name": "うのミサイル"
  }
]</code></pre>

                            <!-- personalities テーブルの例 -->
                            <pre class="bg-light p-3 rounded d-none" id="personalities-example"><code>[
  {
    "id": 1,
    "name": "がんばりや",
    "rise": "A",
    "descent": "B"
  },
  {
    "id": 2,
    "name": "さみしがり",
    "rise": "A",
    "descent": "C"
  },
  {
    "id": 3,
    "name": "いじっぱり",
    "rise": "A",
    "descent": "D"
  }
]</code></pre>

                            <!-- goods テーブルの例 -->
                            <pre class="bg-light p-3 rounded d-none" id="goods-example"><code>[
  {
    "id": 1,
    "name": "きんのたま"
  },
  {
    "id": 2,
    "name": "しんかのきせき"
  },
  {
    "id": 3,
    "name": "こだわりハチマキ"
  }
]</code></pre>

                            <!-- field_effects テーブルの例 -->
                            <pre class="bg-light p-3 rounded d-none" id="field_effects-example"><code>[
  {
    "id": 1,
    "name": "晴れ"
  },
  {
    "id": 2,
    "name": "雨"
  },
  {
    "id": 3,
    "name": "砂嵐"
  }
]</code></pre>

                            <!-- status_conditions テーブルの例 -->
                            <pre class="bg-light p-3 rounded d-none" id="status_conditions-example"><code>[
  {
    "id": 1,
    "name": "どく"
  },
  {
    "id": 2,
    "name": "まひ"
  },
  {
    "id": 3,
    "name": "やけど"
  }
]</code></pre>

                            <!-- pokemons テーブルの例 -->
                            <pre class="bg-light p-3 rounded d-none" id="pokemons-example"><code>[
  {
    "id": 1,
    "name": "フシギダネ",
    "type1_id": 4,
    "type2_id": 8,
    "characteristics1_id": 1,
    "characteristics2_id": 2,
    "characteristics3_id": null,
    "characteristics4_id": null,
    "H": 45,
    "A": 49,
    "B": 49,
    "C": 65,
    "D": 65,
    "S": 45
  },
  {
    "id": 2,
    "name": "フシギソウ",
    "type1_id": 4,
    "type2_id": 8,
    "characteristics1_id": 1,
    "characteristics2_id": 2,
    "characteristics3_id": null,
    "characteristics4_id": null,
    "H": 60,
    "A": 62,
    "B": 63,
    "C": 80,
    "D": 80,
    "S": 60
  }
]</code></pre>

                            <!-- pokemon_forms テーブルの例 -->
                            <pre class="bg-light p-3 rounded d-none" id="pokemon_forms-example"><code>[
  {
    "id": 1,
    "name": "アローラのすがた",
    "type1_id": 6,
    "type2_id": 9,
    "characteristics1_id": 3,
    "characteristics2_id": 4,
    "characteristics3_id": null,
    "characteristics4_id": null,
    "H": 50,
    "A": 55,
    "B": 50,
    "C": 60,
    "D": 60,
    "S": 55,
    "pokemon_id": 25
  }
]</code></pre>

                            <!-- pokemon_megas テーブルの例 -->
                            <pre class="bg-light p-3 rounded d-none" id="pokemon_megas-example"><code>[
  {
    "id": 1,
    "name": "メガフシギバナ",
    "type1_id": 4,
    "type2_id": 8,
    "characteristics1_id": 5,
    "characteristics2_id": null,
    "characteristics3_id": null,
    "characteristics4_id": null,
    "H": 80,
    "A": 100,
    "B": 123,
    "C": 122,
    "D": 120,
    "S": 80,
    "pokemon_id": 3
  }
]</code></pre>

                            <!-- moves テーブルの例 -->
                            <pre class="bg-light p-3 rounded d-none" id="moves-example"><code>[
  {
    "id": 1,
    "name": "たいあたり",
    "type_id": 1,
    "category": "物理",
    "power": 40,
    "accuracy": 100,
    "PP": 35,
    "target": "1体選択"
  },
  {
    "id": 2,
    "name": "ひっかく",
    "type_id": 1,
    "category": "物理",
    "power": 40,
    "accuracy": 100,
    "PP": 35,
    "target": "1体選択"
  }
]</code></pre>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">利用可能なテーブル</h6>
                        <ul class="list-unstyled">
                            @foreach($tables as $table)
                                <li class="mb-1">
                                    <i class="fas fa-database me-2 text-muted"></i>{{ $table }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>注意事項</strong><br>
                    • 既存のデータは上書きされません（新規追加のみ）<br>
                    • 大量のデータを保存する場合は時間がかかる場合があります<br>
                    • エラーが発生した場合は、すべての変更がロールバックされます<br>
                    • 各テーブルに必要なフィールドのみが保存されます（不要なフィールドは自動的に無視されます）<br>
                    • 既存ファイルにはサブフォルダ（goods/, moves/, pokemons/, rules/）のファイルも含まれます
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableSelect = document.getElementById('table');
    const jsonExamples = document.getElementById('json-examples');
    const uploadTab = document.getElementById('upload-tab');
    const existingTab = document.getElementById('existing-tab');
    const jsonFileInput = document.getElementById('json_file');
    const existingFileSelect = document.getElementById('existing_file');
    
    // テーブル選択時のイベントリスナー
    tableSelect.addEventListener('change', function() {
        const selectedTable = this.value;
        
        // すべての例を非表示にする
        const allExamples = jsonExamples.querySelectorAll('pre');
        allExamples.forEach(example => {
            example.classList.add('d-none');
        });
        
        // 選択されたテーブルに対応する例を表示
        if (selectedTable) {
            const targetExample = document.getElementById(selectedTable + '-example');
            if (targetExample) {
                targetExample.classList.remove('d-none');
            }
        } else {
            // 何も選択されていない場合はデフォルトの例を表示
            const defaultExample = document.getElementById('default-example');
            if (defaultExample) {
                defaultExample.classList.remove('d-none');
            }
        }
    });
    
    // タブ切り替え時のイベントリスナー
    uploadTab.addEventListener('click', function() {
        existingFileSelect.required = false;
        jsonFileInput.required = true;
    });
    
    existingTab.addEventListener('click', function() {
        jsonFileInput.required = false;
        existingFileSelect.required = true;
    });
    
    // フォーム送信時のバリデーション
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const activeTab = document.querySelector('#fileTabsContent .tab-pane.active');
        
        if (activeTab.id === 'upload' && !jsonFileInput.files.length) {
            e.preventDefault();
            alert('JSONファイルを選択してください');
            return false;
        }
        
        if (activeTab.id === 'existing' && !existingFileSelect.value) {
            e.preventDefault();
            alert('既存ファイルを選択してください');
            return false;
        }
    });
    
    // 初期状態でデフォルトの例を表示
    const defaultExample = document.getElementById('default-example');
    if (defaultExample) {
        defaultExample.classList.remove('d-none');
    }
});
</script>
@endsection
