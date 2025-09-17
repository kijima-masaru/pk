@extends('layouts.app')

@section('title', 'ポケモン登録 - ポケモン対戦サポート')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-plus-circle me-2 text-success"></i>ポケモン登録
            </h2>
            <a href="{{ route('pokemon.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-2"></i>一覧に戻る
            </a>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <h5><i class="fas fa-exclamation-triangle me-2"></i>入力エラーがあります</h5>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('pokemon.store') }}" method="POST" id="pokemonForm">
    @csrf
    
    <div class="row">
        <!-- 基本情報 -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>基本情報</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">ニックネーム <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pokemon_search" class="form-label">ポケモン <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pokemon_search" placeholder="ポケモン名を入力してください" value="{{ $selectedPokemonName ?? '' }}" autocomplete="off">
                        <input type="hidden" id="pokemon_id" name="pokemon_id" value="{{ old('pokemon_id') }}" required>
                        <div id="pokemon_suggestions" class="list-group position-absolute" style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto; width: 100%;"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pokemon_form_id" class="form-label">フォーム</label>
                        <select class="form-select" id="pokemon_form_id" name="pokemon_form_id">
                            <option value="">フォームを選択してください</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="level" class="form-label">レベル <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="level" name="level" value="{{ old('level', 50) }}" min="1" max="100" required>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 性格・特性・持ち物 -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>性格・特性・持ち物</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="personality_id" class="form-label">性格 <span class="text-danger">*</span></label>
                        <select class="form-select" id="personality_id" name="personality_id" required>
                            <option value="">性格を選択してください</option>
                            @foreach($personalities as $personality)
                                <option value="{{ $personality->id }}" {{ old('personality_id') == $personality->id ? 'selected' : '' }}>
                                    {{ $personality->name }} ({{ $personality->rise }}↑ {{ $personality->descent }}↓)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="characteristics_id" class="form-label">特性 <span class="text-danger">*</span></label>
                        <select class="form-select" id="characteristics_id" name="characteristics_id" required>
                            <option value="">特性を選択してください</option>
                            @foreach($characteristics as $characteristic)
                                <option value="{{ $characteristic->id }}" {{ old('characteristics_id') == $characteristic->id ? 'selected' : '' }}>
                                    {{ $characteristic->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="goods_id" class="form-label">持ち物</label>
                        <select class="form-select" id="goods_id" name="goods_id">
                            <option value="">持ち物を選択してください</option>
                            @foreach($goods as $good)
                                <option value="{{ $good->id }}" {{ old('goods_id') == $good->id ? 'selected' : '' }}>
                                    {{ $good->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 努力値 -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>努力値 (最大510)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="H_effort_values" class="form-label">HP</label>
                            <input type="number" class="form-control effort-value" id="H_effort_values" name="H_effort_values" value="{{ old('H_effort_values', 0) }}" min="0" max="252" required>
                            <div class="invalid-feedback">
                                各項目の最大値は252です。
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="A_effort_values" class="form-label">攻撃</label>
                            <input type="number" class="form-control effort-value" id="A_effort_values" name="A_effort_values" value="{{ old('A_effort_values', 0) }}" min="0" max="252" required>
                            <div class="invalid-feedback">
                                各項目の最大値は252です。
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="B_effort_values" class="form-label">防御</label>
                            <input type="number" class="form-control effort-value" id="B_effort_values" name="B_effort_values" value="{{ old('B_effort_values', 0) }}" min="0" max="252" required>
                            <div class="invalid-feedback">
                                各項目の最大値は252です。
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="C_effort_values" class="form-label">特攻</label>
                            <input type="number" class="form-control effort-value" id="C_effort_values" name="C_effort_values" value="{{ old('C_effort_values', 0) }}" min="0" max="252" required>
                            <div class="invalid-feedback">
                                各項目の最大値は252です。
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="D_effort_values" class="form-label">特防</label>
                            <input type="number" class="form-control effort-value" id="D_effort_values" name="D_effort_values" value="{{ old('D_effort_values', 0) }}" min="0" max="252" required>
                            <div class="invalid-feedback">
                                各項目の最大値は252です。
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="S_effort_values" class="form-label">素早さ</label>
                            <input type="number" class="form-control effort-value" id="S_effort_values" name="S_effort_values" value="{{ old('S_effort_values', 0) }}" min="0" max="252" required>
                            <div class="invalid-feedback">
                                各項目の最大値は252です。
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <strong>努力値合計: <span id="totalEffortValues">0</span>/510</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 技 -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>技 (最大4つ)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="move1_id" class="form-label">技1</label>
                            <select class="form-select" id="move1_id" name="move1_id" disabled>
                                <option value="">ポケモンを選択してください</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="move2_id" class="form-label">技2</label>
                            <select class="form-select" id="move2_id" name="move2_id" disabled>
                                <option value="">ポケモンを選択してください</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="move3_id" class="form-label">技3</label>
                            <select class="form-select" id="move3_id" name="move3_id" disabled>
                                <option value="">ポケモンを選択してください</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="move4_id" class="form-label">技4</label>
                            <select class="form-select" id="move4_id" name="move4_id" disabled>
                                <option value="">ポケモンを選択してください</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 送信ボタン -->
    <div class="row">
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save me-2"></i>ポケモンを登録
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 努力値の合計計算
    function updateEffortValuesTotal() {
        const effortInputs = document.querySelectorAll('.effort-value');
        let total = 0;
        let hasInvalidValue = false;
        
        effortInputs.forEach(input => {
            const value = parseInt(input.value) || 0;
            total += value;
            
            // 各項目の最大値252チェック
            if (value > 252) {
                input.classList.add('is-invalid');
                hasInvalidValue = true;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        document.getElementById('totalEffortValues').textContent = total;
        
        // 510を超えた場合または各項目が252を超えた場合は警告色に変更
        const totalElement = document.getElementById('totalEffortValues');
        if (total > 510 || hasInvalidValue) {
            totalElement.parentElement.className = 'alert alert-danger';
        } else {
            totalElement.parentElement.className = 'alert alert-info';
        }
    }
    
    // 努力値入力時のイベント
    document.querySelectorAll('.effort-value').forEach(input => {
        input.addEventListener('input', updateEffortValuesTotal);
    });
    
    // 初期計算
    updateEffortValuesTotal();
    
    // フォーム送信時のバリデーション
    document.getElementById('pokemonForm').addEventListener('submit', function(e) {
        const effortInputs = document.querySelectorAll('.effort-value');
        let hasInvalidValue = false;
        
        effortInputs.forEach(input => {
            const value = parseInt(input.value) || 0;
            if (value > 252) {
                input.classList.add('is-invalid');
                hasInvalidValue = true;
            }
        });
        
        if (hasInvalidValue) {
            e.preventDefault();
            alert('努力値の各項目は252以下である必要があります。');
        }
    });
    
    // ポケモン検索機能
    const pokemonSearchInput = document.getElementById('pokemon_search');
    const pokemonIdInput = document.getElementById('pokemon_id');
    const pokemonSuggestions = document.getElementById('pokemon_suggestions');
    let searchTimeout;

    pokemonSearchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 1) {
            pokemonSuggestions.style.display = 'none';
            pokemonIdInput.value = '';
            // 技の選択肢を無効化
            loadPokemonMoves(null);
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetch(`/pokemon/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(pokemons => {
                    displayPokemonSuggestions(pokemons);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }, 300);
    });

    function displayPokemonSuggestions(pokemons) {
        pokemonSuggestions.innerHTML = '';
        
        if (pokemons.length === 0) {
            pokemonSuggestions.style.display = 'none';
            return;
        }
        
        pokemons.forEach(pokemon => {
            const item = document.createElement('div');
            item.className = 'list-group-item list-group-item-action';
            item.textContent = pokemon.name;
            item.addEventListener('click', function() {
                pokemonSearchInput.value = pokemon.name;
                pokemonIdInput.value = pokemon.id;
                pokemonSuggestions.style.display = 'none';
                
                // フォーム取得
                loadPokemonForms(pokemon.id);
                
                // 技の選択肢を更新
                loadPokemonMoves(pokemon.id);
            });
            pokemonSuggestions.appendChild(item);
        });
        
        pokemonSuggestions.style.display = 'block';
    }

    // クリック外部でサジェストを非表示
    document.addEventListener('click', function(e) {
        if (!pokemonSearchInput.contains(e.target) && !pokemonSuggestions.contains(e.target)) {
            pokemonSuggestions.style.display = 'none';
        }
    });

    // ポケモン選択時のフォーム取得
    function loadPokemonForms(pokemonId) {
        const formSelect = document.getElementById('pokemon_form_id');
        
        if (pokemonId) {
            fetch(`/pokemon/forms?pokemon_id=${pokemonId}`)
                .then(response => response.json())
                .then(forms => {
                    formSelect.innerHTML = '<option value="">フォームを選択してください</option>';
                    forms.forEach(form => {
                        const option = document.createElement('option');
                        option.value = form.id;
                        option.textContent = form.name;
                        formSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        } else {
            formSelect.innerHTML = '<option value="">フォームを選択してください</option>';
        }
    }

    // ポケモンが覚えられる技を取得
    function loadPokemonMoves(pokemonId) {
        const moveSelects = ['move1_id', 'move2_id', 'move3_id', 'move4_id'];
        
        if (pokemonId) {
            fetch(`/pokemon/moves?pokemon_id=${pokemonId}`)
                .then(response => response.json())
                .then(moves => {
                    moveSelects.forEach(selectId => {
                        const select = document.getElementById(selectId);
                        select.innerHTML = '<option value="">技を選択してください</option>';
                        
                        moves.forEach(move => {
                            const option = document.createElement('option');
                            option.value = move.id;
                            option.textContent = move.name;
                            select.appendChild(option);
                        });
                        
                        // 選択肢を有効化
                        select.disabled = false;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    // エラー時は選択肢を無効化
                    moveSelects.forEach(selectId => {
                        const select = document.getElementById(selectId);
                        select.innerHTML = '<option value="">技の取得に失敗しました</option>';
                        select.disabled = true;
                    });
                });
        } else {
            // ポケモンが選択されていない場合は選択肢を無効化
            moveSelects.forEach(selectId => {
                const select = document.getElementById(selectId);
                select.innerHTML = '<option value="">ポケモンを選択してください</option>';
                select.disabled = true;
            });
        }
    }

    // 初期値がある場合の処理
    const initialPokemonId = pokemonIdInput.value;
    if (initialPokemonId) {
        loadPokemonForms(initialPokemonId);
        loadPokemonMoves(initialPokemonId);
    }
});
</script>
@endsection
