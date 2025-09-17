<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MyPokemon;
use App\Models\Pokemon;
use App\Models\PokemonForm;
use App\Models\Personality;
use App\Models\Characteristic;
use App\Models\Goods;
use App\Models\Move;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MyPokemonController extends Controller
{
    /**
     * ポケモン登録画面を表示
     */
    public function create()
    {
        $personalities = Personality::orderBy('id')->get();
        $characteristics = Characteristic::orderBy('id')->get();
        $goods = Goods::orderBy('id')->get();
        $moves = Move::orderBy('id')->get();

        // 初期値のポケモン名を取得
        $selectedPokemonName = '';
        if (old('pokemon_id')) {
            $selectedPokemon = Pokemon::find(old('pokemon_id'));
            if ($selectedPokemon) {
                $selectedPokemonName = $selectedPokemon->name;
            }
        }

        return view('pokemon.create', compact(
            'personalities',
            'characteristics',
            'goods',
            'moves',
            'selectedPokemonName'
        ));
    }

    /**
     * ポケモンを登録
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'pokemon_id' => 'required|exists:pokemons,id',
            'pokemon_form_id' => 'nullable|exists:pokemon_forms,id',
            'level' => 'required|integer|min:1|max:100',
            'personality_id' => 'required|exists:personalities,id',
            'characteristics_id' => 'required|exists:characteristics,id',
            'goods_id' => 'nullable|exists:goods,id',
            'H_effort_values' => 'required|integer|min:0|max:252',
            'A_effort_values' => 'required|integer|min:0|max:252',
            'B_effort_values' => 'required|integer|min:0|max:252',
            'C_effort_values' => 'required|integer|min:0|max:252',
            'D_effort_values' => 'required|integer|min:0|max:252',
            'S_effort_values' => 'required|integer|min:0|max:252',
            'move1_id' => 'nullable|exists:moves,id',
            'move2_id' => 'nullable|exists:moves,id',
            'move3_id' => 'nullable|exists:moves,id',
            'move4_id' => 'nullable|exists:moves,id',
        ]);

        // 努力値の合計チェック（最大510）
        $totalEffortValues = $request->H_effort_values + $request->A_effort_values + 
                           $request->B_effort_values + $request->C_effort_values + 
                           $request->D_effort_values + $request->S_effort_values;
        
        if ($totalEffortValues > 510) {
            $validator->errors()->add('effort_values', '努力値の合計は510以下である必要があります。');
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // 実数値の計算（簡易版）
        $pokemon = Pokemon::find($request->pokemon_id);
        $personality = Personality::find($request->personality_id);
        
        $H_real = $this->calculateRealValue($pokemon->H, $request->H_effort_values, $request->level, $personality->rise === 'H' ? 1.1 : ($personality->descent === 'H' ? 0.9 : 1.0));
        $A_real = $this->calculateRealValue($pokemon->A, $request->A_effort_values, $request->level, $personality->rise === 'A' ? 1.1 : ($personality->descent === 'A' ? 0.9 : 1.0));
        $B_real = $this->calculateRealValue($pokemon->B, $request->B_effort_values, $request->level, $personality->rise === 'B' ? 1.1 : ($personality->descent === 'B' ? 0.9 : 1.0));
        $C_real = $this->calculateRealValue($pokemon->C, $request->C_effort_values, $request->level, $personality->rise === 'C' ? 1.1 : ($personality->descent === 'C' ? 0.9 : 1.0));
        $D_real = $this->calculateRealValue($pokemon->D, $request->D_effort_values, $request->level, $personality->rise === 'D' ? 1.1 : ($personality->descent === 'D' ? 0.9 : 1.0));
        $S_real = $this->calculateRealValue($pokemon->S, $request->S_effort_values, $request->level, $personality->rise === 'S' ? 1.1 : ($personality->descent === 'S' ? 0.9 : 1.0));

        $myPokemon = MyPokemon::create([
            'name' => $request->name,
            'user_id' => Auth::id(),
            'pokemon_id' => $request->pokemon_id,
            'pokemon_form_id' => $request->pokemon_form_id,
            'level' => $request->level,
            'personality_id' => $request->personality_id,
            'characteristics_id' => $request->characteristics_id,
            'goods_id' => $request->goods_id,
            'H_effort_values' => $request->H_effort_values,
            'A_effort_values' => $request->A_effort_values,
            'B_effort_values' => $request->B_effort_values,
            'C_effort_values' => $request->C_effort_values,
            'D_effort_values' => $request->D_effort_values,
            'S_effort_values' => $request->S_effort_values,
            'H_real_values' => $H_real,
            'A_real_values' => $A_real,
            'B_real_values' => $B_real,
            'C_real_values' => $C_real,
            'D_real_values' => $D_real,
            'S_real_values' => $S_real,
            'move1_id' => $request->move1_id,
            'move2_id' => $request->move2_id,
            'move3_id' => $request->move3_id,
            'move4_id' => $request->move4_id,
            'move1_PP' => $request->move1_id ? Move::find($request->move1_id)->PP : null,
            'move2_PP' => $request->move2_id ? Move::find($request->move2_id)->PP : null,
            'move3_PP' => $request->move3_id ? Move::find($request->move3_id)->PP : null,
            'move4_PP' => $request->move4_id ? Move::find($request->move4_id)->PP : null,
        ]);

        return redirect()->route('pokemon.index')
            ->with('success', 'ポケモンが正常に登録されました！');
    }

    /**
     * ポケモン一覧を表示
     */
    public function index()
    {
        $myPokemons = MyPokemon::where('user_id', Auth::id())
            ->with(['pokemon', 'personality', 'characteristics', 'goods'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pokemon.index', compact('myPokemons'));
    }

    /**
     * 実数値計算（簡易版）
     */
    private function calculateRealValue($baseStat, $effortValue, $level, $natureMultiplier)
    {
        // 簡易的な実数値計算式
        $realValue = floor(($baseStat * 2 + $effortValue / 4 + 31) * $level / 100 + 5) * $natureMultiplier;
        return floor($realValue);
    }

    /**
     * ポケモンフォームを取得（AJAX用）
     */
    public function getPokemonForms(Request $request)
    {
        $pokemonId = $request->pokemon_id;
        $forms = PokemonForm::where('pokemon_id', $pokemonId)->get();
        
        return response()->json($forms);
    }

    /**
     * ポケモン検索（AJAX用）
     */
    public function searchPokemons(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 1) {
            return response()->json([]);
        }
        
        $pokemons = Pokemon::where('name', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);
        
        return response()->json($pokemons);
    }

    /**
     * ポケモンが覚えられる技を取得（AJAX用）
     */
    public function getPokemonMoves(Request $request)
    {
        $pokemonId = $request->pokemon_id;
        
        if (!$pokemonId) {
            return response()->json([]);
        }
        
        // ok_moveフォルダ内のCSVファイルを読み込み
        $csvPath = public_path("json/basic_deta/pokemons/ok_move/{$pokemonId}.csv");
        
        if (!file_exists($csvPath)) {
            return response()->json([]);
        }
        
        $csvContent = file_get_contents($csvPath);
        $lines = explode("\n", trim($csvContent));
        
        $moveIds = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $moveIds[] = (int)$line;
            }
        }
        
        // 技の詳細情報を取得
        $moves = Move::whereIn('id', $moveIds)
            ->orderBy('id')
            ->get(['id', 'name']);
        
        return response()->json($moves);
    }
}
