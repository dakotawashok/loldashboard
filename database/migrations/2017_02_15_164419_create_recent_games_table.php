<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecentGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recent_games', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gameId');
            $table->string('summonerId');
            $table->integer('championId');
            $table->longText('fellowPlayers');
            $table->string('createDate');
            $table->integer('spell1');
            $table->integer('spell2');
            $table->longText('stats');
            $table->string('gameMode');
            $table->boolean('invalid');
            $table->integer('mapId');
            $table->integer('level');
            $table->integer('ipEarned');
            $table->integer('teamId');
            $table->string('gameType');
            $table->string('subType');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recent_games');
    }
}
