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

class GeneticAlgorithmOptimizer
{
    private $battleEngine;
    private $populationSize = 50;
    private $generations = 100;
    private $mutationRate = 0.1;
    private $crossoverRate = 0.8;
    private $eliteSize = 10;

    public function __construct(BattleSimulationEngine $battleEngine)
    {
        $this->battleEngine = $battleEngine;
    }

    /**
     * 世代数を設定
     */
    public function setGenerations(int $generations): void
    {
        $this->generations = $generations;
    }

    /**
     * 個体数を設定
     */
    public function setPopulationSize(int $populationSize): void
    {
        $this->populationSize = $populationSize;
    }

    /**
     * 突然変異率を設定
     */
    public function setMutationRate(float $mutationRate): void
    {
        $this->mutationRate = $mutationRate;
    }

    /**
     * 交叉率を設定
     */
    public function setCrossoverRate(float $crossoverRate): void
    {
        $this->crossoverRate = $crossoverRate;
    }

    /**
     * 遺伝的アルゴリズムによる最適化実行
     */
    public function optimizeParty(array $constraints = []): array
    {
        // 初期集団を生成
        $population = $this->generateInitialPopulation();
        
        $bestFitness = 0;
        $bestIndividual = null;
        $fitnessHistory = [];

        for ($generation = 0; $generation < $this->generations; $generation++) {
            // 適応度を評価
            $fitnessScores = $this->evaluatePopulation($population);
            
            // 最良個体を記録
            $maxFitness = max($fitnessScores);
            $maxIndex = array_search($maxFitness, $fitnessScores);
            
            if ($maxFitness > $bestFitness) {
                $bestFitness = $maxFitness;
                $bestIndividual = $population[$maxIndex];
            }
            
            $fitnessHistory[] = $maxFitness;
            
            // 新しい世代を生成
            $population = $this->evolvePopulation($population, $fitnessScores);
            
            // 進捗を出力
            if ($generation % 10 == 0) {
                echo "Generation {$generation}: Best Fitness = {$maxFitness}\n";
            }
        }

        return [
            'best_individual' => $bestIndividual,
            'best_fitness' => $bestFitness,
            'fitness_history' => $fitnessHistory,
            'generation_count' => $this->generations
        ];
    }

    /**
     * 初期集団を生成
     */
    private function generateInitialPopulation(): array
    {
        $population = [];
        
        for ($i = 0; $i < $this->populationSize; $i++) {
            $population[] = $this->generateRandomParty();
        }
        
        return $population;
    }

    /**
     * ランダムなパーティを生成
     */
    private function generateRandomParty(): array
    {
        $party = [];
        $usedPokemon = [];
        
        // 6匹のポケモンを選択
        for ($i = 0; $i < 6; $i++) {
            $pokemon = $this->getRandomPokemon($usedPokemon);
            if ($pokemon) {
                $usedPokemon[] = $pokemon->id;
                $party[] = $this->generateRandomPokemonBuild($pokemon);
            }
        }
        
        return $party;
    }

    /**
     * ランダムなポケモンを取得
     */
    private function getRandomPokemon(array $excludeIds = []): ?Pokemon
    {
        $query = Pokemon::inRandomOrder();
        
        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }
        
        return $query->first();
    }

    /**
     * ランダムなポケモンビルドを生成
     */
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

    /**
     * ランダムな性格を取得
     */
    private function getRandomPersonality(): int
    {
        return Personality::inRandomOrder()->first()->id;
    }

    /**
     * ランダムな特性を取得（ポケモンが持つ特性から）
     */
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

    /**
     * ランダムな持ち物を取得
     */
    private function getRandomGoods(): int
    {
        return Goods::inRandomOrder()->first()->id;
    }

    /**
     * ランダムな努力値を生成（合計508まで）
     */
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

    /**
     * ランダムな技を取得
     */
    private function getRandomMoves(Pokemon $pokemon): array
    {
        // 簡易的にランダムな技を4つ選択
        $moves = Move::inRandomOrder()->limit(4)->pluck('id')->toArray();
        return array_pad($moves, 4, null);
    }

    /**
     * 集団の適応度を評価
     */
    private function evaluatePopulation(array $population): array
    {
        $fitnessScores = [];
        
        foreach ($population as $individual) {
            $fitnessScores[] = $this->evaluateIndividual($individual);
        }
        
        return $fitnessScores;
    }

    /**
     * 個体の適応度を評価
     */
    private function evaluateIndividual(array $individual): float
    {
        $totalFitness = 0;
        $battleCount = 20; // 各個体を20回対戦させる
        
        for ($i = 0; $i < $battleCount; $i++) {
            // ランダムな相手パーティを生成
            $opponent = $this->generateRandomParty();
            
            // 対戦シミュレーション
            $result = $this->simulateBattleBetweenIndividuals($individual, $opponent);
            
            if ($result['winner'] == 1) {
                $totalFitness += 1.0;
            } elseif ($result['winner'] == 2) {
                $totalFitness += 0.0;
            } else {
                $totalFitness += 0.5; // 引き分け
            }
            
            // 追加の評価要素
            $totalFitness += $result['damage_dealt'] * 0.001;
            $totalFitness += $result['effectiveness_used'] * 0.01;
        }
        
        return $totalFitness / $battleCount;
    }

    /**
     * 2つの個体間の対戦をシミュレート
     */
    private function simulateBattleBetweenIndividuals(array $individual1, array $individual2): array
    {
        // 簡易的な対戦シミュレーション
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

    /**
     * 個体のスコアを計算
     */
    private function calculateIndividualScore(array $individual): float
    {
        $totalScore = 0;
        
        foreach ($individual as $pokemon) {
            $totalScore += $this->calculatePokemonBuildScore($pokemon);
        }
        
        // パーティの相性ボーナス
        $synergyBonus = $this->calculatePartySynergy($individual);
        
        return $totalScore * (1 + $synergyBonus);
    }

    /**
     * ポケモンビルドのスコアを計算
     */
    private function calculatePokemonBuildScore(array $pokemonBuild): float
    {
        $pokemon = Pokemon::find($pokemonBuild['pokemon_id']);
        if (!$pokemon) return 0;
        
        $score = 0;
        
        // 種族値による基本スコア
        $score += $pokemon->H * 0.5;
        $score += $pokemon->A * 1.0;
        $score += $pokemon->B * 1.0;
        $score += $pokemon->C * 1.0;
        $score += $pokemon->D * 1.0;
        $score += $pokemon->S * 0.8;
        
        // 努力値によるボーナス
        $effortValues = $pokemonBuild['effort_values'];
        $score += $effortValues['H'] * 0.1;
        $score += $effortValues['A'] * 0.2;
        $score += $effortValues['B'] * 0.2;
        $score += $effortValues['C'] * 0.2;
        $score += $effortValues['D'] * 0.2;
        $score += $effortValues['S'] * 0.15;
        
        return $score;
    }

    /**
     * パーティの相性を計算
     */
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
        return $uniqueTypes * 0.05; // タイプ多様性ボーナス
    }

    /**
     * 集団を進化させる
     */
    private function evolvePopulation(array $population, array $fitnessScores): array
    {
        $newPopulation = [];
        
        // エリート選択（最良の個体を保持）
        $eliteIndices = $this->selectElite($fitnessScores);
        foreach ($eliteIndices as $index) {
            $newPopulation[] = $population[$index];
        }
        
        // 残りの個体を生成
        while (count($newPopulation) < $this->populationSize) {
            // 親を選択
            $parent1 = $this->selectParent($population, $fitnessScores);
            $parent2 = $this->selectParent($population, $fitnessScores);
            
            // 交叉
            if (mt_rand() / mt_getrandmax() < $this->crossoverRate) {
                $offspring = $this->crossover($parent1, $parent2);
            } else {
                $offspring = $parent1; // 親をそのままコピー
            }
            
            // 突然変異
            if (mt_rand() / mt_getrandmax() < $this->mutationRate) {
                $offspring = $this->mutate($offspring);
            }
            
            $newPopulation[] = $offspring;
        }
        
        return $newPopulation;
    }

    /**
     * エリート個体を選択
     */
    private function selectElite(array $fitnessScores): array
    {
        $indices = array_keys($fitnessScores);
        array_multisort($fitnessScores, SORT_DESC, $indices);
        
        return array_slice($indices, 0, $this->eliteSize);
    }

    /**
     * 親を選択（トーナメント選択）
     */
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

    /**
     * 交叉（一様交叉）
     */
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
        
        return array_filter($offspring); // nullを除去
    }

    /**
     * 突然変異
     */
    private function mutate(array $individual): array
    {
        $mutated = $individual;
        
        // ランダムに1つのポケモンを変更
        if (!empty($mutated)) {
            $index = array_rand($mutated);
            $pokemon = $mutated[$index];
            
            // ランダムに1つの要素を変更
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
}
