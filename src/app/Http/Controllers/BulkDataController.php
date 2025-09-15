<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BulkDataController extends Controller
{
    /**
     * データ一括保存画面を表示
     */
    public function index()
    {
        $tables = $this->getAvailableTables();
        $existingJsonFiles = $this->getExistingJsonFiles();
        return view('bulk_data', compact('tables', 'existingJsonFiles'));
    }

    /**
     * データ一括保存処理
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table' => 'required|string|in:' . implode(',', $this->getAvailableTables()),
            'json_file' => 'nullable|file|mimes:json|max:10240', // 10MB max
            'existing_file' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $table = $request->input('table');
            $data = null;

            // アップロードされたファイルがある場合
            if ($request->hasFile('json_file')) {
                $file = $request->file('json_file');
                $jsonContent = file_get_contents($file->getPathname());
                $data = json_decode($jsonContent, true);
            }
            // 既存ファイルが選択された場合
            elseif ($request->input('existing_file')) {
                $existingFile = $request->input('existing_file');
                $filePath = public_path('json/basic_deta/' . $existingFile);
                
                if (!file_exists($filePath)) {
                    return redirect()->back()
                        ->with('error', '選択されたファイルが見つかりません');
                }
                
                $jsonContent = file_get_contents($filePath);
                $data = json_decode($jsonContent, true);
            }
            else {
                return redirect()->back()
                    ->with('error', 'JSONファイルまたは既存ファイルを選択してください');
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()
                    ->with('error', 'JSONファイルの形式が正しくありません: ' . json_last_error_msg());
            }

            if (!is_array($data)) {
                return redirect()->back()
                    ->with('error', 'JSONファイルは配列形式である必要があります');
            }

            // データベースに保存
            $insertedCount = $this->insertDataToTable($table, $data);

            $source = $request->hasFile('json_file') ? 'アップロードファイル' : '既存ファイル';
            return redirect()->back()
                ->with('success', "{$table}テーブルに{$insertedCount}件のデータを保存しました（{$source}から）");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * 利用可能なテーブル一覧を取得
     */
    private function getAvailableTables()
    {
        return [
            'types',
            'characteristics', 
            'personalities',
            'goods',
            'field_effects',
            'status_conditions',
            'pokemons',
            'pokemon_forms',
            'pokemon_megas',
            'moves',
        ];
    }

    /**
     * データをテーブルに挿入
     */
    private function insertDataToTable($table, $data)
    {
        $insertedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($data as $record) {
                // テーブルごとに必要なフィールドのみを抽出
                $filteredRecord = $this->filterFieldsForTable($table, $record);
                
                // created_at, updated_atを追加
                $filteredRecord['created_at'] = now();
                $filteredRecord['updated_at'] = now();

                // テーブルに挿入
                DB::table($table)->insert($filteredRecord);
                $insertedCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $insertedCount;
    }

    /**
     * テーブルごとに必要なフィールドのみを抽出
     */
    private function filterFieldsForTable($table, $record)
    {
        $allowedFields = $this->getAllowedFieldsForTable($table);
        
        return array_intersect_key($record, array_flip($allowedFields));
    }

    /**
     * テーブルごとに許可されたフィールドを取得
     */
    private function getAllowedFieldsForTable($table)
    {
        $fieldMap = [
            'types' => ['id', 'name'],
            'characteristics' => ['id', 'name'],
            'personalities' => ['id', 'name', 'rise', 'descent'],
            'goods' => ['id', 'name'],
            'field_effects' => ['id', 'name'],
            'status_conditions' => ['id', 'name'],
            'pokemons' => [
                'id', 'name', 'type1_id', 'type2_id', 
                'characteristics1_id', 'characteristics2_id', 'characteristics3_id', 'characteristics4_id',
                'H', 'A', 'B', 'C', 'D', 'S'
            ],
            'pokemon_forms' => [
                'id', 'name', 'type1_id', 'type2_id', 
                'characteristics1_id', 'characteristics2_id', 'characteristics3_id', 'characteristics4_id',
                'H', 'A', 'B', 'C', 'D', 'S', 'pokemon_id'
            ],
            'pokemon_megas' => [
                'id', 'name', 'type1_id', 'type2_id', 
                'characteristics1_id', 'characteristics2_id', 'characteristics3_id', 'characteristics4_id',
                'H', 'A', 'B', 'C', 'D', 'S', 'pokemon_id'
            ],
            'moves' => [
                'id', 'name', 'type_id', 'category', 'power', 'accuracy', 'PP', 'target'
            ],
        ];

        return $fieldMap[$table] ?? [];
    }

    /**
     * 既存のJSONファイル一覧を取得
     */
    private function getExistingJsonFiles()
    {
        $jsonPath = public_path('json/basic_deta/');
        $files = [];
        
        if (is_dir($jsonPath)) {
            // ルートディレクトリのJSONファイル
            $rootFiles = glob($jsonPath . '*.json');
            foreach ($rootFiles as $file) {
                $fileName = basename($file);
                $files[] = $fileName;
            }
            
            // サブフォルダのJSONファイル
            $subfolders = ['goods', 'moves', 'pokemons', 'rules'];
            foreach ($subfolders as $subfolder) {
                $subfolderPath = $jsonPath . $subfolder . '/';
                if (is_dir($subfolderPath)) {
                    $subfolderFiles = glob($subfolderPath . '*.json');
                    foreach ($subfolderFiles as $file) {
                        $fileName = $subfolder . '/' . basename($file);
                        $files[] = $fileName;
                    }
                }
            }
        }
        
        return $files;
    }
}
