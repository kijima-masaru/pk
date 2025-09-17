<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI ポケモン最適化システム</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8">AI ポケモン最適化システム</h1>
        
        <!-- 最適化設定 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">最適化設定</h2>
            
            <form id="optimizationForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            世代数
                        </label>
                        <input
                            type="number"
                            name="generations"
                            value="100"
                            min="10"
                            max="1000"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            個体数
                        </label>
                        <input
                            type="number"
                            name="population_size"
                            value="50"
                            min="20"
                            max="200"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            制約条件
                        </label>
                        <select
                            name="constraint_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">制約なし</option>
                            <option value="balanced">バランス型</option>
                            <option value="offensive">攻撃特化</option>
                            <option value="defensive">防御特化</option>
                            <option value="speed">素早さ特化</option>
                        </select>
                    </div>
                </div>
                
                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700 transition-colors"
                >
                    AI最適化を開始
                </button>
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            パーティ1 ID
                        </label>
                        <input
                            type="number"
                            name="party1_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            パーティ2 ID
                        </label>
                        <input
                            type="number"
                            name="party2_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                </div>
                
                <button
                    type="submit"
                    class="w-full bg-red-600 text-white py-3 px-6 rounded-md hover:bg-red-700 transition-colors"
                >
                    対戦シミュレーション実行
                </button>
            </form>
            
            <div id="battleResult" class="mt-4 p-4 bg-gray-50 rounded-lg" style="display: none;">
                <h3 class="font-semibold mb-2">対戦結果</h3>
                <div id="battleResultContent"></div>
            </div>
        </div>
    </div>

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
            
            resultContent.innerHTML = '<div class="text-center py-4">最適化を実行中...</div>';
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
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-green-800">最適化完了</h3>
                                    <p class="text-green-600">適応度: ${result.result.best_fitness.toFixed(2)}</p>
                                </div>
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-blue-800">世代数</h3>
                                    <p class="text-blue-600">${result.result.generation_count}</p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-purple-800">最良個体</h3>
                                    <p class="text-purple-600">${result.result.best_individual.length}匹のポケモン</p>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h3 class="font-semibold mb-2">最適化されたパーティ</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    ${result.result.best_individual.map((pokemon, index) => `
                                        <div class="border rounded-lg p-4">
                                            <h4 class="font-semibold mb-2">ポケモン ${index + 1}</h4>
                                            <div class="space-y-1 text-sm">
                                                <div><span class="font-medium">ポケモンID:</span> ${pokemon.pokemon_id}</div>
                                                <div><span class="font-medium">性格ID:</span> ${pokemon.personality_id}</div>
                                                <div><span class="font-medium">特性ID:</span> ${pokemon.characteristics_id}</div>
                                                <div><span class="font-medium">持ち物ID:</span> ${pokemon.goods_id}</div>
                                                <div><span class="font-medium">努力値:</span> H:${pokemon.effort_values.H} A:${pokemon.effort_values.A} B:${pokemon.effort_values.B} C:${pokemon.effort_values.C} D:${pokemon.effort_values.D} S:${pokemon.effort_values.S}</div>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                            
                            <button onclick="saveParty('${JSON.stringify(result.result.best_individual).replace(/"/g, '&quot;')}')" 
                                    class="w-full bg-green-600 text-white py-3 px-6 rounded-md hover:bg-green-700 transition-colors">
                                最適化されたパーティを保存
                            </button>
                        </div>
                    `;
                } else {
                    resultContent.innerHTML = `<div class="text-red-600">エラー: ${result.message}</div>`;
                }
            } catch (error) {
                console.error('最適化エラー:', error);
                resultContent.innerHTML = '<div class="text-red-600">最適化中にエラーが発生しました</div>';
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
            
            battleResultContent.innerHTML = '<div class="text-center py-4">対戦シミュレーションを実行中...</div>';
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
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium">勝者:</span>
                                <span class="text-green-600">パーティ${result.result.winner}</span>
                            </div>
                            <div>
                                <span class="font-medium">ターン数:</span>
                                <span>${result.result.turns}</span>
                            </div>
                            <div>
                                <span class="font-medium">与えたダメージ:</span>
                                <span>${result.result.damage_dealt}</span>
                            </div>
                            <div>
                                <span class="font-medium">受けたダメージ:</span>
                                <span>${result.result.damage_taken}</span>
                            </div>
                        </div>
                    `;
                } else {
                    battleResultContent.innerHTML = `<div class="text-red-600">エラー: ${result.message}</div>`;
                }
            } catch (error) {
                console.error('対戦シミュレーションエラー:', error);
                battleResultContent.innerHTML = '<div class="text-red-600">対戦シミュレーション中にエラーが発生しました</div>';
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
</body>
</html>
