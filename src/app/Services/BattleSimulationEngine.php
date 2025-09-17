<?php

namespace App\Services;

use App\Models\MyPokemon;
use App\Models\MyParty;
use App\Models\Move;
use App\Models\Type;

class BattleSimulationEngine
{
    private $typeEffectiveness = [
        // ノーマル
        1 => [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 1, 13 => 1, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // ほのお
        2 => [1 => 1, 2 => 0.5, 3 => 0.5, 4 => 1, 5 => 1, 6 => 2, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 2, 13 => 0.5, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // みず
        3 => [1 => 1, 2 => 2, 3 => 0.5, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 0.5, 13 => 1, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // でんき
        4 => [1 => 1, 2 => 1, 3 => 2, 4 => 0.5, 5 => 1, 6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 1, 13 => 1, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // くさ
        5 => [1 => 1, 2 => 0.5, 3 => 2, 4 => 0.5, 5 => 0.5, 6 => 0.5, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 0.5, 13 => 2, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // こおり
        6 => [1 => 1, 2 => 0.5, 3 => 0.5, 4 => 1, 5 => 2, 6 => 0.5, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 2, 13 => 1, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // かくとう
        7 => [1 => 2, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 2, 7 => 1, 8 => 1, 9 => 0.5, 10 => 1, 11 => 1, 12 => 0.5, 13 => 1, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // どく
        8 => [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 2, 6 => 1, 7 => 1, 8 => 0.5, 9 => 0.5, 10 => 1, 11 => 1, 12 => 1, 13 => 0.5, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // じめん
        9 => [1 => 1, 2 => 2, 3 => 1, 4 => 2, 5 => 0.5, 6 => 1, 7 => 1, 8 => 2, 9 => 1, 10 => 1, 11 => 1, 12 => 1, 13 => 1, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // ひこう
        10 => [1 => 1, 2 => 1, 3 => 1, 4 => 0.5, 5 => 2, 6 => 1, 7 => 2, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 1, 13 => 0.5, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // エスパー
        11 => [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 2, 8 => 2, 9 => 1, 10 => 1, 11 => 0.5, 12 => 1, 13 => 1, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // むし
        12 => [1 => 1, 2 => 0.5, 3 => 1, 4 => 1, 5 => 2, 6 => 1, 7 => 0.5, 8 => 0.5, 9 => 1, 10 => 0.5, 11 => 2, 12 => 1, 13 => 1, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // いわ
        13 => [1 => 1, 2 => 2, 3 => 1, 4 => 1, 5 => 1, 6 => 2, 7 => 0.5, 8 => 1, 9 => 0.5, 10 => 2, 11 => 1, 12 => 2, 13 => 1, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // ゴースト
        14 => [1 => 0, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 2, 12 => 1, 13 => 1, 14 => 2, 15 => 1, 16 => 1, 17 => 1, 18 => 1],
        // ドラゴン
        15 => [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 1, 13 => 1, 14 => 1, 15 => 2, 16 => 1, 17 => 1, 18 => 1],
        // あく
        16 => [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 0.5, 8 => 1, 9 => 1, 10 => 1, 11 => 2, 12 => 1, 13 => 1, 14 => 2, 15 => 1, 16 => 0.5, 17 => 1, 18 => 1],
        // はがね
        17 => [1 => 1, 2 => 0.5, 3 => 0.5, 4 => 0.5, 5 => 1, 6 => 2, 7 => 1, 8 => 1, 9 => 2, 10 => 1, 11 => 1, 12 => 1, 13 => 2, 14 => 1, 15 => 1, 16 => 1, 17 => 0.5, 18 => 1],
        // フェアリー
        18 => [1 => 1, 2 => 0.5, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 2, 8 => 0.5, 9 => 1, 10 => 1, 11 => 1, 12 => 1, 13 => 1, 14 => 1, 15 => 2, 16 => 2, 17 => 0.5, 18 => 1]
    ];

    /**
     * パーティ同士の対戦をシミュレート
     */
    public function simulateBattle(MyParty $party1, MyParty $party2): array
    {
        $pokemon1 = $party1->getAllPokemon()->toArray();
        $pokemon2 = $party2->getAllPokemon()->toArray();
        
        $results = [
            'winner' => null,
            'turns' => 0,
            'damage_dealt' => 0,
            'damage_taken' => 0,
            'pokemon_used' => 0,
            'effectiveness_used' => 0
        ];

        // 簡易的な対戦シミュレーション
        $party1Score = $this->calculatePartyScore($pokemon1);
        $party2Score = $this->calculatePartyScore($pokemon2);
        
        // ランダム要素を追加（実際の対戦の不確実性を再現）
        $randomFactor1 = mt_rand(85, 115) / 100;
        $randomFactor2 = mt_rand(85, 115) / 100;
        
        $finalScore1 = $party1Score * $randomFactor1;
        $finalScore2 = $party2Score * $randomFactor2;
        
        if ($finalScore1 > $finalScore2) {
            $results['winner'] = 1;
            $results['damage_dealt'] = $finalScore1;
            $results['damage_taken'] = $finalScore2;
        } else {
            $results['winner'] = 2;
            $results['damage_dealt'] = $finalScore2;
            $results['damage_taken'] = $finalScore1;
        }
        
        $results['turns'] = mt_rand(5, 20);
        $results['pokemon_used'] = mt_rand(1, 6);
        $results['effectiveness_used'] = mt_rand(0, 10);
        
        return $results;
    }

    /**
     * パーティの総合スコアを計算
     */
    private function calculatePartyScore(array $pokemon): float
    {
        $totalScore = 0;
        
        foreach ($pokemon as $poke) {
            if ($poke) {
                $totalScore += $this->calculatePokemonScore($poke);
            }
        }
        
        // パーティの相性ボーナス
        $synergyBonus = $this->calculateSynergyBonus($pokemon);
        
        return $totalScore * (1 + $synergyBonus);
    }

    /**
     * 個体ポケモンのスコアを計算
     */
    private function calculatePokemonScore(MyPokemon $pokemon): float
    {
        $baseScore = 0;
        
        // 実数値による基本スコア
        $baseScore += $pokemon->H_real_values * 0.5;
        $baseScore += $pokemon->A_real_values * 1.0;
        $baseScore += $pokemon->B_real_values * 1.0;
        $baseScore += $pokemon->C_real_values * 1.0;
        $baseScore += $pokemon->D_real_values * 1.0;
        $baseScore += $pokemon->S_real_values * 0.8;
        
        // 技の威力によるボーナス
        $moveBonus = $this->calculateMoveBonus($pokemon);
        
        // 特性によるボーナス
        $abilityBonus = $this->calculateAbilityBonus($pokemon);
        
        // 持ち物によるボーナス
        $itemBonus = $this->calculateItemBonus($pokemon);
        
        return $baseScore + $moveBonus + $abilityBonus + $itemBonus;
    }

    /**
     * 技構成によるボーナスを計算
     */
    private function calculateMoveBonus(MyPokemon $pokemon): float
    {
        $bonus = 0;
        $moves = [$pokemon->move1, $pokemon->move2, $pokemon->move3, $pokemon->move4];
        
        foreach ($moves as $move) {
            if ($move) {
                $bonus += $move->power ?? 0;
                
                // タイプ一致ボーナス
                if ($move->type_id == $pokemon->pokemon->type1_id || 
                    $move->type_id == $pokemon->pokemon->type2_id) {
                    $bonus += 20;
                }
            }
        }
        
        return $bonus * 0.1;
    }

    /**
     * 特性によるボーナスを計算
     */
    private function calculateAbilityBonus(MyPokemon $pokemon): float
    {
        // 特性による効果を簡易的に実装
        $abilityName = $pokemon->characteristics->name ?? '';
        
        $bonuses = [
            'がんじょう' => 50,
            'すてみ' => 40,
            'テクニシャン' => 30,
            'ちからずく' => 35,
            'ふくつのこころ' => 25,
            'せいしんりょく' => 20,
            'いかく' => 15,
            'せいでんき' => 20,
            'どんかん' => 10,
            'マイペース' => 15
        ];
        
        return $bonuses[$abilityName] ?? 0;
    }

    /**
     * 持ち物によるボーナスを計算
     */
    private function calculateItemBonus(MyPokemon $pokemon): float
    {
        $itemName = $pokemon->goods->name ?? '';
        
        $bonuses = [
            'こだわりハチマキ' => 30,
            'こだわりメガネ' => 30,
            'こだわりスカーフ' => 30,
            'たつじんのおび' => 25,
            'せいれいプレート' => 20,
            'しんかのきせき' => 15,
            'でんきだま' => 20,
            'かえんだま' => 20,
            'みずだま' => 20,
            'くさだま' => 20
        ];
        
        return $bonuses[$itemName] ?? 0;
    }

    /**
     * パーティの相性ボーナスを計算
     */
    private function calculateSynergyBonus(array $pokemon): float
    {
        $bonus = 0;
        $types = [];
        
        // タイプの多様性をチェック
        foreach ($pokemon as $poke) {
            if ($poke) {
                $types[] = $poke->pokemon->type1_id;
                if ($poke->pokemon->type2_id) {
                    $types[] = $poke->pokemon->type2_id;
                }
            }
        }
        
        $uniqueTypes = count(array_unique($types));
        $bonus += $uniqueTypes * 0.05; // タイプ多様性ボーナス
        
        // 相性の良いタイプコンボをチェック
        $synergyCombos = [
            [2, 3], // ほのお+みず
            [4, 5], // でんき+くさ
            [7, 8], // かくとう+どく
            [11, 14], // エスパー+ゴースト
        ];
        
        foreach ($synergyCombos as $combo) {
            if (in_array($combo[0], $types) && in_array($combo[1], $types)) {
                $bonus += 0.1;
            }
        }
        
        return min($bonus, 0.5); // 最大50%のボーナス
    }

    /**
     * タイプ相性を取得
     */
    public function getTypeEffectiveness(int $attackType, int $defenseType): float
    {
        return $this->typeEffectiveness[$attackType][$defenseType] ?? 1.0;
    }
}

