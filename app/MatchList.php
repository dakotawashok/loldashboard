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

    public function assignData($id, $list_type, $params) {
        try {
            $matchList = $this->where('summonerId',$id, 'list_type', $list_type)->firstOrFail();

            // If it's older than a day, retrieve the newest match list from the api and resave it in the database
            if (strtotime($matchList->updated_at) < (strtotime('-1 day'))) {
                $matches = $this->api->getMatchList($id, $params);
                if (gettype($matches) == "object") {
                    $matchList->matches = json_encode($matches->matches);
                } else {
                    $matchList->matches = json_encode($matches['matches']);
                }
                $matchList->updated_at = $matchList->freshTimestampString();
                $matchList->save();
                $this->saveMatchListMatches($this->api, $matchList);
            }

            $this->id = $matchList['id'];
            $this->summonerId = $matchList['summonerId'];
            $this->season = $matchList['season'];
            $this->list_type = $matchList['list_type'];
            $this->matches = $matchList['matches'];
        } catch (ModelNotFoundException $e) {
            $matches = $this->api->getMatchList($id, $params);
            $matchList = new MatchList;
            $matchList->summonerId = $id;
            $matchList->season = '["9"]';
            $matchList->list_type = $list_type;
            $matchList->matches = json_encode($matches['matches']);
            $matchList->save();
            $this->saveMatchListMatches($matchList);
        }
    }

    private function saveMatchListMatches($matches) {
        // Now we have to go through each part of the matchlist and see if the match is in our database, if it isn't go find it...
        $matches = json_decode($matches['matches'], true);
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
}
