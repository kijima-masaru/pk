<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('my_pokemons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('pokemon_id')->constrained('pokemons');
            $table->foreignId('pokemon_form_id')->nullable()->constrained('pokemon_forms');
            $table->integer('level');
            $table->foreignId('personality_id')->constrained('personalities');
            $table->foreignId('characteristics_id')->constrained('characteristics');
            $table->foreignId('goods_id')->constrained('goods');
            $table->integer('H_effort_values');
            $table->integer('A_effort_values');
            $table->integer('B_effort_values');
            $table->integer('C_effort_values');
            $table->integer('D_effort_values');
            $table->integer('S_effort_values');
            $table->integer('H_real_values');
            $table->integer('A_real_values');
            $table->integer('B_real_values');
            $table->integer('C_real_values');
            $table->integer('D_real_values');
            $table->integer('S_real_values');
            $table->foreignId('move1_id')->constrained('moves');
            $table->foreignId('move2_id')->nullable()->constrained('moves');
            $table->foreignId('move3_id')->nullable()->constrained('moves');
            $table->foreignId('move4_id')->nullable()->constrained('moves');
            $table->integer('move1_PP');
            $table->integer('move2_PP');
            $table->integer('move3_PP');
            $table->integer('move4_PP');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('my_pokemons');
    }
};
