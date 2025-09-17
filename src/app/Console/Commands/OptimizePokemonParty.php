<?php

namespace App\Console\Commands;

use App\Services\GeneticAlgorithmOptimizer;
use App\Services\BattleSimulationEngine;
use Illuminate\Console\Command;

class OptimizePokemonParty extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pokemon:optimize 
                            {--generations=100 : Number of generations to run}
                            {--population=50 : Population size}
                            {--constraint= : Optimization constraint (balanced, offensive, defensive, speed)}
                            {--output= : Output file path for results}';

    /**
     * The console command description.
     */
    protected $description = 'AI最適化を使用してポケモンパーティを最適化する';

    private $optimizer;
    private $battleEngine;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->battleEngine = new BattleSimulationEngine();
        $this->optimizer = new GeneticAlgorithmOptimizer($this->battleEngine);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('ポケモンパーティ最適化を開始します...');
        
        // パラメータを取得
        $generations = (int) $this->option('generations');
        $populationSize = (int) $this->option('population');
        $constraint = $this->option('constraint');
        $outputPath = $this->option('output');
        
        // パラメータを設定
        $this->optimizer->setGenerations($generations);
        $this->optimizer->setPopulationSize($populationSize);
        
        $this->info("設定:");
        $this->info("- 世代数: {$generations}");
        $this->info("- 個体数: {$populationSize}");
        $this->info("- 制約: " . ($constraint ?: 'なし'));
        
        // 進捗バーを初期化
        $progressBar = $this->output->createProgressBar($generations);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('最適化中...');
        $progressBar->start();
        
        $startTime = microtime(true);
        
        try {
            // 最適化を実行
            $result = $this->optimizer->optimizeParty(['type' => $constraint]);
            
            $progressBar->finish();
            $this->newLine();
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            // 結果を表示
            $this->displayResults($result, $executionTime);
            
            // 結果をファイルに保存
            if ($outputPath) {
                $this->saveResultsToFile($result, $outputPath);
                $this->info("結果を {$outputPath} に保存しました。");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $progressBar->finish();
            $this->newLine();
            $this->error('最適化中にエラーが発生しました: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 結果を表示
     */
    private function displayResults(array $result, float $executionTime): void
    {
        $this->newLine();
        $this->info('=== 最適化結果 ===');
        $this->info("実行時間: {$executionTime}秒");
        $this->info("最良適応度: " . number_format($result['best_fitness'], 4));
        $this->info("世代数: {$result['generation_count']}");
        $this->info("最良個体のポケモン数: " . count($result['best_individual']));
        
        $this->newLine();
        $this->info('=== 最適化されたパーティ ===');
        
        foreach ($result['best_individual'] as $index => $pokemon) {
            $this->info("ポケモン " . ($index + 1) . ":");
            $this->line("  - ポケモンID: {$pokemon['pokemon_id']}");
            $this->line("  - 性格ID: {$pokemon['personality_id']}");
            $this->line("  - 特性ID: {$pokemon['characteristics_id']}");
            $this->line("  - 持ち物ID: {$pokemon['goods_id']}");
            $this->line("  - 努力値: H:{$pokemon['effort_values']['H']} A:{$pokemon['effort_values']['A']} B:{$pokemon['effort_values']['B']} C:{$pokemon['effort_values']['C']} D:{$pokemon['effort_values']['D']} S:{$pokemon['effort_values']['S']}");
            $this->line("  - 技: " . implode(', ', array_filter($pokemon['moves'])));
            $this->newLine();
        }
        
        // 適応度の履歴を表示
        $this->displayFitnessHistory($result['fitness_history']);
    }
    
    /**
     * 適応度の履歴を表示
     */
    private function displayFitnessHistory(array $fitnessHistory): void
    {
        $this->info('=== 適応度の推移 ===');
        
        $maxFitness = max($fitnessHistory);
        $minFitness = min($fitnessHistory);
        $avgFitness = array_sum($fitnessHistory) / count($fitnessHistory);
        
        $this->info("最大適応度: " . number_format($maxFitness, 4));
        $this->info("最小適応度: " . number_format($minFitness, 4));
        $this->info("平均適応度: " . number_format($avgFitness, 4));
        
        // グラフを表示
        $this->newLine();
        $this->info('適応度の推移グラフ:');
        $this->displayFitnessGraph($fitnessHistory);
    }
    
    /**
     * 適応度のグラフを表示
     */
    private function displayFitnessGraph(array $fitnessHistory): void
    {
        $maxFitness = max($fitnessHistory);
        $minFitness = min($fitnessHistory);
        $range = $maxFitness - $minFitness;
        
        if ($range == 0) {
            $this->line('適応度に変化がありません。');
            return;
        }
        
        $graphWidth = 50;
        $step = count($fitnessHistory) / $graphWidth;
        
        for ($i = 0; $i < $graphWidth; $i++) {
            $index = (int) ($i * $step);
            $fitness = $fitnessHistory[$index];
            $normalizedFitness = ($fitness - $minFitness) / $range;
            $barLength = (int) ($normalizedFitness * 20);
            
            $bar = str_repeat('█', $barLength) . str_repeat('░', 20 - $barLength);
            $this->line(sprintf('%3d: %s %.4f', $index, $bar, $fitness));
        }
    }
    
    /**
     * 結果をファイルに保存
     */
    private function saveResultsToFile(array $result, string $outputPath): void
    {
        $data = [
            'timestamp' => now()->toISOString(),
            'generation_count' => $result['generation_count'],
            'best_fitness' => $result['best_fitness'],
            'fitness_history' => $result['fitness_history'],
            'best_individual' => $result['best_individual']
        ];
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($outputPath, $json);
    }
}
