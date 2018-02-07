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

require_once '../app/Providers/riotapi/php-riot-api.php';
require_once '../app/Providers/riotapi/FileSystemCache.php';
use riotapi;
use FileSystemCache;

class MatchController extends Controller
{
    public $api;
    public $normalParamsList = [
        'queue'=> [2,14,400,430],
        'season'=> [9,10,11],
        'endIndex'=> 20
    ];
    public $rankedParamsList = [
        'queue' => [410, 420, 440, 6, 41, 42],
        'season' => [9,10,11],
        'endIndex' => 20
    ];
    public $otherParamsList = [
        'queue'=> [70, 72, 73, 75, 76, 78, 83, 98, 100, 310, 313, 317, 325, 450, 460, 470, 600, 610, 900, 910, 920, 940, 950, 960, 980, 990, 1000, 1010],
        'season'=> [9,10,11],
        'endIndex'=> 20
    ];

    function __construct() {
        $this->api = new riotapi('NA1', new FileSystemCache('cache/'));
    }

    public function getMatchById($matchId) {
        $returnObject = [];

        try {
            $matchFromDatabase = Match::where('gameId', (string)$matchId)->firstOrFail();
            $matchFromDatabase->matchTeams = $matchFromDatabase->teams;
            $matchFromDatabase->matchParticipants = $matchFromDatabase->participants;
            $matchFromDatabase->MatchParticipantIdentities = $matchFromDatabase->participantIdentities;
        } catch (ModelNotFoundException $e) {
            return response()->json(json_encode('Error: match not found in database'));
        }

        return response()->json(json_encode($matchFromDatabase));
    }

    public function getMatchList(Request $request, $identifier) {
        $matchListObject = $this->getMatchListObject($request, $identifier);

        return response()->json(json_encode($matchListObject));
    }

    public function getDefinedMatchList(Request $request, $identifier) {
        $matchListObject = $this->getMatchListObject($request, $identifier);

        $definedMatchListObject = $matchListObject->getMatchListMatches();
        return response()->json(json_encode($definedMatchListObject));
    }

    private function getMatchListObject(Request $request, $identifier) {
        $matchListType = $request->input('matchListType', 'normal');
        $accountId = $request->input('accountId');
        $params = $request->input('params', 'null');
        if ($params != 'null') {
            $params = json_decode($params);
        } else {
            switch ($matchListType) {
                case 'ranked' :
                    $params = $this->rankedParamsList;
                    break;
                case 'normal' :
                    $params = $this->normalParamsList;
                    break;
                case 'other' :
                    $params = $this->otherParamsList;
                    break;
                default :
                    $params = $this->rankedParamsList;
                    break;
            }
        }

        try {
            $matchListObject = MatchList::where('summonerId', $accountId)->where('list_type', $matchListType)->firstOrFail();
            $matchListObject->determineNeedForUpdating();
        } catch (ModelNotFoundException $e) {
            $matchListObject = new MatchList;
            $matchListObject->assignDataFromAPI($accountId, $matchListType, $params);
        }

        return $matchListObject;
    }

    public function log($message) {
        $file = './log.txt';
        $message = file_get_contents($file) . "\n" . json_encode($message);
        file_put_contents($file, $message);
    }
}
