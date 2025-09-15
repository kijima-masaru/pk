<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    use HasFactory;

    protected $table = 'pokemons';

    protected $fillable = [
        'id',
        'name',
        'type1_id',
        'type2_id',
        'characteristics1_id',
        'characteristics2_id',
        'characteristics3_id',
        'characteristics4_id',
        'H',
        'A',
        'B',
        'C',
        'D',
        'S'
    ];

    protected $casts = [
        'id' => 'integer',
        'type1_id' => 'integer',
        'type2_id' => 'integer',
        'characteristics1_id' => 'integer',
        'characteristics2_id' => 'integer',
        'characteristics3_id' => 'integer',
        'characteristics4_id' => 'integer',
        'H' => 'integer',
        'A' => 'integer',
        'B' => 'integer',
        'C' => 'integer',
        'D' => 'integer',
        'S' => 'integer'
    ];

    // リレーション
    public function type1()
    {
        return $this->belongsTo(Type::class, 'type1_id');
    }

    public function type2()
    {
        return $this->belongsTo(Type::class, 'type2_id');
    }

    public function characteristics1()
    {
        return $this->belongsTo(Characteristic::class, 'characteristics1_id');
    }

    public function characteristics2()
    {
        return $this->belongsTo(Characteristic::class, 'characteristics2_id');
    }

    public function characteristics3()
    {
        return $this->belongsTo(Characteristic::class, 'characteristics3_id');
    }

    public function characteristics4()
    {
        return $this->belongsTo(Characteristic::class, 'characteristics4_id');
    }

    public function forms()
    {
        return $this->hasMany(PokemonForm::class);
    }

    public function megas()
    {
        return $this->hasMany(PokemonMega::class);
    }
}
