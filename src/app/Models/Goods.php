<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    use HasFactory;

    protected $table = 'goods';

    protected $fillable = [
        'id',
        'name'
    ];

    protected $casts = [
        'id' => 'integer'
    ];

    // リレーション
    public function myPokemons()
    {
        return $this->hasMany(MyPokemon::class);
    }
}
