<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;

    protected $table = 'types';

    protected $fillable = [
        'id',
        'name'
    ];

    protected $casts = [
        'id' => 'integer'
    ];

    // リレーション
    public function pokemonsType1()
    {
        return $this->hasMany(Pokemon::class, 'type1_id');
    }

    public function pokemonsType2()
    {
        return $this->hasMany(Pokemon::class, 'type2_id');
    }

    public function pokemonFormsType1()
    {
        return $this->hasMany(PokemonForm::class, 'type1_id');
    }

    public function pokemonFormsType2()
    {
        return $this->hasMany(PokemonForm::class, 'type2_id');
    }

    public function pokemonMegasType1()
    {
        return $this->hasMany(PokemonMega::class, 'type1_id');
    }

    public function pokemonMegasType2()
    {
        return $this->hasMany(PokemonMega::class, 'type2_id');
    }

    public function moves()
    {
        return $this->hasMany(Move::class);
    }
}