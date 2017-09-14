<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('matchId');
            $table->boolean('firstDragon')->default(0);
            $table->string('bans', 10000)->default('');
            $table->boolean('firstInhibitor')->default(0);
            $table->string('win')->default('');;
            $table->boolean('firstRiftHerald')->default(0);
            $table->boolean('firstBaron')->default(0);
            $table->integer('baronKills')->default(0);
            $table->integer('riftHeraldKills')->default(0);
            $table->boolean('firstBlood')->default(0);
            $table->integer('teamId')->default(0);
            $table->boolean('firstTower')->default(false);
            $table->integer('vilemawKills')->default(0);
            $table->integer('inhibitorKills')->default(0);
            $table->integer('towerKills')->default(0);
            $table->integer('dominionVictoryScore')->default(0);
            $table->integer('dragonKills')->default(0);
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
        Schema::dropIfExists('match_teams');
    }
}
