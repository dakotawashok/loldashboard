var Vuex = require('vuex');

var store = new Vuex.Store({
    state : {
        staticInfo : {
            items : [],
            champions : [],
            spells : [],
            seasons : [],
            matchmaking_queues : [],
            map_names : []
        },
        summoner1 : {
            loaded : false,
            loading : false,

            accountId: "",
            championMastery: [],
            league: {},
            profileIconId: "",
            revisionDate: "",
            summonerLevel: "",
            summonerName : '',
            summonerNumber: 0,

            normalMatchList :{},
            rankedMatchList : {},
            otherMatchList : {},
            definedNormalMatchList: {},
            definedRankedMatchList: {},
            definedOtherMatchList: {},
        },
        summoner2 : {
            loaded : false,
            loading : false,

            accountId: "",
            championMastery: [],
            league: {},
            profileIconId: "",
            revisionDate: "",
            summonerLevel: "",
            summonerName : '',
            summonerNumber: 0,

            normalMatchList :{},
            rankedMatchList : {},
            otherMatchList : {},
            definedNormalMatchList: {},
            definedRankedMatchList: {},
            definedOtherMatchList: {},
        },
        testSummoner1 : {},
        testSummoner2 : {},
        currentYear : "2017",
        modalMatch: {
            gameId: 0,
            MatchParticipantIdentities: [],
            created_at: "",
            gameCreation: "",
            gameDuration: "",
            gameMode: "",
            gameType: "",
            gameVersion: "",
            id: 0,
            mapId: "",
            matchParticipants: [],
            matchTeams: [],
            platformId: "",
            queueId: "",
            seasonId: "",
            updated_at: ""
        },
        matchModalLoading: false,
        matchModalLoaded: false,
    },
    mutations : {
        assignItems (state, itemList) {                             state.staticInfo.items = itemList; },
        assignChampions (state, championsList) {                    state.staticInfo.champions = championsList; },
        assignSpells (state, spellList) {                           state.staticInfo.spells = spellList; },
        assignSeasons (state, seasonList) {                         state.staticInfo.seasons = seasonList; },
        assignMatchmakingQueues (state, matchmakingQueuesList) {    state.staticInfo.matchmaking_queues = matchmakingQueuesList; },
        assignMapNames (state, mapNameList) {                       state.staticInfo.map_names = mapNameList; },

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

        assignSummonerMatchList(state, load) {
            if (load.summonerNumber == 1) {
                if (load.matchListType == 'normal') {
                    state.summoner1.normalMatchList = load.matchList;
                } else if (load.matchListType == 'ranked') {
                    state.summoner1.rankedMatchList = load.matchList;
                } else {
                    state.summoner1.otherMatchList = load.matchList;
                }
            } else {
                if (load.matchListType == 'normal') {
                    state.summoner2.normalMatchList = load.matchList;
                } else if (load.matchListType == 'ranked') {
                    state.summoner2.rankedMatchList = load.matchList;
                } else {
                    state.summoner2.otherMatchList = load.matchList;
                }
            }
        },
        assignSummonerDefinedMatchList(state, load) {
            if (load.summonerNumber == 1) {
                if (load.matchListType == 'normal') {
                    state.summoner1.definedNormalMatchList = load.matchList;
                } else if (load.matchListType == 'ranked') {
                    state.summoner1.definedRankedMatchList = load.matchList;
                } else {
                    state.summoner1.definedOtherMatchList = load.matchList;
                }
            } else {
                if (load.matchListType == 'normal') {
                    state.summoner2.definedNormalMatchList = load.matchList;
                } else if (load.matchListType == 'ranked') {
                    state.summoner2.definedRankedMatchList = load.matchList;
                } else {
                    state.summoner2.definedOtherMatchList = load.matchList;
                }
            }
        },

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

        assignModalMatch (state, modalMatch) {
            state.modalMatch = modalMatch;
        },
        assignMatchModalLoading (state, loading) {
            state.matchModalLoading = loading;
        },
        assignMatchModalLoaded (state, loaded) {
            state.matchModalLoaded = loaded;
        },

        assignTestSummoner(state, summonerObject) {
            var summonerNumber = summonerObject.summonerNumber;
            var summoner = summonerObject.summoner;
            if (summonerNumber === "1") {
                state.testSummoner1 = summoner;
            } else {
                state.testSummoner2 = summoner;
            }
        },

        assignSummoner(state, summonerObject) {
            var summonerNumber = summonerObject.summonerNumber;
            var summoner = summonerObject.summoner;
            if (summonerNumber === "1") {
                state.summoner1 = summoner;
            } else {
                state.summoner2 = summoner;
            }
        }
    }
});

export default store;