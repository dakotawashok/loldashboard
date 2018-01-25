import store from "../store";
import $ from 'jquery';

export default class Summoner {
    constructor(identity, isSummonerName) {
        this.summonerName = '';
        this.summonerNumber = '';
        this.accountNumber = '';

        this.championMastery = [];
        this.profileIconId = '';
        this.summonerLevel = '';
        this.rankedData = [];

        this.rankedMatchList = [];
        this.normalMatchList = [];
        this.otherMatchList = [];

        this.definedRankedMatchList = [];
        this.definedNormalMatchList = [];
        this.definedOtherMatchList = [];

        this.loading = false;
        this.loaded = false;

        if (isSummonerName) {
            this.summonerName = identity;
        } else {
            this.summonerNumber = identity;
        }

        $.get('/summoner/' + (this.summonerName != '' ? this.summonerName : this.summonerNumber)).then((resp) => {
            this._parseSummonerDataFromSummonerResponse(resp)
        }).catch((resp) => {
            console.log('error: ' + resp);
        });
    }

    getMatchList(matchListType, $params) {
        $.get('/summoner/' + (this.summonerName != '' ? this.summonerName : this.summonerNumber)).then((resp) => {
            console.log(resp);
        }).catch((resp) => {
            console.log('error: ' + resp);
        });
    }

    getDefinedMatchList(matchListType, $params) {

    }

    _parseSummonerDataFromSummonerResponse(response) {
        var parsedResponse = JSON.parse(response);
        this.summonerName = parsedResponse.name;
        this.summonerNumber = parsedResponse.id;
        this.accountNumber = parsedResponse.accountId;
        this.championMastery = JSON.parse(parsedResponse.championMastery);
        this.profileIconId = parsedResponse.profileIconId;
        this.summonerLevel = parsedResponse.summonerLevel;
        this.league = JSON.parse(parsedResponse.league);
        this.revisionDate = parsedResponse.revisionDate;
        this.created_at = parsedResponse.created_at;
        this.updated_at = parsedResponse.updated_at;
    }

    _parseMatchListDataFromResponse(matchList, definedMatchList) {

    }

    _assignRankedData() {

    }
}