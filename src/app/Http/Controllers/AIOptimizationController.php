<?php

namespace App\Http\Controllers;

use App\Services\GeneticAlgorithmOptimizer;
use App\Services\AdaptiveGeneticOptimizer;
use App\Services\MachineLearningOptimizer;
use App\Services\BattleSimulationEngine;
use App\Models\MyParty;
use App\Models\MyPokemon;
use App\Models\Pokemon;
use App\Models\Personality;
use App\Models\Characteristic;
use App\Models\Goods;
use App\Models\Move;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AIOptimizationController extends Controller
{
    private $optimizer;
    private $battleEngine;

    public function __construct()
    {
        $this->battleEngine = new BattleSimulationEngine();
        $this->optimizer = new GeneticAlgorithmOptimizer($this->battleEngine);
    }

    /**
     * AI最適化を開始
     */
    public function startOptimization(Request $request): JsonResponse
    {
        $request->validate([
            'generations' => 'integer|min:10|max:1000',
            'population_size' => 'integer|min:20|max:200',
            'constraints' => 'array'
        ]);

        // パラメータを設定
        if ($request->has('generations')) {
            $this->optimizer->setGenerations($request->generations);
        }
        
        if ($request->has('population_size')) {
            $this->optimizer->setPopulationSize($request->population_size);
        }

        $constraints = $request->get('constraints', []);

        try {
            // 最適化を実行
            $result = $this->optimizer->optimizeParty($constraints);
            
            return response()->json([
                'success' => true,
                'result' => $result,
                'message' => '最適化が完了しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '最適化中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 最適化結果をパーティとして保存
     */
    public function saveOptimizedParty(Request $request): JsonResponse
    {
        $request->validate([
            'party_name' => 'required|string|max:255',
            'optimized_individual' => 'required|array',
            'user_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();

            // パーティを作成
            $party = MyParty::create([
                'name' => $request->party_name,
                'user_id' => $request->user_id
            ]);

            // 最適化された個体からポケモンを作成
            $individual = $request->optimized_individual;
            $pokemonIds = [];

            foreach ($individual as $index => $pokemonBuild) {
                if ($index >= 6) break; // 最大6匹まで

                $myPokemon = MyPokemon::create([
                    'name' => Pokemon::find($pokemonBuild['pokemon_id'])->name,
                    'user_id' => $request->user_id,
                    'pokemon_id' => $pokemonBuild['pokemon_id'],
                    'level' => 50, // デフォルトレベル
                    'personality_id' => $pokemonBuild['personality_id'],
                    'characteristics_id' => $pokemonBuild['characteristics_id'],
                    'goods_id' => $pokemonBuild['goods_id'],
                    'H_effort_values' => $pokemonBuild['effort_values']['H'],
                    'A_effort_values' => $pokemonBuild['effort_values']['A'],
                    'B_effort_values' => $pokemonBuild['effort_values']['B'],
                    'C_effort_values' => $pokemonBuild['effort_values']['C'],
                    'D_effort_values' => $pokemonBuild['effort_values']['D'],
                    'S_effort_values' => $pokemonBuild['effort_values']['S'],
                    'move1_id' => $pokemonBuild['moves'][0] ?? null,
                    'move2_id' => $pokemonBuild['moves'][1] ?? null,
                    'move3_id' => $pokemonBuild['moves'][2] ?? null,
                    'move4_id' => $pokemonBuild['moves'][3] ?? null,
                ]);

                // 実数値を計算して設定
                $this->calculateRealValues($myPokemon);
                $myPokemon->save();

                $pokemonIds[] = $myPokemon->id;
            }

            // パーティにポケモンを設定
            $party->update([
                'pokemon1_id' => $pokemonIds[0] ?? null,
                'pokemon2_id' => $pokemonIds[1] ?? null,
                'pokemon3_id' => $pokemonIds[2] ?? null,
                'pokemon4_id' => $pokemonIds[3] ?? null,
                'pokemon5_id' => $pokemonIds[4] ?? null,
                'pokemon6_id' => $pokemonIds[5] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'party_id' => $party->id,
                'message' => '最適化されたパーティが保存されました'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'パーティの保存中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 対戦シミュレーションを実行
     */
    public function simulateBattle(Request $request): JsonResponse
    {
        $request->validate([
            'party1_id' => 'required|integer',
            'party2_id' => 'required|integer'
        ]);

        try {
            $party1 = MyParty::findOrFail($request->party1_id);
            $party2 = MyParty::findOrFail($request->party2_id);

            $result = $this->battleEngine->simulateBattle($party1, $party2);

            return response()->json([
                'success' => true,
                'result' => $result,
                'message' => '対戦シミュレーションが完了しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '対戦シミュレーション中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * パーティの評価を取得
     */
    public function evaluateParty(Request $request): JsonResponse
    {
        $request->validate([
            'party_id' => 'required|integer'
        ]);

        try {
            $party = MyParty::findOrFail($request->party_id);
            $pokemon = $party->getAllPokemon();

            $evaluation = [
                'total_score' => 0,
                'pokemon_scores' => [],
                'synergy_bonus' => 0,
                'type_coverage' => [],
                'weaknesses' => [],
                'strengths' => []
            ];

            $types = [];
            $totalScore = 0;

            foreach ($pokemon as $poke) {
                $score = $this->calculatePokemonScore($poke);
                $evaluation['pokemon_scores'][] = [
                    'pokemon_id' => $poke->id,
                    'name' => $poke->name,
                    'score' => $score
                ];
                $totalScore += $score;

                // タイプを収集
                $types[] = $poke->pokemon->type1_id;
                if ($poke->pokemon->type2_id) {
                    $types[] = $poke->pokemon->type2_id;
                }
            }

            $evaluation['total_score'] = $totalScore;
            $evaluation['type_coverage'] = array_unique($types);
            $evaluation['synergy_bonus'] = count($evaluation['type_coverage']) * 0.05;

            return response()->json([
                'success' => true,
                'evaluation' => $evaluation,
                'message' => 'パーティの評価が完了しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'パーティの評価中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 最適化の進捗を取得
     */
    public function getOptimizationProgress(): JsonResponse
    {
        // 実装例：Redisやセッションを使用して進捗を管理
        return response()->json([
            'success' => true,
            'progress' => [
                'current_generation' => 0,
                'total_generations' => 100,
                'best_fitness' => 0,
                'average_fitness' => 0,
                'status' => 'idle'
            ]
        ]);
    }

    /**
     * ポケモンのスコアを計算
     */
    private function calculatePokemonScore(MyPokemon $pokemon): float
    {
        $score = 0;
        
        // 実数値による基本スコア
        $score += $pokemon->H_real_values * 0.5;
        $score += $pokemon->A_real_values * 1.0;
        $score += $pokemon->B_real_values * 1.0;
        $score += $pokemon->C_real_values * 1.0;
        $score += $pokemon->D_real_values * 1.0;
        $score += $pokemon->S_real_values * 0.8;
        
        return $score;
    }

    /**
     * 実数値を計算
     */
    private function calculateRealValues(MyPokemon $pokemon): void
    {
        $pokemonData = $pokemon->pokemon;
        $personality = $pokemon->personality;
        
        // 実数値 = (種族値 + 個体値/2 + 努力値/8) * 性格補正
        $stats = ['H', 'A', 'B', 'C', 'D', 'S'];
        
        foreach ($stats as $stat) {
            $baseValue = $pokemonData->$stat;
            $effortValue = $pokemon->{$stat . '_effort_values'};
            $individualValue = 31; // 仮で31（最良個体値）
            
            $realValue = floor(($baseValue + $individualValue / 2 + $effortValue / 8) + 5);
            
            // 性格補正を適用
            if ($personality->rise == $stat) {
                $realValue = floor($realValue * 1.1);
            } elseif ($personality->descent == $stat) {
                $realValue = floor($realValue * 0.9);
            }
            
            $pokemon->{$stat . '_real_values'} = $realValue;
        }
    }
}
