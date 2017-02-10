var Vuex = require('vuex');

var store = new Vuex.Store({
    state : {
        staticInfo : {
            champions : []
        },
        summoner1 : {
            loaded : false,
            summoner : {},
            summaryData : {},
            rankedData : {},
            rankedMatchList : {},
            recentGameList : {},
            averageData : {}
        },
        summoner2 : {
            loaded : false,
            summoner : {},
            summaryData : {},
            rankedData : {},
            rankedMatchList : {},
            recentGameList : {},
            averageData : {}
        },
        currentMenuItem : "",
        currentSubMenuItem : "",
        currentYear : "2017",

        loading : false,
    },
    mutations : {
        assignChampions (state, championsList) {
            state.staticInfo.champions = championsList;
        },
        assignSummoner1Summoner (state, summoner) {
            state.summoner1.summoner = summoner;
        },
        assignSummoner2Summoner (state, summoner) {
            state.summoner2.summoner = summoner;
        },
        assignSummoner1SummaryData (state, summaryData) {
            state.summoner1.summaryData = summaryData;
        },
        assignSummoner2SummaryData (state, summaryData) {
            state.summoner2.summaryData = summaryData;
        },
        assignSummoner1RankedData (state, rankedData) {
            state.summoner1.rankedData = rankedData;
        },
        assignSummoner2RankedData (state, rankedData) {
            state.summoner2.rankedData = rankedData;
        },
        assignSummoner1RankedMatchList (state, rankedMatchList) {
            state.summoner1.rankedMatchList = rankedMatchList;
        },
        assignSummoner2RankedMatchList (state, rankedMatchList) {
            state.summoner2.rankedMatchList = rankedMatchList;
        },
        assignSummoner1RecentGameList (state, recentGameList) {
            state.summoner1.recentGameList = recentGameList;
        },
        assignSummoner2RecentGameList (state, recentGameList) {
            state.summoner2.recentGameList = recentGameList;
        },
        assignSummoner1AverageData (state, averageData) {
            state.summoner1.averageData = averageData;
        },
        assignSummoner2AverageData (state, averageData) {
            state.summoner2.averageData = averageData;
        },
        assignSummoner1Loaded (state, loaded) {
            state.summoner1.loaded = loaded;
        },
        assignSummoner2Loaded (state, loaded) {
            state.summoner2.loaded = loaded;
        },

        assignCurrentMenuItem (state, menuItem) {
            state.currentMenuItem = menuItem;
        },
        assignCurrentSubMenuItem(state, menuItem) {
            state.currentSubMenuItem = menuItem;
        },
        assignCurrentYear(state, year) {
            state.currentYear = year;
        },
        assignLoading(state, loading ) { state.loading = loading; }
    }
});

export default store;