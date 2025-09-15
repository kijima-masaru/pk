<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyParty extends Model
{
    use HasFactory;

    protected $table = 'my_parties';

    protected $fillable = [
        'id',
        'name',
        'user_id',
        'pokemon1_id',
        'pokemon2_id',
        'pokemon3_id',
        'pokemon4_id',
        'pokemon5_id',
        'pokemon6_id'
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'pokemon1_id' => 'integer',
        'pokemon2_id' => 'integer',
        'pokemon3_id' => 'integer',
        'pokemon4_id' => 'integer',
        'pokemon5_id' => 'integer',
        'pokemon6_id' => 'integer'
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pokemon1()
    {
        return $this->belongsTo(MyPokemon::class, 'pokemon1_id');
    }

    public function pokemon2()
    {
        return $this->belongsTo(MyPokemon::class, 'pokemon2_id');
    }

    public function pokemon3()
    {
        return $this->belongsTo(MyPokemon::class, 'pokemon3_id');
    }

    public function pokemon4()
    {
        return $this->belongsTo(MyPokemon::class, 'pokemon4_id');
    }

    public function pokemon5()
    {
        return $this->belongsTo(MyPokemon::class, 'pokemon5_id');
    }

    public function pokemon6()
    {
        return $this->belongsTo(MyPokemon::class, 'pokemon6_id');
    }

    // パーティの全ポケモンを取得するメソッド
    public function getAllPokemon()
    {
        return collect([
            $this->pokemon1,
            $this->pokemon2,
            $this->pokemon3,
            $this->pokemon4,
            $this->pokemon5,
            $this->pokemon6
        ])->filter(); // nullを除外
    }
}
