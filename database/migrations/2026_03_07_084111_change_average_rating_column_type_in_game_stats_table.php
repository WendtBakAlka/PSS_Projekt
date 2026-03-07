<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('game_stats', function (Blueprint $table) {
            // Zmiana na decimal(4,2) – 4 cyfry ogólnie, 2 po przecinku (zakres -99.99 do 99.99)
            $table->decimal('average_rating', 4, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('game_stats', function (Blueprint $table) {
            // Przywrócenie poprzedniego typu (np. decimal(3,2))
            $table->decimal('average_rating', 3, 2)->nullable()->change();
        });
    }
};
