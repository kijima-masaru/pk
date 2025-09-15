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
        return view('bulk_data', compact('tables'));
    }

    /**
     * データ一括保存処理
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table' => 'required|string|in:' . implode(',', $this->getAvailableTables()),
            'json_file' => 'required|file|mimes:json|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $table = $request->input('table');
            $file = $request->file('json_file');
            
            // JSONファイルの内容を読み込み
            $jsonContent = file_get_contents($file->getPathname());
            $data = json_decode($jsonContent, true);

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

            return redirect()->back()
                ->with('success', "{$table}テーブルに{$insertedCount}件のデータを保存しました");

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
                // created_at, updated_atを追加
                $record['created_at'] = now();
                $record['updated_at'] = now();

                // テーブルに挿入
                DB::table($table)->insert($record);
                $insertedCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $insertedCount;
    }
}
