import store from "../store";

class Summoner {
    summonerName = '';
    summonerNumber = '';
    accountNumber = '';

    championMastery = [];
    profileIconId = '';
    summonerLevel = '';
    rankedData = [];

    rankedMatchList = [];
    normalMatchList = [];
    otherMatchList = [];

    definedRankedMatchList = [];
    definedNormalMatchList = [];
    definedOtherMatchList = [];

    loading = false;
    loaded = false;

    constructor(identity, isSummonerName) {
        if (isSummonerName) {
            this.summonerName = identity;
        } else {
            this.summonerNumber = identity;
        }

        this.$http.get('/summoner/' + (this.summonerName != '' ? this.summonerName : this.accountNumber) + '/allData').then((resp) => {
            resp = JSON.parse(resp.body);
            // get the summoner information from the response
            resp.summoner = this._parseSummonerDataFromResponse(resp.summoner);
            resp.normalMatchList.matches = JSON.parse(resp.normalMatchList.matches);
            resp.rankedMatchList.matches = JSON.parse(resp.rankedMatchList.matches);
            resp.otherMatchList.matches = JSON.parse(resp.otherMatchList.matches);
            this._parseMatchListDataFromResponse(resp.normalMatchList.matches, resp.normalDefinedMatchList);
            this._parseMatchListDataFromResponse(resp.rankedMatchList.matches, resp.rankedDefinedMatchList);
            this._parseMatchListDataFromResponse(resp.otherMatchList.matches, resp.otherDefinedMatchList);
            store.commit('assignSummoner1Summoner', resp.summoner);
            store.state.summoner1.summonerName = resp.summoner.name;
            store.commit('assignSummoner1Loaded', true);
            store.commit('assignSummoner1RankedMatchList', resp.rankedMatchList.matches);
            store.commit('assignSummoner1DefinedRankedMatchList', resp.rankedDefinedMatchList);
            store.commit('assignSummoner1NormalMatchList', resp.normalMatchList.matches);
            store.commit('assignSummoner1DefinedNormalMatchList', resp.normalDefinedMatchList);
            store.commit('assignSummonerMatchList', {'matchList' : resp.otherMatchList.matches, summonerNumber : 1, matchListType: 'other'});
            store.commit('assignSummonerDefinedMatchList', {'matchList' : resp.otherDefinedMatchList, summonerNumber : 1, matchListType: 'other'});

            this.assignRankedData(summonerNumber);

            store.commit('assignSummonerLoading', {'summonerNumber' : 1, 'loading' : false});
        }).catch((resp) => {
            store.commit('assignSummonerLoading', {'summonerNumber' : 1, 'loading' : false});
            this.$notify.error('Summoner not found');
        });


    }

    _parseSummonerDataFromResponse(summoner) {

    }

    _parseMatchListDataFromResponse(matchList, definedMatchList) {

    }
}