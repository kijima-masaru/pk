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
        Schema::create('my_parties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('pokemon1_id')->nullable()->constrained('my_pokemons');
            $table->foreignId('pokemon2_id')->nullable()->constrained('my_pokemons');
            $table->foreignId('pokemon3_id')->nullable()->constrained('my_pokemons');
            $table->foreignId('pokemon4_id')->nullable()->constrained('my_pokemons');
            $table->foreignId('pokemon5_id')->nullable()->constrained('my_pokemons');
            $table->foreignId('pokemon6_id')->nullable()->constrained('my_pokemons');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('my_parties');
    }
};
