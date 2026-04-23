<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('to_play'); // to_play, playing, finished
            $table->unsignedTinyInteger('rating')->nullable(); // ocena użytkownika 1-10
            $table->timestamps();

            $table->unique(['user_id', 'game_id']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_games');
    }
};
