<?php
/*

PHP Riot API 
Kevin Ohashi (http://kevinohashi.com)
http://github.com/kevinohashi/php-riot-api


The MIT License (MIT)

Copyright (c) 2013 Kevin Ohashi

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

require_once('CacheInterface.php');
require_once('NullCache.php');
require_once('RateLimitHandler.php');
require_once('RateLimitSleeper.php');

class riotapi {
    const API_URL_PLATFORM_3 = "https://{platform}.api.riotgames.com/lol/platform/v3/";
    const API_URL_CHAMPION_MASTERY_3 = "https://{platform}.api.riotgames.com/lol/champion-mastery/v3/";
    const API_URL_SPECTATOR_3 = 'https://{platform}.api.riotgames.com/lol/spectator/v3/';
    const API_URL_STATIC_3 = 'https://{platform}.api.riotgames.com/lol/static-data/v3/';
    const API_URL_MATCH_3 = 'https://{platform}.api.riotgames.com/lol/match/v3/';
    const API_URL_LEAGUE_3 = 'https://{platform}.api.riotgames.com/lol/league/v3/';
    const API_URL_SUMMONER_3 = 'https://{platform}.api.riotgames.com/lol/summoner/v3/';

    const HTTP_OK = 200;
	const HTTP_RATE_LIMIT = 429;

    // Rate limit for 10 minutes
    const LONG_LIMIT_INTERVAL = 120;
    const RATE_LIMIT_LONG = 100;

    // Rate limit for 10 seconds'
    const SHORT_LIMIT_INTERVAL = 1;
    const RATE_LIMIT_SHORT = 20;

    // Cache variables
    const CACHE_LIFETIME_MINUTES = 60;
    private $cache;

    private $PLATFORM;
    private $API_KEY;
    //variable to retrieve last response code
    private $responseCode;


    private static $errorCodes = array(0   => 'NO_RESPONSE',
        400 => 'BAD_REQUEST',
        401 => 'UNAUTHORIZED',
        404 => 'NOT_FOUND',
        429 => 'RATE_LIMIT_EXCEEDED',
        500 => 'SERVER_ERROR',
        503 => 'UNAVAILABLE');




    // Whether or not you want returned queries to be JSON or decoded JSON.
    // honestly I think this should be a public variable initalized in the constructor, but the style before me seems definitely to use const's.
    // Remove this commit if you want. - Ahubers
    const DECODE_ENABLED = TRUE;

	public function __construct($platform, CacheInterface $cache = null, RateLimitHandler $rateLimitHandler = null)
	{
        $this->PLATFORM = $platform;
        $this->API_KEY = env('RIOT_API_KEY');

		// if a cache and rate limiter weren't provided, then we'll just use these default ones
		$this->cache = $cache = $cache !== null ? $cache : new NullCache();
		$this->rateLimitHandler = $rateLimitHandler !== null ? $rateLimitHandler : new RateLimitSleeper();
	}

    //Returns all champion information.
    //Set $free at true to get only free champions.
    public function getChampion($free = false){
        $call = 'champions';

        if($free)
            $call  .= '?freeToPlay=true';

        //add API URL to the call
        $call = self::API_URL_PLATFORM_3 . $call;

        return $this->request($call);
    }

    //Returns all free champion information.
    public function getFreeChampions(){
        $call = 'champions?freeToPlay=true';

        //add API URL to the call
        $call = self::API_URL_PLATFORM_3 . $call;

        return $this->request($call);
    }

    //Returns all champions mastery for a player
    public function getChampionMastery($id, $championId = false){

        $call = self::API_URL_CHAMPION_MASTERY_3 . 'champion-masteries/by-summoner/' . $id ;

        if($championId)
            $call .= "/by-champion/" . $championId;

        return $this->request($call);
    }

    //gets current game information for player
    public function getCurrentGame($id){
        $call = self::API_URL_SPECTATOR_3 . 'active-games/by-summoner/' . $id;
        return $this->request($call);
    }

    //performs a static call. Not counted in rate limit.
    //$call is what is asked (champion, item...)
    //$id the id for a specific item, champion. Set at null to get all champions, items...
    //$params is the string you get after the "?"
    //		getStatic("champions", 1, "locale=fr_FR&tags=image&tags=spells") will get you image data and spells data in French from champion whose ID is 1, here Annie.
    public function getStatic($call, $id = null, $params = null) {
        $call = self::API_URL_STATIC_3 . $call;

        if( $id !=null)
            $call.="/" . $id;

        if( $params !=null)
            $call.="?" . $params;

        return $this->request($call, true);
    }

    //New to my knowledge. Returns match details.
    //Now that timeline is a separated call, when includedTimeline is true, two calls are done at the same time.
    //Data is then processed to match the old structure, with timeline data included in the match data
    public function getMatch($matchId, $includeTimeline = true) {
        $call = self::API_URL_MATCH_3  . 'matches/' . $matchId;

        if(!$includeTimeline)
            return $this->request($call);

        else
            $timelineCall =  self::API_URL_MATCH_3  . 'timelines/by-match/' . $matchId;
        $data = $this->requestMultiple(array(
            "data"=>$call,
            "timeline"=>$timelineCall
        ));
        $data["data"]["timeline"] = $data["timeline"];
        return $data["data"];
    }

    public function getMatches($ids, $includeTimeline = true){

        $calls=array();

        foreach($ids as $matchId){
            $call = self::API_URL_MATCH_3  . 'matches/' . $matchId;
            $calls["match-".$matchId] = $call;

            if($includeTimeline)
                $calls["timeline-".$matchId] = self::API_URL_MATCH_3  . 'timelines/by-match/' . $matchId;
        }

        if(!$includeTimeline)
            return $this->requestMultiple($calls);

        $results = array();

        $data = $this->requestMultiple($calls);

        foreach($data as $k=>$d){
            $e = explode("-", $k);

            //Check if it's match data
            if($e[0]=="match"){
                //Check if the timeline exists
                //Timeline is only stored by Riot for one year, too old games may not have it
                if(isset($data["timeline-".$e[1]]["frames"]))
                    //add the matching timeline
                    $d["timeline"] = $data["timeline-".$e[1]];
                array_push($results, $d);
            }
        }

        return $results;
    }

    //Returns timeline of a match
    public function getTimeline($matchId){
        $call =  self::API_URL_MATCH_3  . 'timelines/by-match/' . $matchId;

        return $this->request($call);
    }

    //Returns a user's matchList given their account id.
    public function getMatchList($accountId,$params=null) {
        if($params==null){
            $call = self::API_URL_MATCH_3  . 'matchlists/by-account/' . $accountId;
        }else{
            $call = self::API_URL_MATCH_3  . 'matchlists/by-account/' . $accountId .'?';

            //You can pass params either as an array or as string
            if(is_array($params))
                foreach($params as $key=>$param){
                    //each param can also be an array, a list of champions, queues or seasons
                    //refer to API doc to get details about params
                    if(is_array($param))
                        foreach($param as $p)
                            $call .= $key . '=' . $p . '&';

                    else
                        $call .= $key . '=' . $param . '&';
                }

            else
                $call .= $params . '&';
        }

        return $this->request($call);
    }

    //Returns a user's recent matchList given their account id.
    public function getRecentMatchList($accountId) {
        $call = self::API_URL_MATCH_3  . 'matchlists/by-account/' . $accountId . '/recent';

        return $this->request($call);
    }

    //Returns the league of a given summoner.
    public function getLeague($id){
        $call = 'leagues/by-summoner/' . $id;

        //add API URL to the call
        $call = self::API_URL_LEAGUE_3 . $call;

        return $this->request($call);
    }

    //Returns the league position of a given summoner.
    //Similar to the old league /entry
    public function getLeaguePosition($id){
        $call = 'positions/by-summoner/' . $id;

        //add API URL to the call
        $call = self::API_URL_LEAGUE_3 . $call;

        return $this->request($call);
    }

    //Returns the challenger ladder.
    public function getChallenger($queue = "RANKED_SOLO_5x5") {
        $call = 'challengerleagues/by-queue/' . $queue;

        //add API URL to the call
        $call = self::API_URL_LEAGUE_3 . $call;
        return $this->request($call);
    }

    //Returns the master ladder.
    public function getMaster($queue = "RANKED_SOLO_5x5") {
        $call = 'masterleagues/by-queue/' . $queue;

        //add API URL to the call
        $call = self::API_URL_LEAGUE_3 . $call;
        return $this->request($call);
    }

    //returns a summoner's id
    public function getSummonerId($name) {
        $name = strtolower($name);
        $summoner = $this->getSummonerByName($name);
        if (self::DECODE_ENABLED) {
            return $summoner['id'];
        }
        else {
            $summoner = json_decode($summoner, true);
            return $summoner['id'];
        }
    }

    //returns an account id
    public function getSummonerAccountId($name) {
        $name = strtolower($name);
        $summoner = $this->getSummonerByName($name);
        if (self::DECODE_ENABLED) {
            return $summoner['accountId'];
        }
        else {
            $summoner = json_decode($summoner, true);
            return $summoner['accountId'];
        }
    }

    //Returns summoner info given summoner id or account id.
    public function getSummoner($id,$accountId = false){
        $call = 'summoners/';
        if ($accountId) {
            $call .= 'by-account/';
        }
        $call .= $id;

        //add API URL to the call
        $call = self::API_URL_SUMMONER_3 . $call;

        return $this->request($call);
    }

    //Gets a summoner's info given their name, instead of id.
    public function getSummonerByName($name){
        $call = 'summoners/by-name/' . rawurlencode($name);

        //add API URL to the call
        $call = self::API_URL_SUMMONER_3 . $call;

        return $this->request($call);
    }

    //Gets a summoner's masteries.
    public function getMasteries($id){
        $call = 'masteries/by-summoner/' . $id;

        //add API URL to the call
        $call = self::API_URL_PLATFORM_3 . $call;

        return $this->request($call);
    }

    //Gets a summoner's runes.
    public function getRunes($id){
        $call = 'runes/by-summoner/' . $id;

        //add API URL to the call
        $call = self::API_URL_PLATFORM_3 . $call;

        return $this->request($call);
    }

	public function getLastResponseHeaders(){
		return $this->responseHeaders;
	}

	public function getLastResponseBody(){
		return $this->responseBody;
	}

	public function getLastResponseCode(){
		return $this->responseCode;
	}

	private function request($call, $otherQueries=false, $static = false) {
				//format the full URL
		$url = $this->format_url($call, $otherQueries);
		$this->log($url);

		$result = $this->cache->remember($url, self::CACHE_LIFETIME_MINUTES * 60, function () use ($url, $call, $otherQueries, $static)
		{
			$this->curlExecute($url);

			/**
			 * Here we are going to check if we were rate limited. If we WERE rate limited, then lets call our rate limit
			 * handler and let that class deal with it.
			 */
			if ($this->responseCode == self::HTTP_RATE_LIMIT) {
			    $this->log('we were rate limited...');
				$retryAfter = (int) $this->responseHeaders['Retry-After'];
				$this->rateLimitHandler->handleLimit($retryAfter);

				if ($this->rateLimitHandler->retryEnabled()) {
					return $this->request($call, $otherQueries, $static);
				}
			}

			if ($this->responseCode != self::HTTP_OK) {
                $this->log('we got an error...');
				throw new Exception(self::$errorCodes[$this->responseCode]);
			}

			$result = $this->responseBody;
			if (self::DECODE_ENABLED) {
				$result = json_decode($result, true);
			}

			return $result;
		});

		return $result;
	}

	private function curlExecute($url){
		//call the API and return the result
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Riot-Token: '. $this->API_KEY
        ));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$result = curl_exec($ch);

		list($header, $body) = explode("\r\n\r\n", $result, 2);
		$this->responseHeaders = $this->parseHeaders($header);
		$this->responseBody = $body;
		$this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
	}

	//creates a full URL you can query on the API
	private function format_url($call, $otherQueries=false){
		return str_replace('{platform}', $this->PLATFORM, $call) . ($otherQueries ? '&' : '?');
	}

	private function parseHeaders($header) {
		$headers = array();
		$headerLines = explode("\r\n", $header);
		foreach ($headerLines as $headerLine) {
			@list($key, $val) = explode(': ', $headerLine, 2);
			$headers[$key] = $val;
		}
		return $headers;
	}

	public function debug($message) {
		echo "<pre>";
		print_r($message);
		echo "</pre>";
	}

	public function log($message) {
        $file = './log.txt';
        $message = file_get_contents($file) . "\n" . $message;
        file_put_contents($file, $message);
    }
}
