<?php

namespace App\Http\Controllers;

use App\Match;
use App\MatchList;
use App\MatchTeam;
use App\MatchParticipantIdentities;
use App\MatchParticipant;
use App\RankedStats;
use App\RecentGame;
use App\RecentGamesList;
use App\Summoner;
use App\SummaryStats;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Exception\InvalidArgumentException;

require '../vendor/riotapi/php-riot-api.php';
use riotapi;

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
            $api = new riotapi('na1');

            if (is_numeric($id)) {
                $returnSummoner = $api->getSummoner($id);
            } else {
                $returnSummoner = $api->getSummonerByName($id);
            }

            $summoner = new Summoner;
            $summoner->id = $returnSummoner['id'];
            $summoner->accountId = $returnSummoner['accountId'];
            $summoner->name = $returnSummoner['name'];
            $summoner->profileIconId = $returnSummoner['profileIconId'];
            $summoner->summonerLevel = $returnSummoner['summonerLevel'];
            $summoner->revisionDate = $returnSummoner['revisionDate'];

            $summoner->save();

            $response = response()->json(json_encode($summoner));

            return $response;
        }

    }

    /**
     * Display the statistics for a specified Summoner for the given year
     *
     * @param  string|int  $id
     * @param  int  $season
     * @return \Illuminate\Http\Response
     */
    public function getSummonerData($accountId, $type, $season) {
        $api = new riotapi('NA1');

        $summoner = Summoner::where('accountId', $accountId)->firstOrFail();

        /**
         *  Summary data includes Runes, Masteries, champion masteries, and leagues?
         */
        if ($type == 'summary') {
            $this->assignMasteries($api, $summoner);
            $this->assignRunes($api, $summoner);
            $this->assignChampionMasteries($api, $summoner);
            $this->assignLeagues($api, $summoner);

            $response = response()->json(json_encode($summoner));
            return $response;
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
            $api = new riotapi('na1');

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
            $api = new riotapi('na1');

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
            $api = new riotapi('na1');

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
            $api = new riotapi('na1');

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

    private function saveMatchListMatches(&$api, $matches) {
        // Now we have to go through each part of the matchlist and see if the match is in our database, if it isn't go find it...
        forEach($matches['matches'] as $match_entry) {
            try {
                $matchFromDatabase = Match::where('gameId', (string)$match_entry['gameId'])->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $apiMatch = $api->getMatch($match_entry['gameId'], false);

                $newMatch = new Match;
                $newMatch->seasonId = (isset($apiMatch['seasonId']) ? $apiMatch['seasonId'] : '');
                $newMatch->queueId = (isset($apiMatch['queueId']) ? $apiMatch['queueId'] : '');
                $newMatch->gameId = (string)$match_entry['gameId'];
                // make the participant identities
                forEach($apiMatch['participantIdentities'] as $pId) {
                    try {
                        $findParticipantIdentity = MatchParticipantIdentities::where('matchId', (string)$match_entry['gameId'])->where('accountId', $pId['player']['currentAccountId'])->firstOrFail();
                    } catch (ModelNotFoundException $pie) {
                        $newPId = new MatchParticipantIdentities;
                        $newPId->matchId = (string)$match_entry['gameId'];
                        $newPId->currentPlatformId = (isset($pId['player']['currentPlatformId']) ? $pId['player']['currentPlatformId'] : '');
                        $newPId->summonerName = $pId['player']['summonerName'];
                        $newPId->matchHistoryUri = (isset($pId['player']['matchHistoryUri']) ? $pId['player']['matchHistoryUri'] : '');
                        $newPId->platformId = (isset($pId['player']['platformId']) ? $pId['player']['platformId'] : '');
                        $newPId->currentAccountId = $pId['player']['currentAccountId'];
                        $newPId->profileIcon = (isset($pId['player']['profileIcon']));
                        $newPId->summonerId = $pId['player']['summonerId'];
                        $newPId->accountId = $pId['player']['accountId'];
                        $newPId->participantId = (isset($pId['participantId']) ? $pId['participantId'] : '');
                        $newPId->save();
                    }
                }

                $newMatch->gameVersion = (isset($apiMatch['gameVersion']) ? $apiMatch['gameVersion'] : '');
                $newMatch->platformId = (isset($apiMatch['platformId']) ? $apiMatch['platformId'] : '');
                $newMatch->gameMode = $apiMatch['gameMode'];
                $newMatch->mapId = $apiMatch['mapId'];
                $newMatch->gameType = $apiMatch['gameType'];
                // make the teams
                forEach($apiMatch['teams'] as $team) {
                    $newTeam = new MatchTeam;
                    $newTeam->matchId = (string)$match_entry['gameId'];
                    $newTeam->firstDragon = (isset($team['firstDragon']) ? $team['firstDragon'] : '');
                    $newTeam->bans = json_encode($team['bans']);
                    $newTeam->win = $team['win'];
                    $newTeam->firstRiftHerald = (isset($team['firstRiftHerald']) ? $team['firstRiftHerald'] : '');
                    $newTeam->firstBaron = (isset($team['firstBaron']) ? $team['firstBaron'] : '');
                    $newTeam->baronKills = (isset($team['baronKills']) ? $team['baronKills'] : '');
                    $newTeam->riftHeraldKills = (isset($team['riftHeraldKills']) ? $team['riftHeraldKills'] : '');
                    $newTeam->firstBlood = $team['firstBlood'];
                    $newTeam->teamId = $team['teamId'];
                    $newTeam->firstTower = (isset($team['firstTower']) ? $team['firstTower'] : '');
                    $newTeam->vilemawKills = (isset($team['vilemawKills']) ? $team['vilemawKills'] : '');
                    $newTeam->inhibitorKills = (isset($team['inhibitorKills']) ? $team['inhibitorKills'] : '');
                    $newTeam->towerKills = (isset($team['towerKills']) ? $team['towerKills'] : '');
                    $newTeam->dominionVictoryScore = (isset($team['dominionVictoryScore']) ? $team['dominionVictoryScore'] : '');
                    $newTeam->dragonKills = (isset($team['dragonKills']) ? $team['dragonKills'] : '');
                    $newTeam->save();
                }
                // make the participants
                forEach($apiMatch['participants'] as $participant) {
                    $newParticipant = new MatchParticipant;
                    $newParticipant->matchId = (string)$match_entry['gameId'];
                    $newParticipant->stats = json_encode($participant['stats']);
                    $newParticipant->runes = (isset($participant['runes']) ? json_encode($participant['runes']) : '');
                    $newParticipant->masteries = json_encode($participant['masteries']);
                    $newParticipant->timeline = (isset($participant['timeline']) ? json_encode($participant['timeline']) : '');
                    $newParticipant->spell1Id = (isset($participant['spell1Id']) ? $participant['spell1Id'] : '');
                    $newParticipant->spell2Id = (isset($participant['spell2Id']) ? $participant['spell2Id'] : '');
                    $newParticipant->participantId = $participant['participantId'];
                    $newParticipant->highestAchievedSeasonTier = (isset($participant['highestAchievedSeasonTier']) ? $participant['highestAchievedSeasonTier'] : '');
                    $newParticipant->teamId = $participant['teamId'];
                    $newParticipant->championId = $participant['championId'];
                    $newParticipant->save();
                }
                $newMatch->gameDuration = $apiMatch['gameDuration'];
                $newMatch->gameCreation = (string)$apiMatch['gameCreation'];
                $newMatch->save();
            }
        }
    }

    private function createNormalMatchData($summonerId, $params = null) {
        $api = new riotapi('na1');
        // if the summoner isn't in here, maybe we should build some stats for them?
        // First lets get a list of regular matches I guess then get a list of ranked matches?
        if (isset($params)) {
            $normalParams = [
                'queue' => (isset($params['queue']) ? $params['queue'] : ''),
                'endTime' => (isset($params['endTime']) ? $params['endTime'] : ''),
                'beginIndex' => (isset($params['beginIndex']) ? $params['beginIndex'] : ''),
                'beginTime' => (isset($params['beginTime']) ? $params['beginTime'] : ''),
                'season' => (isset($params['season']) ? $params['season'] : ''),
                'champion' => (isset($params['champion']) ? $params['champion'] : ''),
                'endIndex' => (isset($params['endIndex']) ? $params['endIndex'] : ''),
            ];
        } else {
            $normalParams = [
                'queue'=> [2,14,400,430],
                'season'=> [9],
                'endIndex'=> 20
            ];
        }

        try {
            $matchListFromDatabase = MatchList::where('summonerId', $summonerId)->where('list_type', 'normal')->firstOrFail();
            if (strtotime($matchListFromDatabase->updated_at) < (strtotime('now') - 86400) || $overrideDateLimit) {
                $matches = $api->getMatchList($summonerId, $normalParams);
                $matchListFromDatabase->matches = json_encode($matches['matches']);
                $matchListFromDatabase->save();
            }
        } catch (ModelNotFoundException $e) {
            $matches = $api->getMatchList($summonerId, $normalParams);
            $matchesObject = new MatchList;
            $matchesObject->summonerId = $summonerId;
            $matchesObject->season = 9;
            $matchesObject->list_type = 'normal';
            $matchesObject->matches = json_encode($matches['matches']);
            $matchesObject->save();

            $this->saveMatchListMatches($api, $matches);
        }
    }

    private function createRankedMatchData($summonerId, $params = null) {
        $api = new riotapi('na1');
        if (isset($params)) {
            $rankedParams = [
                'queue' => (isset($params['queue']) ? $params['queue'] : ''),
                'endTime' => (isset($params['endTime']) ? $params['endTime'] : ''),
                'beginIndex' => (isset($params['beginIndex']) ? $params['beginIndex'] : ''),
                'beginTime' => (isset($params['beginTime']) ? $params['beginTime'] : ''),
                'season' => (isset($params['season']) ? $params['season'] : ''),
                'champion' => (isset($params['champion']) ? $params['champion'] : ''),
                'endIndex' => (isset($params['endIndex']) ? $params['endIndex'] : ''),
            ];
        } else {
            $rankedParams = [
                'queue' => [410, 420, 440, 6, 41, 42],
                'season' => [9]
            ];
        }

        $matches = $api->getMatchList($summonerId, $rankedParams);
        $matchesObject = new MatchList;
        $matchesObject->summonerId = $summonerId;
        $matchesObject->season = 9;
        $matchesObject->list_type = 'normal';
        $matchesObject->matches = json_encode($matches['matches']);
        $matchesObject->save();

        $this->saveMatchListMatches($api, $matches);
    }

    private function assignMasteries(&$api, &$summoner) {
        if (strlen($summoner->masteries) == 0) {
            $masteries = $api->getMasteries($summoner->id);
            $summoner->masteries = json_encode($masteries);
            $summoner->save();
        }
    }
    private function assignRunes(&$api, &$summoner) {
        if (strlen($summoner->runes) == 0) {
            $runes = $api->getRunes($summoner->id);
            $summoner->runes = json_encode($runes);
            $summoner->save();
        }
    }
    private function assignChampionMasteries(&$api, &$summoner) {
        if (strlen($summoner->championMastery) == 0) {
            $championMastery = $api->getChampionMastery($summoner->id);
            $summoner->championMastery = json_encode($championMastery);
            $summoner->save();
        }
    }
    private function assignLeagues(&$api, &$summoner) {
        if (strlen($summoner->league) == 0) {
            $league = $api->getLeague($summoner->id);
            $summoner->league = json_encode($league);
            $summoner->save();
        }
    }
}
