<?php

namespace App\Http\Controllers;

use App\Match;
use App\MatchList;
use App\RankedStats;
use App\RecentGame;
use App\RecentGamesList;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Summoner;
use App\SummaryStats;

use Illuminate\Validation\ValidationException;
use LeagueWrap\Api;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class SummonerController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function getSummoner($id)
    {
        try {
            if (is_numeric($id)) {
                $summoner = Summoner::findOrFail($id);
            } else {
                $summoner = Summoner::where('name', $id)->firstOrFail();
            }

            return response()->json(json_encode($summoner));

        } catch (ModelNotFoundException $e) {
            $api = new Api('RGAPI-0b8ccaa3-1745-41be-ae90-90a60dc315ef');

            $returnSummoner = $api->summoner()->info($id);

            $summoner = new Summoner;
            $summoner->id = $returnSummoner->id;
            $summoner->name = $returnSummoner->name;
            $summoner->profileIconId = $returnSummoner->profileIconId;
            $summoner->summonerLevel = $returnSummoner->summonerLevel;
            $summoner->revisionDate = $returnSummoner->revisionDate;

            $summoner->save();

            $response = response()->json(json_encode($summoner));

            return $response;
        }

    }

    /**
     * Display the statistics for a specified Summoner for the given year
     *
     * @param  string|int  $id
     * @param  int  $year
     * @return \Illuminate\Http\Response
     */
    public function getSummonerData($id, $type, $year) {
        switch($year) {
            case 2013: $year = "SEASON3"; break;
            case 2014: $year = "SEASON2014"; break;
            case 2015: $year = "SEASON2015"; break;
            case 2016: $year = "SEASON2016"; break;
            case 2017: $year = "SEASON2017"; break;
            default: $year = "SEASON2016";
        }

        try {
            if ($type == "summary") {
                $data = SummaryStats::where('id', $id)->where('season', $year)->firstOrFail();
            } else {
                $data = RankedStats::where('id', $id)->where('season', $year)->firstOrFail();
            }

            return response()->json($data->data);
        } catch (ModelNotFoundException $e) {
            $api = new Api('RGAPI-0b8ccaa3-1745-41be-ae90-90a60dc315ef');
            $statsApi = $api->stats();
            $statsApi->setSeason($year);

            if ($type == "summary") {
                $returnStats = $statsApi->summary($id)->raw();

                $stats = new SummaryStats();
                $stats->id = $id;
                $stats->season = $year;
                $stats->data = json_encode($returnStats);
                $stats->save();
            } else {

                $returnStats = $statsApi->ranked($id)->raw();

                $stats = new RankedStats();
                $stats->id = $id;
                $stats->season = $year;
                $stats->data = json_encode($returnStats);
                $stats->save();
            }

            return response()->json(json_encode($returnStats));
        }
    }

    public function getMatchList($id, $season = null, $rankedQueue = null) {
        try {
            if ($rankedQueue == null && $season == null) {
                $matchList = MatchList::where('summonerId', $id)->firstOrFail();
            } else if ($rankedQueue != null && $season == null) {
                $matchList = MatchList::where('summonerId', $id)->where('rankedQueue', $rankedQueue)->firstOrFail();
            } else if ($rankedQueue == null && $season != null) {
                $matchList = MatchList::where('summonerId', $id)->where('season', $season)->firstOrFail();
            } else {
                $matchList = MatchList::where('summonerId', $id)->where('rankedQueue', $rankedQueue)->where('season', $season)->firstOrFail();
            }

            return response()->json($matchList->matches);

        } catch (ModelNotFoundException $e) {
            $api = new Api('RGAPI-0b8ccaa3-1745-41be-ae90-90a60dc315ef');

            if ($rankedQueue == null && $season == null) {
                $matchlist = $api->matchlist()->matchlist($id);
            } else if ($rankedQueue != null && $season == null) {
                $matchlist = $api->matchlist()->matchlist($id, $rankedQueue);
            } else if ($rankedQueue == null && $season != null) {
                // temporary work around for bug with RIOT Api where seasons marked SEASON2017 or PRESEASON2017 don't work
                if ($season == "SEASON2017" || $season == "PRESEASON2017") {
                    $matchlist = $api->matchlist()->matchlist($id, null, null, null, null, null, $beginTime = 1481108400000, $endTime = null);
                    $i = 0;
                    foreach ($matchlist as $match) {
                        if ($match->season != $season) {
                            unset($matchlist[$i]);
                        }
                        $i++;
                    }
                } else {
                    $matchlist = $api->matchlist()->matchlist($id, null, $season);
                }
            } else {
                $matchlist = $api->matchlist()->matchlist($id, $rankedQueue, $season);
            }

            foreach($matchlist as $match) {
                $tempMatch = new Match;
                $tempMatch->region = $match->region;
                $tempMatch->platformId = $match->platformId;
                $tempMatch->matchId = strval($match->matchId);
                $tempMatch->champion = $match->champion;
                $tempMatch->queue = $match->queue;
                $tempMatch->timestamp = strval($match->timestamp);
                $tempMatch->lane = $match->lane;
                $tempMatch->role = $match->role;
                $tempMatch->season = $match->season;
                $tempMatch->summonerId = $id;

                $tempMatch->save();
            }

            $matchlist = $matchlist->raw();

            $tempMatchList = new MatchList;
            $tempMatchList->summonerId = $id;
            $tempMatchList->season = $season;
            $tempMatchList->rankedQueue = $rankedQueue;

            $tempMatchList->matches = json_encode($matchlist);
            $tempMatchList->save();

            $returnMatchList = json_encode($matchlist);

            return response()->json($returnMatchList);
        }
    }

    public function getMatchData($id, $matchId) {
        try {
            $match = Match::where('matchId', $matchId)->where('summonerId', $id)->firstOrFail();
            if (is_null($match->data)) {
                throw new InvalidArgumentException;
            }

            return response()->json($match->data);
        } catch(ModelNotFoundException $e) {
            return response($e);

        } catch (InvalidArgumentException $e) {
            $api = new Api('RGAPI-0b8ccaa3-1745-41be-ae90-90a60dc315ef');

            $match = $api->match()->match($matchId)->raw();

            $tempMatch = Match::where('matchId', $matchId)->where('summonerId', $id)->firstOrFail();
            $tempMatch->data = json_encode($match);
            $tempMatch->save();

            return response()->json(json_encode($match));
        }
    }

    public function getRecentGames($id) {
        try {
            $recentGamesList = RecentGamesList::where('summonerId', $id)->firstOrFail();
            $now = time();
            if ($recentGamesList->updated_at->timestamp < ($now - (60 * 60))) {
                throw new InvalidArgumentException;
            } else {
                return response()->json($recentGamesList->games);
            }
        } catch (ModelNotFoundException $e) {
            $api = new Api('RGAPI-0b8ccaa3-1745-41be-ae90-90a60dc315ef');

            $recentGamesList = $api->game()->recent($id);

            foreach($recentGamesList->games as $recentGame) {
                $recentGame = $recentGame->raw();

                $tempRecentGame = new RecentGame;
                $tempRecentGame->championId = $recentGame['championId'];
                $tempRecentGame->summonerId = strval($id);
                $tempRecentGame->gameId = strval($recentGame['gameId']);
                $tempRecentGame->fellowPlayers = json_encode($recentGame['fellowPlayers']);
                $tempRecentGame->spell1 = $recentGame['spell1'];
                $tempRecentGame->spell2 = $recentGame['spell2'];
                $tempRecentGame->stats = json_encode($recentGame['stats']);
                $tempRecentGame->mapId = $recentGame['mapId'];
                $tempRecentGame->invalid = $recentGame['invalid'];
                $tempRecentGame->gameMode = $recentGame['gameMode'];
                $tempRecentGame->level = $recentGame['level'];
                $tempRecentGame->ipEarned = $recentGame['ipEarned'];
                $tempRecentGame->gameType = $recentGame['gameType'];
                $tempRecentGame->subType = $recentGame['subType'];
                $tempRecentGame->teamId = $recentGame['teamId'];
                $tempRecentGame->createDate = strval($recentGame['createDate']);

                $tempRecentGame->save();
            }

            $recentGamesList = $recentGamesList->raw();

            $tempGamesList = new RecentGamesList;
            $tempGamesList->games = json_encode($recentGamesList['games']);
            $tempGamesList->summonerId = $id;
            $tempGamesList->save();

            return response()->json(json_encode($recentGamesList['games']));
        } catch (InvalidArgumentException $e) {
            // This branch is called when there is a recent games list already in the database, but it more than a day old.
            // It will go through the new gameList from the api, check to see if each recentgame is in the database, and if it's
            // not, put it there. Then it will update the recentgameslist in our database and return a response
            $api = new Api('RGAPI-0b8ccaa3-1745-41be-ae90-90a60dc315ef');

            $apiGamesList = $api->game()->recent($id);
            foreach($apiGamesList->games as $recentGame) {
                try {
                     $tempRecentGame = RecentGame::where('summonerId', strval($id))->where('gameId', strval($recentGame->gameId))->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    $recentGame = $recentGame->raw();

                    $tempRecentGame = new RecentGame;
                    $tempRecentGame->championId = $recentGame['championId'];
                    $tempRecentGame->summonerId = strval($id);
                    $tempRecentGame->gameId = strval($recentGame['gameId']);
                    $tempRecentGame->fellowPlayers = json_encode($recentGame['fellowPlayers']);
                    $tempRecentGame->spell1 = $recentGame['spell1'];
                    $tempRecentGame->spell2 = $recentGame['spell2'];
                    $tempRecentGame->stats = json_encode($recentGame['stats']);
                    $tempRecentGame->mapId = $recentGame['mapId'];
                    $tempRecentGame->invalid = $recentGame['invalid'];
                    $tempRecentGame->gameMode = $recentGame['gameMode'];
                    $tempRecentGame->level = $recentGame['level'];
                    $tempRecentGame->ipEarned = $recentGame['ipEarned'];
                    $tempRecentGame->gameType = $recentGame['gameType'];
                    $tempRecentGame->subType = $recentGame['subType'];
                    $tempRecentGame->teamId = $recentGame['teamId'];
                    $tempRecentGame->createDate = strval($recentGame['createDate']);

                    $tempRecentGame->save();
                }
            }

            $apiGamesList = $apiGamesList->raw();

            $recentGamesList = RecentGamesList::where('summonerId', $id)->firstOrFail();
            $recentGamesList->games = json_encode($apiGamesList['games']);
            $recentGamesList->save();

            return response()->json(json_encode($apiGamesList['games']));
        }
    }
}
