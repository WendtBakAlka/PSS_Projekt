<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_games', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ID z RAWG (z wyników wyszukiwania, game.id)
            $table->unsignedBigInteger('rawg_game_id');

            // minimum do wyświetlenia biblioteki bez trzymania całej tabeli games
            $table->string('title');
            $table->string('cover_url')->nullable();

            // 1-10, może być null
            $table->unsignedTinyInteger('rating')->nullable();

            // status: do ogrania / w trakcie / ograna
            $table->string('status', 32)->default('to_play'); // to_play|playing|finished

            $table->timestamps();

            $table->unique(['user_id', 'rawg_game_id']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_games');
    }
};
