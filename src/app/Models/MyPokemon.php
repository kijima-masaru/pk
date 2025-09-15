<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyPokemon extends Model
{
    use HasFactory;

    protected $table = 'my_pokemons';

    protected $fillable = [
        'id',
        'name',
        'pokemon_id',
        'pokemon_form_id',
        'level',
        'personality_id',
        'characteristics_id',
        'goods_id',
        'H_effort_values',
        'A_effort_values',
        'B_effort_values',
        'C_effort_values',
        'D_effort_values',
        'S_effort_values',
        'H_real_values',
        'A_real_values',
        'B_real_values',
        'C_real_values',
        'D_real_values',
        'S_real_values',
        'move1_id',
        'move2_id',
        'move3_id',
        'move4_id',
        'move1_PP',
        'move2_PP',
        'move3_PP',
        'move4_PP'
    ];

    protected $casts = [
        'id' => 'integer',
        'pokemon_id' => 'integer',
        'pokemon_form_id' => 'integer',
        'level' => 'integer',
        'personality_id' => 'integer',
        'characteristics_id' => 'integer',
        'goods_id' => 'integer',
        'H_effort_values' => 'integer',
        'A_effort_values' => 'integer',
        'B_effort_values' => 'integer',
        'C_effort_values' => 'integer',
        'D_effort_values' => 'integer',
        'S_effort_values' => 'integer',
        'H_real_values' => 'integer',
        'A_real_values' => 'integer',
        'B_real_values' => 'integer',
        'C_real_values' => 'integer',
        'D_real_values' => 'integer',
        'S_real_values' => 'integer',
        'move1_id' => 'integer',
        'move2_id' => 'integer',
        'move3_id' => 'integer',
        'move4_id' => 'integer',
        'move1_PP' => 'integer',
        'move2_PP' => 'integer',
        'move3_PP' => 'integer',
        'move4_PP' => 'integer'
    ];

    // リレーション
    public function pokemon()
    {
        return $this->belongsTo(Pokemon::class);
    }

    public function pokemonForm()
    {
        return $this->belongsTo(PokemonForm::class, 'pokemon_form_id');
    }

    public function personality()
    {
        return $this->belongsTo(Personality::class);
    }

    public function characteristics()
    {
        return $this->belongsTo(Characteristic::class, 'characteristics_id');
    }

    public function goods()
    {
        return $this->belongsTo(Goods::class);
    }

    public function move1()
    {
        return $this->belongsTo(Move::class, 'move1_id');
    }

    public function move2()
    {
        return $this->belongsTo(Move::class, 'move2_id');
    }

    public function move3()
    {
        return $this->belongsTo(Move::class, 'move3_id');
    }

    public function move4()
    {
        return $this->belongsTo(Move::class, 'move4_id');
    }
}
