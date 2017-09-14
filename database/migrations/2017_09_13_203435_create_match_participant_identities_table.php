<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchParticipantIdentitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_participant_identities', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('matchId');
            $table->string('currentPlatformId')->default('');;
            $table->string('summonerName')->default('');;
            $table->string('matchHistoryUri')->default('');;
            $table->string('currentAccountId')->default('');;
            $table->string('profileIcon')->default('');;
            $table->string('summonerId')->default('');;
            $table->string('accountId')->default('');;
            $table->string('platformId')->default('');;
            $table->string('participantId')->default('');;
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
        Schema::dropIfExists('match_participant_identities');
    }
}
