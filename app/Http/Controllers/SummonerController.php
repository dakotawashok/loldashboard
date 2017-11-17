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
    public $api;

    function __construct() {
        $this->api = new riotapi('NA1');
    }

    /**
     * Return all the data necessary for a single summoner on the front end
     * This includes:
     *      Summoner Object
     *      Matchlist Object
     *      Defined matches for each match in the matchlist object
     *      Masteries, Runes, Champion Masteries,
     *
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function getSummonerData($id) {
        $returnObject = [];         // The object that will be returned to the front end
        $summonerId = $id;

        // First, find the summoner data
        try {
            if (is_numeric($id)) {
                $summoner = Summoner::where('accountId', $id)->firstOrFail();
            } else {
                $summoner = Summoner::where('name', $id)->firstOrFail();
            }
        } catch (ModelNotFoundException $e) {
            $api = new riotapi('na1');

            if (is_numeric($id)) {
                $returnSummoner = $api->getSummoner($id, true);
            } else {
                $returnSummoner = $api->getSummonerByName($id);
            }

            $summoner = new Summoner;
            $summoner->id = $returnSummoner['id'];
            $summoner->accountId = $returnSummoner['accountId'];
            $summoner->name = $returnSummoner['name'];
            $summoner->profileIconId = $returnSummoner['profileIconId'];
            $summoner->summonerLevel = $returnSummoner['summonerLevel'];
            $summoner->revisionDate = (string)$returnSummoner['revisionDate'];

            $summoner->save();

            $this->assignMasteries($this->api, $summoner);
            $this->assignRunes($this->api, $summoner);
            $this->assignChampionMasteries($this->api, $summoner);
            $this->assignLeagues($this->api, $summoner);
        }
        $returnObject['summoner'] = $summoner;

        $summonerId = $summoner->id;
        $accountId = $summoner->accountId;

        // Then get the normal and ranked matchlist for that summoner
        // Normal matchlist first
        $normalParams = [
            'queue'=> [2,14,400,430],
            'season'=> [9],
            'endIndex'=> 20
        ];
        try {
            $matchListAggregate = MatchList::where('summonerId',$accountId)->get();
            if (count($matchListAggregate) == 0) {
                throw new ModelNotFoundException();
            } else {
                $found = false;
                // go through all the matchlists found, then for each matchlist, compare it to the params we were sent
                // if one of the matchlists matches the params sent in, return that, else we have to throw an exception
                foreach($matchListAggregate as $matchListEntry) {
                    // if it's the same season, same list type, and less than a day old
                    if ($matchListEntry->list_type == 'normal' && strtotime($matchListEntry->updated_at) > (strtotime('-1 day'))) {
                        $matchListObject = $matchListEntry;
                        $found = true;
                    }
                }
                // throw the exception since we couldn't find a matchlist that matches!
                if (!$found) {
                    throw new ModelNotFoundException();
                }
            }
        } catch (ModelNotFoundException $e) {
            $matches = $this->api->getMatchList($accountId, $normalParams);
            $matchListObject = new MatchList;
            $matchListObject->summonerId = $accountId;
            $matchListObject->season = '["9"]';
            $matchListObject->list_type = 'normal';
            $matchListObject->matches = json_encode($matches['matches']);
            $matchListObject->save();
        }

        $this->saveMatchListMatches($this->api, $matchListObject);

        $returnObject['normalMatchList'] = $matchListObject;
        $returnObject['normalDefinedMatchList'] = $this->getMatchListMatches($matchListObject);

        // Ranked matchlist and matches next
        $rankedParams = [
            'queue' => [410, 420, 440, 6, 41, 42],
            'season' => [9],
            'endIndex' => 20
        ];
        try {
            $matchListAggregate = MatchList::where('summonerId',$accountId)->get();
            if (count($matchListAggregate) == 0) {
                throw new ModelNotFoundException();
            } else {
                $found = false;
                // go through all the matchlists found, then for each matchlist, compare it to the params we were sent
                // if one of the matchlists matches the params sent in, return that, else we have to throw an exception
                foreach($matchListAggregate as $matchListEntry) {
                    // if it's the same season, same list type, and less than a day old
                    if ($matchListEntry->list_type == 'ranked' && strtotime($matchListEntry->updated_at) > (strtotime('-1 day'))) {
                        $matchListObject = $matchListEntry;
                        $found = true;
                    }
                }
                // throw the exception since we couldn't find a matchlist that matches!
                if (!$found) {
                    throw new ModelNotFoundException();
                }
            }
        } catch (ModelNotFoundException $e) {
            $matches = $this->api->getMatchList($accountId, $rankedParams);
            $matchListObject = new MatchList;
            $matchListObject->summonerId = $accountId;
            $matchListObject->season = '["9"]';
            $matchListObject->list_type = 'ranked';
            $matchListObject->matches = json_encode($matches['matches']);
            $matchListObject->save();
        }

        $this->saveMatchListMatches($this->api, $matchListObject);

        $returnObject['rankedMatchList'] = $matchListObject;
        $returnObject['rankedDefinedMatchList'] = $this->getMatchListMatches($matchListObject);

        return response()->json(json_encode($returnObject));
    }

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
            if (is_numeric($id)) {
                $returnSummoner = $this->api->getSummoner($id);
            } else {
                $returnSummoner = $this->api->getSummonerByName($id);
            }

            $summoner = new Summoner;
            $summoner->id = $returnSummoner['id'];
            $summoner->accountId = $returnSummoner['accountId'];
            $summoner->name = $returnSummoner['name'];
            $summoner->profileIconId = $returnSummoner['profileIconId'];
            $summoner->summonerLevel = $returnSummoner['summonerLevel'];
            $summoner->revisionDate = $returnSummoner['revisionDate'];

            $summoner->save();

            $this->assignMasteries($this->api, $summoner);
            $this->assignRunes($this->api, $summoner);
            $this->assignChampionMasteries($this->api, $summoner);
            $this->assignLeagues($this->api, $summoner);

            $response = response()->json(json_encode($summoner));

            return $response;
        }

    }


    /**
     * Query the API for new ranked stats, save them to the summoner with the specified account id, then return the new
     * ranked stats
     *
     *
     * @param  string|int  $accountId
     * @return \Illuminate\Http\Response
     */
    public function refreshRankedStats($accountId) {
        try {
            $summoner = Summoner::where('accountId', $accountId)->firstOrFail();
            $this->assignLeagues($this->api, $summoner);

            return response()->json($summoner->league);
        } catch (ModelNotFoundException $e) {
            $returnObject = ['error' => 'That account wasn\'t found in our database...', 'error_e' => $e];
            return response()->json(json_encode($returnObject));
        }
    }


    public function getMatchList(Request  $request, $summonerId) {
        $matchlistType = $request->input('matchlistType');  // get the matchlisttype from the post request
        $params = $request->input('params');                // get the params from the post request
        parse_str($params, $parsedParams);                       // parse the request string into a usable array
        $parsedParams['queue'] = str_getcsv($parsedParams['queue']);        // turn the csv string of queues into an array
        $parsedParams['season'] = str_getcsv($parsedParams['season']);        // turn the csv string of queues into an array

        try {
            $matchListAggregate = MatchList::where('summonerId',$summonerId)->get();
            if (count($matchListAggregate) == 0) {
                throw new ModelNotFoundException();
            } else {
                $found = false;
                // go through all the matchlists found, then for each matchlist, compare it to the params we were sent
                // if one of the matchlists matches the params sent in, return that, else we have to throw an exception
                foreach($matchListAggregate as $matchListEntry) {
                    // if it's the same season, same list type, and less than a day old
                    if ($matchListEntry->season == json_encode($parsedParams['season']) &&
                        $matchListEntry->list_type == $matchlistType &&
                        strtotime($matchListEntry->updated_at) > (strtotime('-1 day'))) {
                            $matchesObject = $matchListEntry;
                            $found = true;
                    }
                }
                // throw the exception since we couldn't find a matchlist that matches!
                if (!$found) {
                    throw new ModelNotFoundException();
                }
            }
        } catch (ModelNotFoundException $e) {
            $matches = $this->api->getMatchList($summonerId, $parsedParams);
            $matchesObject = new MatchList;
            $matchesObject->summonerId = $summonerId;
            $matchesObject->season = json_encode($parsedParams['season']);
            $matchesObject->list_type = $matchlistType;
            $matchesObject->matches = json_encode($matches['matches']);
            $matchesObject->save();
        }
//        echo "<pre>".print_r($matchesObject,true)."</pre>";
        if ($matchlistType == 'normal') {
            $this->createNormalMatchData($summonerId);
        } else if ($matchlistType == 'ranked') {
            $this->createRankedMatchData($summonerId);
        }

        $matchesDefined = $this->getMatchListMatches($matchesObject);
        $returnObject = ['matches' => $matchesObject, 'matches_defined' => $matchesDefined];

        $response = response()->json(json_encode($returnObject));
        return $response;
    }
    private function saveMatchListMatches(&$api, $matches) {
        // Now we have to go through each part of the matchlist and see if the match is in our database, if it isn't go find it...
        $matches = json_decode($matches['matches'], true);
        forEach($matches as $match_entry) {
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
                    $newParticipant->masteries = (isset($participant['masteries']) ? json_encode($participant['masteries']) : '');
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

    private function getMatchListMatches($matches) {
        $matchArray = [];
        $matches = json_decode($matches);
        $matches = json_decode($matches->matches);
        forEach($matches as $match_entry) {
            try {
                $matchFromDatabase = Match::where('gameId', (string)$match_entry->gameId)->firstOrFail();
                $matchFromDatabase->matchTeams = $matchFromDatabase->teams;
                $matchFromDatabase->matchParticipants = $matchFromDatabase->participants;
                $matchFromDatabase->MatchParticipantIdentities = $matchFromDatabase->participantIdentities;
                array_push($matchArray, $matchFromDatabase);
            } catch (ModelNotFoundException $e) {

            }
        }
        return $matchArray;
    }
    private function createNormalMatchData($summonerId, $params = null) {
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

        $matches = $this->api->getMatchList($summonerId, $normalParams);
        $matchesObject = new MatchList;
        $matchesObject->summonerId = $summonerId;
        $matchesObject->season = 9;
        $matchesObject->list_type = 'normal';
        $matchesObject->matches = json_encode($matches['matches']);
        $matchesObject->save();

        $this->saveMatchListMatches($this->api, $matches);
    }
    private function createRankedMatchData($summonerId, $params = null) {
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

        $matches = $this->api->getMatchList($summonerId, $rankedParams);
        $matchesObject = new MatchList;
        $matchesObject->summonerId = $summonerId;
        $matchesObject->season = 9;
        $matchesObject->list_type = 'ranked';
        $matchesObject->matches = json_encode($matches['matches']);
        $matchesObject->save();

        $this->saveMatchListMatches($this->api, $matches);
    }

    private function assignMasteries(&$api, &$summoner) {
        $masteries = $api->getMasteries($summoner->id);
        $summoner->masteries = json_encode($masteries);
        $summoner->save();
    }
    private function assignRunes(&$api, &$summoner) {
        $runes = $api->getRunes($summoner->id);
        $summoner->runes = json_encode($runes);
        $summoner->save();
    }
    private function assignChampionMasteries(&$api, &$summoner) {
        $championMastery = $api->getChampionMastery($summoner->id);
        $summoner->championMastery = json_encode($championMastery);
        $summoner->save();
    }
    private function assignLeagues(&$api, &$summoner) {
        $league = $api->getLeague($summoner->id);
        $summoner->league = json_encode($league);
        $summoner->save();
    }
}
