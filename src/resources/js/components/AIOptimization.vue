<template>
  <div class="ai-optimization">
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold text-center mb-8">AI ポケモン最適化システム</h1>
      
      <!-- 最適化設定 -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">最適化設定</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              世代数
            </label>
            <input
              v-model="settings.generations"
              type="number"
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
              v-model="settings.populationSize"
              type="number"
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
              v-model="settings.constraints.type"
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
          @click="startOptimization"
          :disabled="isOptimizing"
          class="w-full bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
        >
          {{ isOptimizing ? '最適化中...' : 'AI最適化を開始' }}
        </button>
      </div>

      <!-- 進捗表示 -->
      <div v-if="isOptimizing || optimizationResult" class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">最適化進捗</h2>
        
        <div v-if="isOptimizing" class="mb-4">
          <div class="flex justify-between text-sm text-gray-600 mb-2">
            <span>進捗: {{ progress.currentGeneration }} / {{ progress.totalGenerations }}</span>
            <span>{{ Math.round((progress.currentGeneration / progress.totalGenerations) * 100) }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-blue-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: (progress.currentGeneration / progress.totalGenerations) * 100 + '%' }"
            ></div>
          </div>
        </div>
        
        <div v-if="optimizationResult" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-50 p-4 rounded-lg">
              <h3 class="font-semibold text-green-800">最適化完了</h3>
              <p class="text-green-600">適応度: {{ optimizationResult.best_fitness.toFixed(2) }}</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg">
              <h3 class="font-semibold text-blue-800">世代数</h3>
              <p class="text-blue-600">{{ optimizationResult.generation_count }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
              <h3 class="font-semibold text-purple-800">最良個体</h3>
              <p class="text-purple-600">{{ optimizationResult.best_individual.length }}匹のポケモン</p>
            </div>
          </div>
          
          <button
            @click="saveOptimizedParty"
            class="w-full bg-green-600 text-white py-3 px-6 rounded-md hover:bg-green-700 transition-colors"
          >
            最適化されたパーティを保存
          </button>
        </div>
      </div>

      <!-- 最適化結果の詳細 -->
      <div v-if="optimizationResult" class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">最適化結果</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="(pokemon, index) in optimizationResult.best_individual"
            :key="index"
            class="border rounded-lg p-4 hover:shadow-md transition-shadow"
          >
            <h3 class="font-semibold mb-2">ポケモン {{ index + 1 }}</h3>
            <div class="space-y-2 text-sm">
              <div>
                <span class="font-medium">ポケモン:</span>
                <span>{{ getPokemonName(pokemon.pokemon_id) }}</span>
              </div>
              <div>
                <span class="font-medium">性格:</span>
                <span>{{ getPersonalityName(pokemon.personality_id) }}</span>
              </div>
              <div>
                <span class="font-medium">特性:</span>
                <span>{{ getCharacteristicName(pokemon.characteristics_id) }}</span>
              </div>
              <div>
                <span class="font-medium">持ち物:</span>
                <span>{{ getGoodsName(pokemon.goods_id) }}</span>
              </div>
              <div>
                <span class="font-medium">努力値:</span>
                <div class="grid grid-cols-2 gap-1 text-xs">
                  <span>H: {{ pokemon.effort_values.H }}</span>
                  <span>A: {{ pokemon.effort_values.A }}</span>
                  <span>B: {{ pokemon.effort_values.B }}</span>
                  <span>C: {{ pokemon.effort_values.C }}</span>
                  <span>D: {{ pokemon.effort_values.D }}</span>
                  <span>S: {{ pokemon.effort_values.S }}</span>
                </div>
              </div>
              <div>
                <span class="font-medium">技:</span>
                <div class="text-xs space-y-1">
                  <div v-for="(moveId, moveIndex) in pokemon.moves" :key="moveIndex">
                    <span v-if="moveId">{{ getMoveName(moveId) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 対戦シミュレーション -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">対戦シミュレーション</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              パーティ1 ID
            </label>
            <input
              v-model="battleSettings.party1Id"
              type="number"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              パーティ2 ID
            </label>
            <input
              v-model="battleSettings.party2Id"
              type="number"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
        
        <button
          @click="simulateBattle"
          :disabled="!battleSettings.party1Id || !battleSettings.party2Id"
          class="w-full bg-red-600 text-white py-3 px-6 rounded-md hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
        >
          対戦シミュレーション実行
        </button>
        
        <div v-if="battleResult" class="mt-4 p-4 bg-gray-50 rounded-lg">
          <h3 class="font-semibold mb-2">対戦結果</h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="font-medium">勝者:</span>
              <span class="text-green-600">パーティ{{ battleResult.winner }}</span>
            </div>
            <div>
              <span class="font-medium">ターン数:</span>
              <span>{{ battleResult.turns }}</span>
            </div>
            <div>
              <span class="font-medium">与えたダメージ:</span>
              <span>{{ battleResult.damage_dealt }}</span>
            </div>
            <div>
              <span class="font-medium">受けたダメージ:</span>
              <span>{{ battleResult.damage_taken }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AIOptimization',
  data() {
    return {
      isOptimizing: false,
      settings: {
        generations: 100,
        populationSize: 50,
        constraints: {
          type: ''
        }
      },
      progress: {
        currentGeneration: 0,
        totalGenerations: 100,
        bestFitness: 0,
        averageFitness: 0
      },
      optimizationResult: null,
      battleSettings: {
        party1Id: null,
        party2Id: null
      },
      battleResult: null,
      pokemonData: {},
      personalityData: {},
      characteristicData: {},
      goodsData: {},
      moveData: {}
    };
  },
  async mounted() {
    await this.loadMasterData();
  },
  methods: {
    async startOptimization() {
      this.isOptimizing = true;
      this.optimizationResult = null;
      
      try {
        const response = await axios.post('/api/ai-optimization/start', {
          generations: this.settings.generations,
          population_size: this.settings.populationSize,
          constraints: this.settings.constraints
        });
        
        if (response.data.success) {
          this.optimizationResult = response.data.result;
          this.progress.totalGenerations = this.settings.generations;
          this.progress.currentGeneration = this.settings.generations;
        } else {
          alert('最適化に失敗しました: ' + response.data.message);
        }
      } catch (error) {
        console.error('最適化エラー:', error);
        alert('最適化中にエラーが発生しました');
      } finally {
        this.isOptimizing = false;
      }
    },
    
    async saveOptimizedParty() {
      if (!this.optimizationResult) return;
      
      const partyName = prompt('パーティ名を入力してください:');
      if (!partyName) return;
      
      try {
        const response = await axios.post('/api/ai-optimization/save-party', {
          party_name: partyName,
          optimized_individual: this.optimizationResult.best_individual,
          user_id: 1 // 仮のユーザーID
        });
        
        if (response.data.success) {
          alert('パーティが保存されました！');
        } else {
          alert('保存に失敗しました: ' + response.data.message);
        }
      } catch (error) {
        console.error('保存エラー:', error);
        alert('保存中にエラーが発生しました');
      }
    },
    
    async simulateBattle() {
      try {
        const response = await axios.post('/api/ai-optimization/simulate-battle', {
          party1_id: this.battleSettings.party1Id,
          party2_id: this.battleSettings.party2Id
        });
        
        if (response.data.success) {
          this.battleResult = response.data.result;
        } else {
          alert('対戦シミュレーションに失敗しました: ' + response.data.message);
        }
      } catch (error) {
        console.error('対戦シミュレーションエラー:', error);
        alert('対戦シミュレーション中にエラーが発生しました');
      }
    },
    
    async loadMasterData() {
      // マスターデータを読み込み（実際の実装では適切なAPIエンドポイントから取得）
      // ここでは仮のデータを設定
      this.pokemonData = {
        1: 'フシギダネ',
        2: 'フシギソウ',
        3: 'フシギバナ',
        // 他のポケモンデータ...
      };
      
      this.personalityData = {
        1: 'がんばりや',
        2: 'さみしがり',
        3: 'ゆうかん',
        // 他の性格データ...
      };
      
      this.characteristicData = {
        1: 'がんじょう',
        2: 'すてみ',
        3: 'テクニシャン',
        // 他の特性データ...
      };
      
      this.goodsData = {
        1: 'こだわりハチマキ',
        2: 'こだわりメガネ',
        3: 'たつじんのおび',
        // 他の持ち物データ...
      };
      
      this.moveData = {
        1: 'たいあたり',
        2: 'ひっかく',
        3: 'はたく',
        // 他の技データ...
      };
    },
    
    getPokemonName(id) {
      return this.pokemonData[id] || `ポケモン${id}`;
    },
    
    getPersonalityName(id) {
      return this.personalityData[id] || `性格${id}`;
    },
    
    getCharacteristicName(id) {
      return this.characteristicData[id] || `特性${id}`;
    },
    
    getGoodsName(id) {
      return this.goodsData[id] || `持ち物${id}`;
    },
    
    getMoveName(id) {
      return this.moveData[id] || `技${id}`;
    }
  }
};
</script>

<style scoped>
.ai-optimization {
  min-height: 100vh;
  background-color: #f8fafc;
}

.container {
  max-width: 1200px;
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
