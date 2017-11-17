var Vuex = require('vuex');

var store = new Vuex.Store({
    state : {
        staticInfo : {
            champions : [],
            spells : [],
        },
        summoner1 : {
            loaded : false,
            loading : false,
            summoner : {},
            summonerName : '',
            normalMatchList :{},
            rankedMatchList : {},
            definedNormalMatchList: {},
            definedRankedMatchList: {},
        },
        summoner2 : {
            loaded : false,
            loading : false,
            summoner : {},
            summonerName : '',
            normalMatchList :{},
            rankedMatchList : {},
            definedNormalMatchList: {},
            definedRankedMatchList: {},
        },
        currentYear : "2017",

    },
    mutations : {
        assignChampions (state, championsList) {
            state.staticInfo.champions = championsList;
        },
        assignSpells (state, spellList) {
            state.staticInfo.spells = spellList;
        },
        assignSummoner1Summoner (state, summoner) {
            state.summoner1.summoner = summoner;
        },
        assignSummoner2Summoner (state, summoner) {
            state.summoner2.summoner = summoner;
        },
        assignSummoner1RankedMatchList (state, rankedMatchList) {
            state.summoner1.rankedMatchList = rankedMatchList;
        },
        assignSummoner2RankedMatchList (state, rankedMatchList) {
            state.summoner2.rankedMatchList = rankedMatchList;
        },
        assignSummoner1NormalMatchList (state, normalMatchList) {
            state.summoner1.normalMatchList = normalMatchList;
        },
        assignSummoner2NormalMatchList (state, normalMatchList) {
            state.summoner2.normalMatchList = normalMatchList;
        },

        assignSummoner1DefinedRankedMatchList (state, rankedMatchList) {
            state.summoner1.definedRankedMatchList = rankedMatchList;
        },
        assignSummoner2DefinedRankedMatchList (state, rankedMatchList) {
            state.summoner2.definedRankedMatchList = rankedMatchList;
        },
        assignSummoner1DefinedNormalMatchList (state, normalMatchList) {
            state.summoner1.definedNormalMatchList = normalMatchList;
        },
        assignSummoner2DefinedNormalMatchList (state, normalMatchList) {
            state.summoner2.definedNormalMatchList = normalMatchList;
        },
        assignSummoner1Loaded (state, loaded) { state.summoner1.loaded = loaded; },
        assignSummoner2Loaded (state, loaded) { state.summoner2.loaded = loaded; },

        assignSummonerLoading (state, summonerObject) {
            var summonerNumber  = summonerObject.summonerNumber;
            var loading  = summonerObject.loading;
            if (summonerNumber === 1) {
                state.summoner1.loading = loading;
            } else {
                state.summoner2.loading = loading;
            }
        },

        assignSummonerName (state, summonerObject) {
            var summonerNumber  = summonerObject.summonerNumber;
            var summonerName  = summonerObject.summonerName;
            if (summonerNumber === 1) {
                state.summoner1.summonerName = summonerName;
            } else {
                state.summoner2.summonerName = summonerName;
            }
        },

        assignSummonerAccountId (state, summonerObject) {
            var summonerNumber  = summonerObject.summonerNumber;
            var accountId  = summonerObject.accountId;
            if (summonerNumber === 1) {
                state.summoner1.accountId = accountId;
            } else {
                state.summoner2.accountId = accountId;
            }
        },




        // Other methods that we need like the loading summoners from the match Card
    }
});

export default store;