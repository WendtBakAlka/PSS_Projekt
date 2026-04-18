<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('game_stats', function (Blueprint $table) {
            $table->decimal('rawg_rating', 3, 1)->nullable()->after('average_rating');
        });
    }

    public function down()
    {
        Schema::table('game_stats', function (Blueprint $table) {
            $table->dropColumn('rawg_rating');
        });
    }
};
