<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Move extends Model
{
    use HasFactory;

    protected $table = 'moves';

    protected $fillable = [
        'id',
        'name',
        'type_id',
        'category',
        'power',
        'accuracy',
        'PP',
        'target'
    ];

    protected $casts = [
        'id' => 'integer',
        'type_id' => 'integer',
        'power' => 'integer',
        'accuracy' => 'integer',
        'PP' => 'integer'
    ];

    protected $nullable = [
        'power',
        'accuracy',
        'PP'
    ];

    // リレーション
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function myPokemonsMove1()
    {
        return $this->hasMany(MyPokemon::class, 'move1_id');
    }

    public function myPokemonsMove2()
    {
        return $this->hasMany(MyPokemon::class, 'move2_id');
    }

    public function myPokemonsMove3()
    {
        return $this->hasMany(MyPokemon::class, 'move3_id');
    }

    public function myPokemonsMove4()
    {
        return $this->hasMany(MyPokemon::class, 'move4_id');
    }
}
