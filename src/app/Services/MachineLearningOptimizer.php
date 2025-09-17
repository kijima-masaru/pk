<?php

namespace App\Services;

use App\Models\MyParty;
use App\Models\MyPokemon;
use App\Models\Pokemon;
use App\Models\Personality;
use App\Models\Characteristic;
use App\Models\Goods;
use App\Models\Move;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MachineLearningOptimizer
{
    private $battleEngine;
    private $learningData = [];
    private $modelWeights = [];
    private $experienceBuffer = [];
    private $maxBufferSize = 10000;

    public function __construct(BattleSimulationEngine $battleEngine)
    {
        $this->battleEngine = $battleEngine;
        $this->loadLearningData();
    }

    /**
     * 学習データを読み込み
     */
    private function loadLearningData(): void
    {
        // キャッシュから学習データを読み込み
        $this->learningData = Cache::get('pokemon_learning_data', []);
        $this->modelWeights = Cache::get('pokemon_model_weights', $this->initializeWeights());
        $this->experienceBuffer = Cache::get('pokemon_experience_buffer', []);
    }

    /**
     * 学習データを保存
     */
    private function saveLearningData(): void
    {
        Cache::put('pokemon_learning_data', $this->learningData, 86400 * 30); // 30日間保存
        Cache::put('pokemon_model_weights', $this->modelWeights, 86400 * 30);
        Cache::put('pokemon_experience_buffer', $this->experienceBuffer, 86400 * 30);
    }

    /**
     * モデルの重みを初期化
     */
    private function initializeWeights(): array
    {
        return [
            'pokemon_synergy' => 0.1,
            'type_coverage' => 0.15,
            'stat_balance' => 0.2,
            'move_coverage' => 0.15,
            'ability_synergy' => 0.1,
            'item_synergy' => 0.1,
            'meta_adaptation' => 0.2
        ];
    }

    /**
     * 対戦結果を学習データとして蓄積
     */
    public function recordBattleResult(array $party1, array $party2, array $result): void
    {
        $experience = [
            'timestamp' => now()->timestamp,
            'party1' => $this->extractFeatures($party1),
            'party2' => $this->extractFeatures($party2),
            'result' => $result,
            'winner' => $result['winner']
        ];

        // 経験バッファに追加
        $this->experienceBuffer[] = $experience;

        // バッファサイズを制限
        if (count($this->experienceBuffer) > $this->maxBufferSize) {
            array_shift($this->experienceBuffer);
        }

        // 定期的にモデルを更新
        if (count($this->experienceBuffer) % 100 == 0) {
            $this->updateModel();
        }

        $this->saveLearningData();
    }

    /**
     * パーティから特徴量を抽出
     */
    private function extractFeatures(array $party): array
    {
        $features = [
            'pokemon_count' => count($party),
            'type_diversity' => 0,
            'stat_total' => 0,
            'move_diversity' => 0,
            'ability_diversity' => 0,
            'item_diversity' => 0,
            'synergy_score' => 0
        ];

        $types = [];
        $abilities = [];
        $items = [];
        $moves = [];

        foreach ($party as $pokemon) {
            if (!$pokemon) continue;

            $pokemonData = Pokemon::find($pokemon['pokemon_id']);
            if (!$pokemonData) continue;

            // タイプを収集
            $types[] = $pokemonData->type1_id;
            if ($pokemonData->type2_id) {
                $types[] = $pokemonData->type2_id;
            }

            // 特性を収集
            $abilities[] = $pokemon['characteristics_id'];

            // 持ち物を収集
            $items[] = $pokemon['goods_id'];

            // 技を収集
            foreach ($pokemon['moves'] as $moveId) {
                if ($moveId) {
                    $moves[] = $moveId;
                }
            }

            // ステータス合計
            $features['stat_total'] += $pokemonData->H + $pokemonData->A + $pokemonData->B + 
                                     $pokemonData->C + $pokemonData->D + $pokemonData->S;
        }

        $features['type_diversity'] = count(array_unique($types));
        $features['ability_diversity'] = count(array_unique($abilities));
        $features['item_diversity'] = count(array_unique($items));
        $features['move_diversity'] = count(array_unique($moves));

        // 相性スコアを計算
        $features['synergy_score'] = $this->calculateSynergyScore($types, $abilities, $items);

        return $features;
    }

    /**
     * 相性スコアを計算
     */
    private function calculateSynergyScore(array $types, array $abilities, array $items): float
    {
        $score = 0;

        // タイプ相性ボーナス
        $score += count(array_unique($types)) * 0.1;

        // 特性の相性（簡易実装）
        $abilityCounts = array_count_values($abilities);
        foreach ($abilityCounts as $count) {
            if ($count > 1) {
                $score += 0.05; // 同じ特性の重複ボーナス
            }
        }

        // 持ち物の相性（簡易実装）
        $itemCounts = array_count_values($items);
        foreach ($itemCounts as $count) {
            if ($count > 1) {
                $score += 0.03; // 同じ持ち物の重複ペナルティ
            }
        }

        return $score;
    }

    /**
     * モデルを更新（簡易的な強化学習）
     */
    private function updateModel(): void
    {
        if (count($this->experienceBuffer) < 100) {
            return; // 十分なデータがない場合は更新しない
        }

        $learningRate = 0.01;
        $recentExperiences = array_slice($this->experienceBuffer, -500); // 最近の500件

        foreach ($recentExperiences as $experience) {
            $party1Features = $experience['party1'];
            $party2Features = $experience['party2'];
            $winner = $experience['winner'];

            // 勝者の特徴を強化
            if ($winner == 1) {
                $this->updateWeights($party1Features, 1.0, $learningRate);
                $this->updateWeights($party2Features, -0.5, $learningRate);
            } else {
                $this->updateWeights($party1Features, -0.5, $learningRate);
                $this->updateWeights($party2Features, 1.0, $learningRate);
            }
        }

        $this->saveLearningData();
    }

    /**
     * 重みを更新
     */
    private function updateWeights(array $features, float $reward, float $learningRate): void
    {
        // 簡易的な重み更新
        $this->modelWeights['type_coverage'] += $features['type_diversity'] * $reward * $learningRate;
        $this->modelWeights['stat_balance'] += ($features['stat_total'] / 1000) * $reward * $learningRate;
        $this->modelWeights['move_coverage'] += $features['move_diversity'] * $reward * $learningRate;
        $this->modelWeights['ability_synergy'] += $features['ability_diversity'] * $reward * $learningRate;
        $this->modelWeights['item_synergy'] += $features['item_diversity'] * $reward * $learningRate;
        $this->modelWeights['pokemon_synergy'] += $features['synergy_score'] * $reward * $learningRate;

        // 重みを正規化
        $this->normalizeWeights();
    }

    /**
     * 重みを正規化
     */
    private function normalizeWeights(): void
    {
        $total = array_sum($this->modelWeights);
        if ($total > 0) {
            foreach ($this->modelWeights as $key => $weight) {
                $this->modelWeights[$key] = $weight / $total;
            }
        }
    }

    /**
     * 学習済みモデルを使用してパーティを評価
     */
    public function evaluatePartyWithLearning(array $party): float
    {
        $features = $this->extractFeatures($party);
        
        $score = 0;
        $score += $features['type_diversity'] * $this->modelWeights['type_coverage'];
        $score += ($features['stat_total'] / 1000) * $this->modelWeights['stat_balance'];
        $score += $features['move_diversity'] * $this->modelWeights['move_coverage'];
        $score += $features['ability_diversity'] * $this->modelWeights['ability_synergy'];
        $score += $features['item_diversity'] * $this->modelWeights['item_synergy'];
        $score += $features['synergy_score'] * $this->modelWeights['pokemon_synergy'];

        return $score;
    }

    /**
     * 学習データの統計を取得
     */
    public function getLearningStats(): array
    {
        return [
            'total_battles' => count($this->experienceBuffer),
            'model_weights' => $this->modelWeights,
            'recent_win_rate' => $this->calculateRecentWinRate(),
            'learning_progress' => $this->calculateLearningProgress()
        ];
    }

    /**
     * 最近の勝率を計算
     */
    private function calculateRecentWinRate(): float
    {
        $recentBattles = array_slice($this->experienceBuffer, -100);
        if (empty($recentBattles)) {
            return 0.5;
        }

        $wins = 0;
        foreach ($recentBattles as $battle) {
            if ($battle['winner'] == 1) {
                $wins++;
            }
        }

        return $wins / count($recentBattles);
    }

    /**
     * 学習進捗を計算
     */
    private function calculateLearningProgress(): float
    {
        $totalBattles = count($this->experienceBuffer);
        $maxBattles = 10000;
        
        return min($totalBattles / $maxBattles, 1.0);
    }

    /**
     * 学習データをリセット
     */
    public function resetLearning(): void
    {
        $this->learningData = [];
        $this->modelWeights = $this->initializeWeights();
        $this->experienceBuffer = [];
        $this->saveLearningData();
    }

    /**
     * 学習データをエクスポート
     */
    public function exportLearningData(): array
    {
        return [
            'learning_data' => $this->learningData,
            'model_weights' => $this->modelWeights,
            'experience_buffer' => $this->experienceBuffer,
            'exported_at' => now()->toISOString()
        ];
    }

    /**
     * 学習データをインポート
     */
    public function importLearningData(array $data): void
    {
        $this->learningData = $data['learning_data'] ?? [];
        $this->modelWeights = $data['model_weights'] ?? $this->initializeWeights();
        $this->experienceBuffer = $data['experience_buffer'] ?? [];
        $this->saveLearningData();
    }
}
