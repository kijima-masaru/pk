@extends('layouts.app')

@section('title', 'AI ポケモン最適化システム')

@section('content')
<div class="ai-optimization">
    <h1 class="text-3xl font-bold text-center mb-8">AI ポケモン最適化システム</h1>
    
    <!-- 最適化設定 -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">最適化設定</h2>
        
        <form id="optimizationForm" class="space-y-4">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">
                        世代数
                    </label>
                    <input
                        type="number"
                        name="generations"
                        value="100"
                        min="10"
                        max="1000"
                        class="form-control"
                    />
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">
                        個体数
                    </label>
                    <input
                        type="number"
                        name="population_size"
                        value="50"
                        min="20"
                        max="200"
                        class="form-control"
                    />
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">
                        制約条件
                    </label>
                    <select
                        name="constraint_type"
                        class="form-select"
                    >
                        <option value="">制約なし</option>
                        <option value="balanced">バランス型</option>
                        <option value="offensive">攻撃特化</option>
                        <option value="defensive">防御特化</option>
                        <option value="speed">素早さ特化</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <button
                    type="submit"
                    class="btn btn-primary btn-lg w-100"
                >
                    <i class="fas fa-robot me-2"></i>AI最適化を開始
                </button>
            </div>
        </form>
    </div>

    <!-- 結果表示エリア -->
    <div id="resultArea" class="bg-white rounded-lg shadow-md p-6 mb-8" style="display: none;">
        <h2 class="text-xl font-semibold mb-4">最適化結果</h2>
        <div id="resultContent"></div>
    </div>

    <!-- 対戦シミュレーション -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">対戦シミュレーション</h2>
        
        <form id="battleForm" class="space-y-4">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">
                        パーティ1 ID
                    </label>
                    <input
                        type="number"
                        name="party1_id"
                        class="form-control"
                    />
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">
                        パーティ2 ID
                    </label>
                    <input
                        type="number"
                        name="party2_id"
                        class="form-control"
                    />
                </div>
            </div>
            
            <div class="mt-4">
                <button
                    type="submit"
                    class="btn btn-danger btn-lg w-100"
                >
                    <i class="fas fa-sword me-2"></i>対戦シミュレーション実行
                </button>
            </div>
        </form>
        
        <div id="battleResult" class="mt-4 p-4 bg-light rounded" style="display: none;">
            <h3 class="font-semibold mb-2">対戦結果</h3>
            <div id="battleResultContent"></div>
        </div>
    </div>
</div>

<style>
.ai-optimization {
    min-height: 100vh;
}

.space-y-4 > * + * {
    margin-top: 1rem;
}

.space-y-1 > * + * {
    margin-top: 0.25rem;
}

.grid {
    display: grid;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .md\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
</style>

<script>
    // CSRFトークンを取得
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // 最適化フォームの処理
    document.getElementById('optimizationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            generations: parseInt(formData.get('generations')),
            population_size: parseInt(formData.get('population_size')),
            constraints: {
                type: formData.get('constraint_type')
            }
        };
        
        const resultArea = document.getElementById('resultArea');
        const resultContent = document.getElementById('resultContent');
        
        resultContent.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>最適化を実行中...</div>';
        resultArea.style.display = 'block';
        
        try {
            const response = await fetch('/api/ai-optimization/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                resultContent.innerHTML = `
                    <div class="space-y-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">最適化完了</h5>
                                        <p class="card-text">適応度: ${result.result.best_fitness.toFixed(2)}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">世代数</h5>
                                        <p class="card-text">${result.result.generation_count}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">最良個体</h5>
                                        <p class="card-text">${result.result.best_individual.length}匹のポケモン</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h3 class="font-semibold mb-2">最適化されたパーティ</h3>
                            <div class="row">
                                ${result.result.best_individual.map((pokemon, index) => `
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">ポケモン ${index + 1}</h5>
                                                <div class="space-y-1 small">
                                                    <div><span class="fw-bold">ポケモンID:</span> ${pokemon.pokemon_id}</div>
                                                    <div><span class="fw-bold">性格ID:</span> ${pokemon.personality_id}</div>
                                                    <div><span class="fw-bold">特性ID:</span> ${pokemon.characteristics_id}</div>
                                                    <div><span class="fw-bold">持ち物ID:</span> ${pokemon.goods_id}</div>
                                                    <div><span class="fw-bold">努力値:</span> H:${pokemon.effort_values.H} A:${pokemon.effort_values.A} B:${pokemon.effort_values.B} C:${pokemon.effort_values.C} D:${pokemon.effort_values.D} S:${pokemon.effort_values.S}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        
                        <button onclick="saveParty('${JSON.stringify(result.result.best_individual).replace(/"/g, '&quot;')}')" 
                                class="btn btn-success btn-lg w-100">
                            <i class="fas fa-save me-2"></i>最適化されたパーティを保存
                        </button>
                    </div>
                `;
            } else {
                resultContent.innerHTML = `<div class="alert alert-danger">エラー: ${result.message}</div>`;
            }
        } catch (error) {
            console.error('最適化エラー:', error);
            resultContent.innerHTML = '<div class="alert alert-danger">最適化中にエラーが発生しました</div>';
        }
    });
    
    // 対戦フォームの処理
    document.getElementById('battleForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            party1_id: parseInt(formData.get('party1_id')),
            party2_id: parseInt(formData.get('party2_id'))
        };
        
        const battleResult = document.getElementById('battleResult');
        const battleResultContent = document.getElementById('battleResultContent');
        
        battleResultContent.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>対戦シミュレーションを実行中...</div>';
        battleResult.style.display = 'block';
        
        try {
            const response = await fetch('/api/ai-optimization/simulate-battle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                battleResultContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="fw-bold">勝者:</span>
                                <span class="badge bg-success">パーティ${result.result.winner}</span>
                            </div>
                            <div class="mb-2">
                                <span class="fw-bold">ターン数:</span>
                                <span>${result.result.turns}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="fw-bold">与えたダメージ:</span>
                                <span>${result.result.damage_dealt}</span>
                            </div>
                            <div class="mb-2">
                                <span class="fw-bold">受けたダメージ:</span>
                                <span>${result.result.damage_taken}</span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                battleResultContent.innerHTML = `<div class="alert alert-danger">エラー: ${result.message}</div>`;
            }
        } catch (error) {
            console.error('対戦シミュレーションエラー:', error);
            battleResultContent.innerHTML = '<div class="alert alert-danger">対戦シミュレーション中にエラーが発生しました</div>';
        }
    });
    
    // パーティ保存関数
    async function saveParty(individualData) {
        const partyName = prompt('パーティ名を入力してください:');
        if (!partyName) return;
        
        try {
            const response = await fetch('/api/ai-optimization/save-party', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    party_name: partyName,
                    optimized_individual: JSON.parse(individualData),
                    user_id: {{ auth()->id() ?? 1 }}
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('パーティが保存されました！');
            } else {
                alert('保存に失敗しました: ' + result.message);
            }
        } catch (error) {
            console.error('保存エラー:', error);
            alert('保存中にエラーが発生しました');
        }
    }
</script>
@endsection