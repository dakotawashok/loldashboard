<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

require_once '../app/Providers/riotapi/php-riot-api.php';
require_once '../app/Providers/riotapi/FileSystemCache.php';

use Illuminate\Database\Eloquent\ModelNotFoundException;
use riotapi;
use FileSystemCache;

class Summoner extends Model
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

    /**
     * This will assign the object
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function assignDataFromAPI($id) {
        if (is_numeric($id)) {
            $tempSummoner = $this->api->getSummoner($id, true);
        } else {
            $tempSummoner = $this->api->getSummonerByName($id);
        }

        $this->id = $tempSummoner['id'];
        $this->accountId = $tempSummoner['accountId'];
        $this->name = $tempSummoner['name'];
        $this->profileIconId = $tempSummoner['profileIconId'];
        $this->summonerLevel = $tempSummoner['summonerLevel'];
        $this->revisionDate = (string)$tempSummoner['revisionDate'];

        $this->assignChampionMasteries(false);
        $this->assignLeagues(false);

        $this->save();

        return $this;
    }

    public function assignChampionMasteries($save) {
        $championMastery = $this->api->getChampionMastery($this->id);
        $this->championMastery = json_encode($championMastery);
        if ($save) { $this->save(); }
    }
    public function assignLeagues($save) {
        $league = $this->api->getLeague($this->id);
        $this->league = json_encode($league);
        if ($save) { $this->save(); }
    }
}
