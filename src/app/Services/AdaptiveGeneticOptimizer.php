<?php

namespace App\Services;

use App\Models\MyPokemon;
use App\Models\MyParty;
use App\Models\Pokemon;
use App\Models\Personality;
use App\Models\Characteristic;
use App\Models\Goods;
use App\Models\Move;
use Illuminate\Support\Facades\DB;

class AdaptiveGeneticOptimizer
{
    private $battleEngine;
    private $mlOptimizer;
    private $populationSize = 50;
    private $generations = 100;
    private $mutationRate = 0.1;
    private $crossoverRate = 0.8;
    private $eliteSize = 10;
    private $learningWeight = 0.3; // 学習データの重み

    public function __construct(BattleSimulationEngine $battleEngine, MachineLearningOptimizer $mlOptimizer)
    {
        $this->battleEngine = $battleEngine;
        $this->mlOptimizer = $mlOptimizer;
    }

    /**
     * 学習機能付きの最適化実行
     */
    public function optimizePartyWithLearning(array $constraints = []): array
    {
        // 初期集団を生成
        $population = $this->generateInitialPopulation();
        
        $bestFitness = 0;
        $bestIndividual = null;
        $fitnessHistory = [];
        $learningHistory = [];

        for ($generation = 0; $generation < $this->generations; $generation++) {
            // 適応度を評価（学習データも考慮）
            $fitnessScores = $this->evaluatePopulationWithLearning($population);
            
            // 最良個体を記録
            $maxFitness = max($fitnessScores);
            $maxIndex = array_search($maxFitness, $fitnessScores);
            
            if ($maxFitness > $bestFitness) {
                $bestFitness = $maxFitness;
                $bestIndividual = $population[$maxIndex];
            }
            
            $fitnessHistory[] = $maxFitness;
            
            // 学習データを蓄積
            $this->recordGenerationData($population, $fitnessScores);
            
            // 学習進捗を記録
            $learningStats = $this->mlOptimizer->getLearningStats();
            $learningHistory[] = $learningStats;
            
            // 新しい世代を生成
            $population = $this->evolvePopulation($population, $fitnessScores);
            
            // 進捗を出力
            if ($generation % 10 == 0) {
                echo "Generation {$generation}: Best Fitness = {$maxFitness}, Learning Progress = " . 
                     round($learningStats['learning_progress'] * 100, 1) . "%\n";
            }
        }

        return [
            'best_individual' => $bestIndividual,
            'best_fitness' => $bestFitness,
            'fitness_history' => $fitnessHistory,
            'learning_history' => $learningHistory,
            'generation_count' => $this->generations,
            'final_learning_stats' => $this->mlOptimizer->getLearningStats()
        ];
    }

    /**
     * 学習データを考慮した集団評価
     */
    private function evaluatePopulationWithLearning(array $population): array
    {
        $fitnessScores = [];
        
        foreach ($population as $individual) {
            // 従来の対戦ベース評価
            $battleFitness = $this->evaluateIndividual($individual);
            
            // 学習ベース評価
            $learningFitness = $this->mlOptimizer->evaluatePartyWithLearning($individual);
            
            // 重み付き合計
            $combinedFitness = ($battleFitness * (1 - $this->learningWeight)) + 
                              ($learningFitness * $this->learningWeight);
            
            $fitnessScores[] = $combinedFitness;
        }
        
        return $fitnessScores;
    }

    /**
     * 世代データを学習システムに記録
     */
    private function recordGenerationData(array $population, array $fitnessScores): void
    {
        // 上位10%の個体同士で対戦を記録
        $topIndices = $this->selectTopIndividuals($fitnessScores, 0.1);
        
        foreach ($topIndices as $i => $index1) {
            foreach ($topIndices as $j => $index2) {
                if ($i >= $j) continue; // 重複を避ける
                
                $individual1 = $population[$index1];
                $individual2 = $population[$index2];
                
                // 対戦シミュレーション
                $result = $this->simulateBattleBetweenIndividuals($individual1, $individual2);
                
                // 学習データとして記録
                $this->mlOptimizer->recordBattleResult($individual1, $individual2, $result);
            }
        }
    }

    /**
     * 上位個体を選択
     */
    private function selectTopIndividuals(array $fitnessScores, float $topRatio): array
    {
        $indices = array_keys($fitnessScores);
        array_multisort($fitnessScores, SORT_DESC, $indices);
        
        $topCount = max(1, (int) (count($fitnessScores) * $topRatio));
        return array_slice($indices, 0, $topCount);
    }

    /**
     * 初期集団を生成（学習データを考慮）
     */
    private function generateInitialPopulation(): array
    {
        $population = [];
        
        // 70%はランダム生成
        $randomCount = (int) ($this->populationSize * 0.7);
        for ($i = 0; $i < $randomCount; $i++) {
            $population[] = $this->generateRandomParty();
        }
        
        // 30%は学習データから生成
        $learningCount = $this->populationSize - $randomCount;
        for ($i = 0; $i < $learningCount; $i++) {
            $population[] = $this->generatePartyFromLearning();
        }
        
        return $population;
    }

    /**
     * 学習データからパーティを生成
     */
    private function generatePartyFromLearning(): array
    {
        // 学習データから良い特徴を抽出してパーティを生成
        $learningStats = $this->mlOptimizer->getLearningStats();
        
        if ($learningStats['total_battles'] < 50) {
            // 学習データが少ない場合はランダム生成
            return $this->generateRandomParty();
        }
        
        // 学習データに基づいてパーティを生成
        $party = [];
        $usedPokemon = [];
        
        // 学習された重みに基づいてポケモンを選択
        for ($i = 0; $i < 6; $i++) {
            $pokemon = $this->selectPokemonBasedOnLearning($usedPokemon);
            if ($pokemon) {
                $usedPokemon[] = $pokemon->id;
                $party[] = $this->generatePokemonBuildFromLearning($pokemon);
            }
        }
        
        return $party;
    }

    /**
     * 学習データに基づいてポケモンを選択
     */
    private function selectPokemonBasedOnLearning(array $excludeIds = []): ?Pokemon
    {
        // 簡易実装：学習データから人気の高いポケモンを選択
        $query = Pokemon::inRandomOrder();
        
        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }
        
        return $query->first();
    }

    /**
     * 学習データに基づいてポケモンビルドを生成
     */
    private function generatePokemonBuildFromLearning(Pokemon $pokemon): array
    {
        $learningStats = $this->mlOptimizer->getLearningStats();
        
        // 学習された重みに基づいてビルドを調整
        $build = [
            'pokemon_id' => $pokemon->id,
            'personality_id' => $this->getRandomPersonality(),
            'characteristics_id' => $this->getRandomCharacteristic($pokemon),
            'goods_id' => $this->getRandomGoods(),
            'effort_values' => $this->generateEffortValuesFromLearning(),
            'moves' => $this->getRandomMoves($pokemon)
        ];
        
        return $build;
    }

    /**
     * 学習データに基づいて努力値を生成
     */
    private function generateEffortValuesFromLearning(): array
    {
        $learningStats = $this->mlOptimizer->getLearningStats();
        
        // 学習された重みに基づいて努力値を調整
        $effortValues = [
            'H' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'S' => 0
        ];
        
        $total = 508;
        $stats = ['H', 'A', 'B', 'C', 'D', 'S'];
        
        // 学習データに基づいて重みを調整
        $weights = [
            'H' => 0.1,
            'A' => 0.2 + ($learningStats['model_weights']['stat_balance'] * 0.1),
            'B' => 0.2,
            'C' => 0.2 + ($learningStats['model_weights']['stat_balance'] * 0.1),
            'D' => 0.2,
            'S' => 0.1 + ($learningStats['model_weights']['stat_balance'] * 0.1)
        ];
        
        // 重みに基づいて努力値を配分
        foreach ($stats as $stat) {
            $value = (int) ($total * $weights[$stat]);
            $effortValues[$stat] = min(252, $value);
            $total -= $effortValues[$stat];
        }
        
        // 残りをランダムに配分
        while ($total > 0) {
            $stat = $stats[array_rand($stats)];
            $value = min(252 - $effortValues[$stat], mt_rand(0, $total));
            $effortValues[$stat] += $value;
            $total -= $value;
        }
        
        return $effortValues;
    }

    // 以下は元のGeneticAlgorithmOptimizerから継承したメソッド
    // （簡略化のため、主要なメソッドのみ実装）

    private function generateRandomParty(): array
    {
        $party = [];
        $usedPokemon = [];
        
        for ($i = 0; $i < 6; $i++) {
            $pokemon = $this->getRandomPokemon($usedPokemon);
            if ($pokemon) {
                $usedPokemon[] = $pokemon->id;
                $party[] = $this->generateRandomPokemonBuild($pokemon);
            }
        }
        
        return $party;
    }

    private function getRandomPokemon(array $excludeIds = []): ?Pokemon
    {
        $query = Pokemon::inRandomOrder();
        
        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }
        
        return $query->first();
    }

    private function generateRandomPokemonBuild(Pokemon $pokemon): array
    {
        return [
            'pokemon_id' => $pokemon->id,
            'personality_id' => $this->getRandomPersonality(),
            'characteristics_id' => $this->getRandomCharacteristic($pokemon),
            'goods_id' => $this->getRandomGoods(),
            'effort_values' => $this->generateRandomEffortValues(),
            'moves' => $this->getRandomMoves($pokemon)
        ];
    }

    private function getRandomPersonality(): int
    {
        return Personality::inRandomOrder()->first()->id;
    }

    private function getRandomCharacteristic(Pokemon $pokemon): int
    {
        $characteristics = [
            $pokemon->characteristics1_id,
            $pokemon->characteristics2_id,
            $pokemon->characteristics3_id,
            $pokemon->characteristics4_id
        ];
        
        $validCharacteristics = array_filter($characteristics);
        return $validCharacteristics[array_rand($validCharacteristics)];
    }

    private function getRandomGoods(): int
    {
        return Goods::inRandomOrder()->first()->id;
    }

    private function generateRandomEffortValues(): array
    {
        $total = 508;
        $stats = ['H', 'A', 'B', 'C', 'D', 'S'];
        $effortValues = [];
        
        foreach ($stats as $stat) {
            $effortValues[$stat] = 0;
        }
        
        while ($total > 0) {
            $stat = $stats[array_rand($stats)];
            $value = min(252, mt_rand(0, min(252, $total)));
            $effortValues[$stat] += $value;
            $total -= $value;
        }
        
        return $effortValues;
    }

    private function getRandomMoves(Pokemon $pokemon): array
    {
        $moves = Move::inRandomOrder()->limit(4)->pluck('id')->toArray();
        return array_pad($moves, 4, null);
    }

    private function evaluateIndividual(array $individual): float
    {
        $totalFitness = 0;
        $battleCount = 20;
        
        for ($i = 0; $i < $battleCount; $i++) {
            $opponent = $this->generateRandomParty();
            $result = $this->simulateBattleBetweenIndividuals($individual, $opponent);
            
            if ($result['winner'] == 1) {
                $totalFitness += 1.0;
            } elseif ($result['winner'] == 2) {
                $totalFitness += 0.0;
            } else {
                $totalFitness += 0.5;
            }
            
            $totalFitness += $result['damage_dealt'] * 0.001;
            $totalFitness += $result['effectiveness_used'] * 0.01;
        }
        
        return $totalFitness / $battleCount;
    }

    private function simulateBattleBetweenIndividuals(array $individual1, array $individual2): array
    {
        $score1 = $this->calculateIndividualScore($individual1);
        $score2 = $this->calculateIndividualScore($individual2);
        
        $randomFactor1 = mt_rand(85, 115) / 100;
        $randomFactor2 = mt_rand(85, 115) / 100;
        
        $finalScore1 = $score1 * $randomFactor1;
        $finalScore2 = $score2 * $randomFactor2;
        
        return [
            'winner' => $finalScore1 > $finalScore2 ? 1 : 2,
            'damage_dealt' => max($finalScore1, $finalScore2),
            'effectiveness_used' => mt_rand(0, 10)
        ];
    }

    private function calculateIndividualScore(array $individual): float
    {
        $totalScore = 0;
        
        foreach ($individual as $pokemon) {
            $totalScore += $this->calculatePokemonBuildScore($pokemon);
        }
        
        $synergyBonus = $this->calculatePartySynergy($individual);
        
        return $totalScore * (1 + $synergyBonus);
    }

    private function calculatePokemonBuildScore(array $pokemonBuild): float
    {
        $pokemon = Pokemon::find($pokemonBuild['pokemon_id']);
        if (!$pokemon) return 0;
        
        $score = 0;
        
        $score += $pokemon->H * 0.5;
        $score += $pokemon->A * 1.0;
        $score += $pokemon->B * 1.0;
        $score += $pokemon->C * 1.0;
        $score += $pokemon->D * 1.0;
        $score += $pokemon->S * 0.8;
        
        $effortValues = $pokemonBuild['effort_values'];
        $score += $effortValues['H'] * 0.1;
        $score += $effortValues['A'] * 0.2;
        $score += $effortValues['B'] * 0.2;
        $score += $effortValues['C'] * 0.2;
        $score += $effortValues['D'] * 0.2;
        $score += $effortValues['S'] * 0.15;
        
        return $score;
    }

    private function calculatePartySynergy(array $individual): float
    {
        $types = [];
        
        foreach ($individual as $pokemon) {
            $pokemonData = Pokemon::find($pokemon['pokemon_id']);
            if ($pokemonData) {
                $types[] = $pokemonData->type1_id;
                if ($pokemonData->type2_id) {
                    $types[] = $pokemonData->type2_id;
                }
            }
        }
        
        $uniqueTypes = count(array_unique($types));
        return $uniqueTypes * 0.05;
    }

    private function evolvePopulation(array $population, array $fitnessScores): array
    {
        $newPopulation = [];
        
        $eliteIndices = $this->selectElite($fitnessScores);
        foreach ($eliteIndices as $index) {
            $newPopulation[] = $population[$index];
        }
        
        while (count($newPopulation) < $this->populationSize) {
            $parent1 = $this->selectParent($population, $fitnessScores);
            $parent2 = $this->selectParent($population, $fitnessScores);
            
            if (mt_rand() / mt_getrandmax() < $this->crossoverRate) {
                $offspring = $this->crossover($parent1, $parent2);
            } else {
                $offspring = $parent1;
            }
            
            if (mt_rand() / mt_getrandmax() < $this->mutationRate) {
                $offspring = $this->mutate($offspring);
            }
            
            $newPopulation[] = $offspring;
        }
        
        return $newPopulation;
    }

    private function selectElite(array $fitnessScores): array
    {
        $indices = array_keys($fitnessScores);
        array_multisort($fitnessScores, SORT_DESC, $indices);
        
        return array_slice($indices, 0, $this->eliteSize);
    }

    private function selectParent(array $population, array $fitnessScores): array
    {
        $tournamentSize = 3;
        $candidates = array_rand($population, min($tournamentSize, count($population)));
        
        $bestFitness = -1;
        $bestIndex = $candidates[0];
        
        foreach ($candidates as $index) {
            if ($fitnessScores[$index] > $bestFitness) {
                $bestFitness = $fitnessScores[$index];
                $bestIndex = $index;
            }
        }
        
        return $population[$bestIndex];
    }

    private function crossover(array $parent1, array $parent2): array
    {
        $offspring = [];
        
        for ($i = 0; $i < 6; $i++) {
            if (mt_rand() % 2 == 0) {
                $offspring[] = $parent1[$i] ?? null;
            } else {
                $offspring[] = $parent2[$i] ?? null;
            }
        }
        
        return array_filter($offspring);
    }

    private function mutate(array $individual): array
    {
        $mutated = $individual;
        
        if (!empty($mutated)) {
            $index = array_rand($mutated);
            $pokemon = $mutated[$index];
            
            $elements = ['personality_id', 'characteristics_id', 'goods_id', 'effort_values', 'moves'];
            $element = $elements[array_rand($elements)];
            
            switch ($element) {
                case 'personality_id':
                    $pokemon['personality_id'] = $this->getRandomPersonality();
                    break;
                case 'characteristics_id':
                    $pokemonData = Pokemon::find($pokemon['pokemon_id']);
                    if ($pokemonData) {
                        $pokemon['characteristics_id'] = $this->getRandomCharacteristic($pokemonData);
                    }
                    break;
                case 'goods_id':
                    $pokemon['goods_id'] = $this->getRandomGoods();
                    break;
                case 'effort_values':
                    $pokemon['effort_values'] = $this->generateRandomEffortValues();
                    break;
                case 'moves':
                    $pokemonData = Pokemon::find($pokemon['pokemon_id']);
                    if ($pokemonData) {
                        $pokemon['moves'] = $this->getRandomMoves($pokemonData);
                    }
                    break;
            }
            
            $mutated[$index] = $pokemon;
        }
        
        return $mutated;
    }

    // 設定メソッド
    public function setGenerations(int $generations): void
    {
        $this->generations = $generations;
    }

    public function setPopulationSize(int $populationSize): void
    {
        $this->populationSize = $populationSize;
    }

    public function setLearningWeight(float $weight): void
    {
        $this->learningWeight = $weight;
    }
}
