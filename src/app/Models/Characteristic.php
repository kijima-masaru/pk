<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Characteristic extends Model
{
    use HasFactory;

    protected $table = 'characteristics';

    protected $fillable = [
        'id',
        'name'
    ];

    protected $casts = [
        'id' => 'integer'
    ];

    // リレーション
    public function pokemonsCharacteristics1()
    {
        return $this->hasMany(Pokemon::class, 'characteristics1_id');
    }

    public function pokemonsCharacteristics2()
    {
        return $this->hasMany(Pokemon::class, 'characteristics2_id');
    }

    public function pokemonsCharacteristics3()
    {
        return $this->hasMany(Pokemon::class, 'characteristics3_id');
    }

    public function pokemonsCharacteristics4()
    {
        return $this->hasMany(Pokemon::class, 'characteristics4_id');
    }

    public function pokemonFormsCharacteristics1()
    {
        return $this->hasMany(PokemonForm::class, 'characteristics1_id');
    }

    public function pokemonFormsCharacteristics2()
    {
        return $this->hasMany(PokemonForm::class, 'characteristics2_id');
    }

    public function pokemonFormsCharacteristics3()
    {
        return $this->hasMany(PokemonForm::class, 'characteristics3_id');
    }

    public function pokemonFormsCharacteristics4()
    {
        return $this->hasMany(PokemonForm::class, 'characteristics4_id');
    }

    public function pokemonMegasCharacteristics1()
    {
        return $this->hasMany(PokemonMega::class, 'characteristics1_id');
    }

    public function pokemonMegasCharacteristics2()
    {
        return $this->hasMany(PokemonMega::class, 'characteristics2_id');
    }

    public function pokemonMegasCharacteristics3()
    {
        return $this->hasMany(PokemonMega::class, 'characteristics3_id');
    }

    public function pokemonMegasCharacteristics4()
    {
        return $this->hasMany(PokemonMega::class, 'characteristics4_id');
    }

    public function myPokemons()
    {
        return $this->hasMany(MyPokemon::class, 'characteristics_id');
    }
}