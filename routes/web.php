<?php

use LeagueWrap\Api;
use App\Http\Controllers\SummonerController;
use App\Http\Controllers\MatchController;

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

Route::get('summoner/{id}/allData', 'SummonerController@getSummonerData');

Route::get('summoner/{accountId}/refreshRankedStats', 'SummonerController@refreshRankedStats');

Route::get('summoner/{accountId}/{type}/data/{season}', 'SummonerController@getSummonerData');

Route::get('summoner/{identifier}/match/{matchId}', 'SummonerController@getMatchData');

Route::get('summoner/{identifier}/recentgames', 'SummonerController@getRecentGames');

Route::get('match/{matchId}', 'MatchController@getMatchById');

Route::post('summoner/{identifier}/matchlist', 'MatchController@getMatchList');
