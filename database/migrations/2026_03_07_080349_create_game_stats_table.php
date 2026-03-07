<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('game_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rawg_game_id')->unique(); // klucz z RAWG
            $table->string('title')->nullable(); // przechowujemy dla wygody
            $table->string('cover_url')->nullable();
            $table->decimal('average_rating', 3, 2)->nullable(); // np. 7.50
            $table->unsignedInteger('ratings_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_stats');
    }
};
