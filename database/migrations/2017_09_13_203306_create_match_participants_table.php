<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('matchId');
            $table->string('stats', 10000)->default('');
            $table->integer('spell1Id');
            $table->integer('spell2Id');
            $table->string('participantId');
            $table->string('runes', 1000)->default('');
            $table->string('highestAchievedSeasonTier')->default('');
            $table->string('masteries', 1000)->default('');
            $table->integer('teamId');
            $table->string('timeline', 1000)->default('');
            $table->integer('championId');
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
        Schema::dropIfExists('match_participants');
    }
}
