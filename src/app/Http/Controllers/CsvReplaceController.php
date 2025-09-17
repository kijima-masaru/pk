<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Move;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CsvReplaceController extends Controller
{
    /**
     * CSV置換画面を表示
     */
    public function index()
    {
        return view('csv-replace.index');
    }

    /**
     * 一括処理画面を表示
     */
    public function batch()
    {
        // ok_moveフォルダ内のCSVファイル一覧を取得
        $csvFiles = $this->getCsvFiles();
        
        return view('csv-replace.batch', compact('csvFiles'));
    }

    /**
     * CSVファイルをアップロードして置換処理を実行
     */
    public function process(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'column_index' => 'required|integer|min:0',
        ]);

        $file = $request->file('csv_file');
        $columnIndex = $request->input('column_index');
        
        // CSVファイルを読み込み（タブ区切りに対応）
        $csvData = [];
        if (($handle = fopen($file->getPathname(), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
                $csvData[] = $data;
            }
            fclose($handle);
        }

        // 置換処理（2行ごとに処理）
        $replaceResults = [];
        $totalRows = count($csvData);
        $successCount = 0;
        $errorCount = 0;

        for ($i = 0; $i < $totalRows; $i += 2) {
            // 1行目（技名がある行）を処理
            if (isset($csvData[$i][$columnIndex]) && !empty($csvData[$i][$columnIndex])) {
                $moveName = trim($csvData[$i][$columnIndex]);
                
                // 技名を正規化（New、[遺伝経路]などを除去）
                $normalizedMoveName = $this->normalizeMoveName($moveName);
                
                // moveテーブルから名前で検索
                $move = Move::where('name', $normalizedMoveName)->first();
                
                if ($move) {
                    // 1行目を置換（置換した値のみ残す）
                    $csvData[$i] = [$move->id];
                    
                    // 2行目も同じIDに置換（存在する場合）
                    if (isset($csvData[$i + 1])) {
                        $csvData[$i + 1] = [$move->id];
                    }
                    
                    $replaceResults[] = [
                        'row' => $i + 1,
                        'original' => $moveName,
                        'replaced' => $move->id,
                        'status' => 'success',
                        'note' => '2行セットで置換',
                        'normalized' => $normalizedMoveName
                    ];
                    $successCount++;
                } else {
                    $replaceResults[] = [
                        'row' => $i + 1,
                        'original' => $moveName,
                        'replaced' => null,
                        'status' => 'error',
                        'error' => 'Move not found',
                        'normalized' => $normalizedMoveName
                    ];
                    $errorCount++;
                }
            }
        }

        // 置換後のCSVファイルを生成
        $outputFileName = 'replaced_' . time() . '.csv';
        $outputPath = storage_path('app/public/' . $outputFileName);
        
        if (($handle = fopen($outputPath, "w")) !== FALSE) {
            foreach ($csvData as $row) {
                fputcsv($handle, $row, "\t");
            }
            fclose($handle);
        }

        return view('csv-replace.result', compact(
            'replaceResults', 
            'totalRows', 
            'successCount', 
            'errorCount', 
            'outputFileName'
        ));
    }

    /**
     * 置換後のCSVファイルをダウンロード
     */
    public function download($filename)
    {
        $filePath = storage_path('app/public/' . $filename);
        
        if (file_exists($filePath)) {
            return response()->download($filePath)->deleteFileAfterSend(true);
        }
        
        return redirect()->back()->with('error', 'ファイルが見つかりません。');
    }

    /**
     * 技名を正規化（New、[遺伝経路]、色名などを除去）
     */
    private function normalizeMoveName($moveName)
    {
        // 元の技名を保持
        $originalName = $moveName;
        
        // 1. "New"を除去
        $moveName = preg_replace('/New$/', '', $moveName);
        
        // 2. "[遺伝経路]"を除去
        $moveName = preg_replace('/\[遺伝経路\]$/', '', $moveName);
        
        // 3. その他の括弧内の文字を除去（例：[技名]、[特殊]など）
        $moveName = preg_replace('/\[.*?\]$/', '', $moveName);
        
        // 4. 色名を除去（碧、藍、赤、青、緑、黄、紫、白、黒など）
        $colorNames = ['碧', '藍', '赤', '青', '緑', '黄', '紫', '白', '黒', '朱', '橙', '桃', '茶', '灰', '銀', '金'];
        foreach ($colorNames as $color) {
            $moveName = preg_replace('/' . preg_quote($color, '/') . '$/', '', $moveName);
        }
        
        // 5. 前後の空白を除去
        $moveName = trim($moveName);
        
        // 6. 空になった場合は元の名前を返す
        if (empty($moveName)) {
            return $originalName;
        }
        
        return $moveName;
    }

    /**
     * ok_moveフォルダ内のCSVファイル一覧を取得
     */
    private function getCsvFiles()
    {
        $csvPath = public_path('json/basic_deta/pokemons/ok_move');
        $files = [];
        
        if (is_dir($csvPath)) {
            $fileList = scandir($csvPath);
            foreach ($fileList as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
                    $filePath = $csvPath . '/' . $file;
                    $files[] = [
                        'name' => $file,
                        'path' => $filePath,
                        'size' => filesize($filePath),
                        'modified' => filemtime($filePath)
                    ];
                }
            }
        }
        
        // ファイル名でソート
        usort($files, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $files;
    }

    /**
     * 一括処理を実行
     */
    public function batchProcess(Request $request)
    {
        $request->validate([
            'column_index' => 'required|integer|min:0',
            'selected_files' => 'required|array|min:1',
            'selected_files.*' => 'string',
        ]);

        $columnIndex = $request->input('column_index');
        $selectedFiles = $request->input('selected_files');
        
        $results = [];
        $totalSuccess = 0;
        $totalError = 0;
        $totalFiles = count($selectedFiles);

        foreach ($selectedFiles as $filePath) {
            if (!file_exists($filePath)) {
                $results[] = [
                    'file' => basename($filePath),
                    'status' => 'error',
                    'message' => 'ファイルが見つかりません',
                    'success_count' => 0,
                    'error_count' => 0
                ];
                $totalError++;
                continue;
            }

            // CSVファイルを読み込み（タブ区切りに対応）
            $csvData = [];
            if (($handle = fopen($filePath, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
                    $csvData[] = $data;
                }
                fclose($handle);
            }

            // 置換処理（2行ごとに処理）
            $successCount = 0;
            $errorCount = 0;

            for ($i = 0; $i < count($csvData); $i += 2) {
                if (isset($csvData[$i][$columnIndex]) && !empty($csvData[$i][$columnIndex])) {
                    $moveName = trim($csvData[$i][$columnIndex]);
                    $normalizedMoveName = $this->normalizeMoveName($moveName);
                    $move = Move::where('name', $normalizedMoveName)->first();
                    
                    if ($move) {
                        // 置換した値のみ残す
                        $csvData[$i] = [$move->id];
                        if (isset($csvData[$i + 1])) {
                            $csvData[$i + 1] = [$move->id];
                        }
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
            }

            // ファイルを上書き（タブ区切りで保存）
            if (($handle = fopen($filePath, "w")) !== FALSE) {
                foreach ($csvData as $row) {
                    fputcsv($handle, $row, "\t");
                }
                fclose($handle);
            }

            $results[] = [
                'file' => basename($filePath),
                'status' => 'success',
                'message' => '処理完了',
                'success_count' => $successCount,
                'error_count' => $errorCount
            ];

            $totalSuccess++;
        }

        return view('csv-replace.batch-result', compact(
            'results', 
            'totalFiles', 
            'totalSuccess', 
            'totalError',
            'columnIndex'
        ));
    }

    /**
     * プレビュー機能 - CSVの内容を確認
     */
    public function preview(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $csvData = [];
        $previewRows = 10; // 最初の10行のみプレビュー
        
        if (($handle = fopen($file->getPathname(), "r")) !== FALSE) {
            $rowCount = 0;
            while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE && $rowCount < $previewRows) {
                $csvData[] = $data;
                $rowCount++;
            }
            fclose($handle);
        }

        return response()->json([
            'preview' => $csvData,
            'total_columns' => count($csvData[0] ?? [])
        ]);
    }
}
