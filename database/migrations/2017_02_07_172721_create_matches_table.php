<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('seasonId');
            $table->string('queueId');
            $table->longText('gameId');
            $table->string('gameVersion');
            $table->string('platformId');
            $table->string('gameMode');
            $table->string('mapId');
            $table->string('gameType');
            $table->string('gameDuration');
            $table->longText('gameCreation');
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
        Schema::dropIfExists('matches');
    }
}
