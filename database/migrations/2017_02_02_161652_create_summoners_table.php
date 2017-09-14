<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSummonersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summoners', function (Blueprint $table) {
            $table->increments('id');
            $table->text('accountId');
            $table->string('name');
            $table->integer('profileIconId');
            $table->double('revisionDate');
            $table->double('summonerLevel');
            $table->text('masteries')->nullable();
            $table->text('runes')->nullable();
            $table->text('championMastery')->nullable();
            $table->longText('league')->nullable();
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
        Schema::dropIfExists('summoners');
    }
}
