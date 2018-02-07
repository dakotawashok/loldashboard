<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

require_once '../app/Providers/riotapi/php-riot-api.php';
require_once '../app/Providers/riotapi/FileSystemCache.php';

use Illuminate\Database\Eloquent\ModelNotFoundException;
use riotapi;
use FileSystemCache;

class MatchList extends Model
{
    public $api;

    public function log($message) {
        $file = './log.txt';
        $message = file_get_contents($file) . "\n" . json_encode($message);
        file_put_contents($file, $message);
    }

    function __construct(array $attributes = [])
    {
        $this->api = new riotapi('NA1', new FileSystemCache('cache/'));
        parent::__construct($attributes);
    }

    public function assignDataFromAPI($id, $list_type, $params) {
        $matches = $this->api->getMatchList($id, $params);

        $this->summonerId = $id;
        $this->season = $params['season'];
        $this->list_type = $list_type;
        $this->matches = json_encode($matches['matches']);
        $this->save();
        $this->saveMatchListMatches();

        return $this;
    }

    public function determineNeedForUpdating() {
        // If it's older than a day, retrieve the newest match list from the api and resave it in the database
        if (strtotime($this->updated_at) < (strtotime('-1 day'))) {
            $matches = $this->api->getMatchList($id, $params);
            if (gettype($matches) == "object") {
                $this->matches = json_encode($matches->matches);
            } else {
                $this->matches = json_encode($matches['matches']);
            }
            $this->updated_at = $this->freshTimestampString();
            $this->save();
            $this->saveMatchListMatches();
            return true;
        } else {
            return false;
        }
    }

    public  function getMatchListMatches() {
    $matchArray = [];
    $matches = json_decode($this->matches);
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
    $this->formatDefinedMatchListForDelivery($matchArray);

    return $matchArray;
}

    private function saveMatchListMatches() {
        // Now we have to go through each part of the matchlist and see if the match is in our database, if it isn't go find it...
        $matches = json_decode($this->matches);
        forEach($matches as $match_entry) {
            try {
                $matchFromDatabase = Match::where('gameId', (string)$match_entry['gameId'])->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $apiMatch = $this->api->getMatch($match_entry['gameId'], false);

                $newMatch = new Match;
                $newMatch->seasonId = (isset($apiMatch['seasonId']) ? $apiMatch['seasonId'] : '');
                $newMatch->queueId = (isset($apiMatch['queueId']) ? $apiMatch['queueId'] : '');
                $newMatch->gameId = (string)$match_entry['gameId'];
                // make the participant identities
                forEach($apiMatch['participantIdentities'] as $pId) {
                    if ($pId['player']['accountId'] != 0) {
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
                    $newTeam->bans = (isset($team['bans']) ? json_encode($team['bans']) : '');
                    $newTeam->win = (isset($team['win']) ? $team['win'] : '');
                    $newTeam->firstRiftHerald = (isset($team['firstRiftHerald']) ? $team['firstRiftHerald'] : '');
                    $newTeam->firstBaron = (isset($team['firstBaron']) ? $team['firstBaron'] : '');
                    $newTeam->firstInhibitor = (isset($team['firstInhibitor']) ? $team['firstInhibitor'] : '');
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

    private function formatDefinedMatchListForDelivery(&$definedMatchListObject) {

        // go through each of the match lists participants and remove unnecessary entries in stats and completely remove the timeline
        foreach($definedMatchListObject as $matchIndex => $match) {
            foreach($match->matchParticipants as $participantIndex => $participant) {
                // unset the timeline, runes, and masteries for each match participant
                unset($definedMatchListObject[$matchIndex]->matchParticipants[$participantIndex]->timeline);
                unset($definedMatchListObject[$matchIndex]->matchParticipants[$participantIndex]->runes);
                unset($definedMatchListObject[$matchIndex]->matchParticipants[$participantIndex]->masteries);

                // make a new stats thing so that we can set it and remove the unecessary stuff
                $tempStats = [];
                $parsedStats = json_decode($participant->stats);
                $tempStats['win'] = $parsedStats->win;
                $tempStats['item0'] = $parsedStats->item0;
                $tempStats['item1'] = $parsedStats->item1;
                $tempStats['item2'] = $parsedStats->item2;
                $tempStats['item3'] = $parsedStats->item3;
                $tempStats['item4'] = $parsedStats->item4;
                $tempStats['item5'] = $parsedStats->item5;
                $tempStats['item6'] = $parsedStats->item6;
                $tempStats['kills'] = $parsedStats->kills;
                $tempStats['deaths'] = $parsedStats->deaths;
                $tempStats['assists'] = $parsedStats->assists;
                $tempStats = json_encode($tempStats);
                $definedMatchListObject[$matchIndex]->matchParticipants[$participantIndex]->stats = $tempStats;
            }
        }
    }
}
