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

require '../app/Providers/riotapi/php-riot-api.php';
require '../app/Providers/riotapi/FileSystemCache.php';
use riotapi;
use FileSystemCache;

class MatchController extends Controller
{
    public $api;

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

    public function log($message) {
        $file = './log.txt';
        $message = file_get_contents($file) . "\n" . json_encode($message);
        file_put_contents($file, $message);
    }
}
