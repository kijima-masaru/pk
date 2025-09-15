<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personality extends Model
{
    use HasFactory;

    protected $table = 'personalities';

    protected $fillable = [
        'id',
        'name',
        'rise',
        'descent'
    ];

    protected $casts = [
        'id' => 'integer'
    ];
}
