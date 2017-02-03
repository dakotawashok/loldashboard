<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChampionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('champions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('allytips');
            $table->string('enemytips');
            $table->string('image');
            $table->string('info');
            $table->string('key');
            $table->string('lore');
            $table->string('name');
            $table->string('partype');
            $table->string('passive');
            $table->string('recommended');
            $table->string('skins');
            $table->string('spells');
            $table->string('stats');
            $table->string('tags');
            $table->string('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('champions');
    }
}
