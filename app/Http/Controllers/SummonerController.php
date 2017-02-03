<?php

namespace App\Http\Controllers;

use App\RankedStats;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Summoner;
use App\SummaryStats;

use LeagueWrap\Api;

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
}
