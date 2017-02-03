<?php

use LeagueWrap\Api;
use App\Http\Controllers\SummonerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
});

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::get('summoner/{id}', 'SummonerController@getSummoner');

Route::get('summoner/{identifier}/{type}/data/{year}', 'SummonerController@getSummonerData');

Route::get('summoner/{identifier}/matchlist', function($identifier) {
    $api = new Api('RGAPI-0b8ccaa3-1745-41be-ae90-90a60dc315ef');

    $summoner = $api->summoner();

    $returnSummoner = $summoner->info($identifier);

    $matchlist = $api->matchlist()->matchlist($returnSummoner)->raw();

    return $matchlist;
});

Route::get('summoner/{identifier}/match/{matchId}', function($identifier, $matchId) {
    $api = new Api('RGAPI-0b8ccaa3-1745-41be-ae90-90a60dc315ef');

    $summoner = $api->summoner();
    $returnSummoner = $summoner->info($identifier);

    $matchApi = $api->match();
    $match = $matchApi->match($matchId)->raw();

    return $match;
});

Route::get('summoner/{identifier}/recentgames', function($identifier) {
    $api = new Api('RGAPI-0b8ccaa3-1745-41be-ae90-90a60dc315ef');

    $games = $api->game()->recent($identifier)->raw();

    return $games;
});

Route::get('champion/{identifier}', function($identifier) {
    $api = new Api('RGAPI-0b8ccaa3-1745-41be-ae90-90a60dc315ef');

    $api->attachStaticData();
    $champion = $api->champion()->championById($identifier)->raw();

    return $champion;
});