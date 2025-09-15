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
        Schema::create('pokemon_megas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('type1_id')->constrained('types');
            $table->foreignId('type2_id')->nullable()->constrained('types');
            $table->foreignId('characteristics1_id')->constrained('characteristics');
            $table->foreignId('characteristics2_id')->nullable()->constrained('characteristics');
            $table->foreignId('characteristics3_id')->nullable()->constrained('characteristics');
            $table->foreignId('characteristics4_id')->nullable()->constrained('characteristics');
            $table->integer('H');
            $table->integer('A');
            $table->integer('B');
            $table->integer('C');
            $table->integer('D');
            $table->integer('S');
            $table->foreignId('pokemon_id')->constrained('pokemons');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pokemon_megas');
    }
};
