<template>
    <div class="container">
        <div class="row header-row">
            <div class="col-sm-2 icon-wrapper">
                <div v-if="summoner1Loaded" class="icon">
                    <img :src="summoner1ProfileIconUrl" />
                </div>
            </div>
            <div class="col-sm-3 summoner-form-wrapper">
                <div class="summoner-form">
                    <h2>SUMMONER NAME: </h2>
                    <input v-model="summoner1Id" placeholder="Summoner Name" v-on:keyup.enter="getInfo(1)"/>
                </div>
            </div>
            <div class="col-sm-2">
                <select v-if="summoner1Loaded || summoner2Loaded" v-model="currentYear" class="form-control" id="currentYear">
                    <option>2017</option>
                    <option>2016</option>
                    <option>2015</option>
                    <option>2014</option>
                    <option>2013</option>
                </select>
            </div>
            <div class="col-sm-3 summoner2-form-wrapper">
                <div class="summoner-form">
                    <h2>SUMMONER NAME: </h2>
                    <input v-model="summoner2Id" placeholder="Summoner Name" v-on:keyup.enter="getInfo(2)"/>
                </div>
            </div>
            <div class="col-sm-2 icon-wrapper">
                <div v-if="summoner2Loaded" class="icon">
                    <img :src="summoner2ProfileIconUrl" />
                </div>
            </div>
        </div>
        <div class="row main-tab">
            <ul class="nav nav-tabs" v-if="summoner1Loaded || summoner2Loaded">
                <li role="presentation" v-for="menuItem in mainMenuItems"><a href="#" v-on:click="changeMenu(menuItem)">{{menuItem}}</a></li>
            </ul>
        </div>
        <div class="row sub-tab" >
            <ul class="nav nav-tabs" v-if="currentMenuItem == 'Summary'">
                <li role="presentation" v-for="stat in summaryStatsSubMenuItems" v-if="loading == false"><a href="#" v-on:click="changeSubMenu(stat)">{{stat}}</a></li>
            </ul>
            <ul class="nav nav-tabs" v-if="currentMenuItem == 'Ranked'">
                <li role="presentation" v-for="item in rankedSubMenuItems"><a href="#" v-on:click="changeSubMenu(item)">{{item}}</a></li>
            </ul>
            <ul class="nav nav-tabs" v-if="currentMenuItem == 'Stats'">
                <li role="presentation" v-for="item in statsSubMenuItems"><a href="#" v-on:click="changeSubMenu(item)">{{item}}</a></li>
            </ul>
        </div>
        <div class="row main-content-wrapper" v-if="loading == false">
            <div class="main-content row" v-if="currentMenuItem == 'Summary' && currentSubMenuItem != ''"><summarycontents></summarycontents></div>
            <div class="main-content row" v-if="currentMenuItem == 'Ranked' && currentSubMenuItem !=''"><rankedmatchlistview></rankedmatchlistview></div>
            <div class="main-content row" v-if="currentMenuItem == 'Champions'"><championstatsview></championstatsview></div>
            <div class="main-content row" v-if="currentMenuItem == 'Recent Games'"><recentgamesview v-bind:recentGames="recentGames"></recentgamesview></div>
            <div class="main-content row" v-if="currentMenuItem == 'Stats' && currentSubMenuItem != ''"><statsview v-bind:currentSubMenu="currentSubMenu" v-bind:averageData="summonerAverageData"></statsview></div>
        </div>
    </div>
</template>

<script>
    import store from '../store.js';

    export default {
        mounted() {
            this.setStaticData();
        },
        data : function() {
            return {
                summoner1Id : "",
                summoner2Id : "",

                currentYear : 2017,

                mainMenuItems : [
                    'Summary',
                    'Ranked',
                    'Champions',
                    'Recent Games',
                    'Stats',
                ],
                rankedSubMenuItems : [
                    'SEASON2017',
                    'PRESEASON2017',
                    'SEASON2016',
                    'PRESEASON2016',
                    'SEASON2015',
                ],
                statsSubMenuItems : [
                    'Current Year Ranked Stats',
                    'Normal Game Stats',
                    'Stats with Friends',

                ],
            }
        },
        computed : {
            summoner1ProfileIconUrl : function() { return "http://ddragon.leagueoflegends.com/cdn/7.2.1/img/profileicon/" + this.summoner1.profileIconId + ".png"; },
            summoner2ProfileIconUrl : function() { return "http://ddragon.leagueoflegends.com/cdn/7.2.1/img/profileicon/" + this.summoner2.profileIconId + ".png"; },

            summaryStatsSubMenuItems : function() {
                var tempMenuItems = [];
                if (store.state.summoner1.loaded && !store.state.summoner2.loaded) {
                    for (var stat in store.state.summoner1.summaryData) {
                        tempMenuItems.push(store.state.summoner1.summaryData[stat].playerStatSummaryType);
                    }
                    return tempMenuItems;
                } else if (!store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    for (var stat in store.state.summoner2.summaryData) {
                        tempMenuItems.push(store.state.summoner2.summaryData[stat].playerStatSummaryType);
                    }
                    return tempMenuItems;
                } else {
                    for (var stat in store.state.summoner1.summaryData) {
                        tempMenuItems.push(store.state.summoner1.summaryData[stat].playerStatSummaryType);
                    }
                    for (var stat in store.state.summoner2.summaryData) {
                        var found = false;
                        for (var tempMenuItem in tempMenuItems) {
                            if (store.state.summoner2.summaryData[stat].playerStatSummaryType == tempMenuItems[tempMenuItem]) {
                                found = true;
                            }
                        }
                        if (!found) {
                            tempMenuItems.push(store.state.summoner2.summaryData[stat].playerStatSummaryType);
                        }
                    }
                }
                tempMenuItems.sort(function(a, b) {
                    var itemA = a.toUpperCase();
                    var itemB = b.toUpperCase();
                    if (itemA < itemB) {
                        return -1;
                    } else {
                        return 1;
                    }
                });
                return tempMenuItems;
            },

            staticInfo : function() { return store.state.staticInfo; },
            staticChampions : function() { return store.state.staticInfo.champions; },

            summoner1 : function() { return store.state.summoner1.summoner; },
            summoner2 : function() { return store.state.summoner2.summoner; },

            summoner1Loaded : function() { return store.state.summoner1.loaded; },
            summoner2Loaded : function() { return store.state.summoner2.loaded; },

            currentMenuItem : function() { return store.state.currentMenuItem; },
            currentSubMenuItem : function() { return store.state.currentSubMenuItem; },

            loading : function() { return store.state.loading; },
        },
        methods : {
            findSummoner : function(summonerNumber) {
                if (summonerNumber == "1") {
                    store.commit('assignSummoner1Loaded', false);
                    return this.$http.get('/summoner/' + this.summoner1Id);
                } else {
                    store.commit('assignSummoner2Loaded', false);
                    return this.$http.get('/summoner/' + this.summoner2Id);
                }
            },
            findSummonerSummaryData : function(identifier) { return this.$http.get('/summoner/' + identifier + '/summary/data/' + this.currentYear); },
            findSummonerRankedData : function(identifier) { return this.$http.get('/summoner/' + identifier + '/ranked/data/' + this.currentYear); },
            findRecentGames : function(id) { return this.$http.get('summoner/' + id + '/recentgames'); },
            findMatchList : function(identifier, season) { return this.$http.get('/summoner/' + identifier + '/matchlist/' + season); },

            // if only the first summoner is loaded, load the data for just the first summoner
            // if only the second summoner is loaded, load the data for just the second summoner
            // if both summoners are loaded, load the data for both of the summoners
            assignSummonerSummaryData : function() {
                store.commit('assignLoading', true);
                if (store.state.summoner1.loaded && !store.state.summoner2.loaded) {
                    this.findSummonerSummaryData(store.state.summoner1.summoner.id).then(
                        response => {
                            var tempSummaryData = JSON.parse(response.body);
                            store.commit('assignSummoner1SummaryData', tempSummaryData.playerStatSummaries);
                            store.commit('assignLoading', false);
                        }
                    );
                } else if (!store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    this.findSummonerSummaryData(store.state.summoner2.summoner.id).then(
                        response => {
                            var tempSummaryData = JSON.parse(response.body);
                            store.commit('assignSummoner2SummaryData', tempSummaryData.playerStatSummaries);
                            store.commit('assignLoading', false);
                        }
                    );
                } else if (store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    // I make temporary variables like this so that I can commit both of them at the same time which fixes
                    // the problem of the menu being loaded once the first dataset is loaded, but then reloading once the second is done
                    var tempSummaryData1 = {};
                    var tempSummaryData2 = {};
                    this.findSummonerSummaryData(store.state.summoner1.summoner.id).then(
                        response => {
                            var tempSummaryData = JSON.parse(response.body);
                            tempSummaryData1 = tempSummaryData.playerStatSummaries;
                            return this.findSummonerSummaryData(store.state.summoner2.summoner.id);
                        }
                    ).then(
                        response => {
                            var tempSummaryData = JSON.parse(response.body);
                            tempSummaryData2 = tempSummaryData.playerStatSummaries;
                            store.commit('assignSummoner1SummaryData', tempSummaryData1);
                            store.commit('assignSummoner2SummaryData', tempSummaryData2);
                            store.commit('assignLoading', false);
                        }
                    ).catch(
                        response => {
                            console.log(response);
                            store.commit('assignLoading', false);
                        }
                    );
                }
            },
            assignChampionData : function() {
                store.commit('assignLoading', true);
                if (store.state.summoner1.loaded && !store.state.summoner2.loaded) {
                    this.findSummonerRankedData(store.state.summoner1.summoner.id).then(
                        response => {
                            var tempSummonerRankedData = JSON.parse(response.body);
                            store.commit('assignSummoner1RankedData', tempSummonerRankedData);
                        }
                    ).catch(
                        response => {
                            console.log("Error in Ranked Champions Data!");
                            console.log(response);
                        }
                    )
                } else if (!store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    this.findSummonerRankedData(store.state.summoner2.summoner.id).then(
                        response => {
                            var tempSummonerRankedData = JSON.parse(response.body);
                            store.commit('assignSummoner2RankedData', tempSummonerRankedData);
                        }
                    ).catch(
                        response => {
                            console.log("Error in Ranked Champions Data!");
                            console.log(response);
                        }
                    )
                } else if (store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    // I make temporary variables like this so that I can commit both of them at the same time which fixes
                    // the problem of the menu being loaded once the first dataset is loaded, but then reloading once the second is done
                    var tempRankedChampionData1 = {};
                    var tempRankedChampionData2 = {};
                    this.findSummonerRankedData(store.state.summoner1.summoner.id).then(
                        response => {
                            tempRankedChampionData1 = JSON.parse(response.body);
                            return this.findSummonerRankedData(store.state.summoner2.summoner.id);
                        }
                    ).then(
                        response => {
                            tempRankedChampionData2 = JSON.parse(response.body);
                            store.commit('assignSummoner1RankedData', tempRankedChampionData1);
                            store.commit('assignSummoner2RankedData', tempRankedChampionData2);
                        }
                    ).catch(
                        response => {
                            console.log("Error in Ranked Champions Data!");
                            console.log(response);
                        }
                    )
                }
                store.commit('assignLoading', false);
            },
            assignRankedMatchList : function() {
                store.commit('assignLoading', true);
                if (store.state.summoner1.loaded && !store.state.summoner2.loaded) {
                    this.findMatchList(store.state.summoner1.summoner.id, store.state.currentSubMenuItem).then(
                        response => {
                            var tempRankedMatchList = JSON.parse(response.body);
                            store.commit('assignSummoner1RankedMatchList', tempRankedMatchList);
                            store.commit('assignLoading', false);
                        }
                    );
                } else if (!store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    this.findMatchList(store.state.summoner2.summoner.id, store.state.currentSubMenuItem).then(
                        response => {
                            var tempRankedMatchList = JSON.parse(response.body);
                            store.commit('assignSummoner2RankedMatchList', tempRankedMatchList);
                            store.commit('assignLoading', false);
                        }
                    );
                } else if (store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    // I make temporary variables like this so that I can commit both of them at the same time which fixes
                    // the problem of the menu being loaded once the first dataset is loaded, but then reloading once the second is done
                    var tempRankedMatchList1 = {};
                    var tempRankedMatchList2 = {};
                    this.findMatchList(store.state.summoner1.summoner.id, store.state.currentSubMenuItem).then(
                        response => {
                            tempRankedMatchList1 = JSON.parse(response.body);
                            return this.findMatchList(store.state.summoner2.summoner.id, store.state.currentSubMenuItem);
                        }
                    ).then(
                        response => {
                            tempRankedMatchList2 = JSON.parse(response.body);
                            store.commit('assignSummoner1RankedMatchList', tempRankedMatchList1);
                            store.commit('assignSummoner2RankedMatchList', tempRankedMatchList2);
                            store.commit('assignLoading', false);
                        }
                    ).catch(
                        response => {
                            console.log(response);
                            store.commit('assignLoading', false);
                        }
                    );
                }
            },
            assignRecentGames : function() {
                store.commit('assignLoading', true);
                if (store.state.summoner1.loaded && !store.state.summoner2.loaded) {
                    this.findRecentGames(store.state.summoner1.summoner.id).then(
                        response => {
                            var tempSummonerRecentGames = JSON.parse(response.body);
                            store.commit('assignSummoner1RecentGameList', tempSummonerRecentGames);
                        }
                    ).catch(
                        response => {
                            console.log("Error in Recent Games Data!");
                            console.log(response);
                        }
                    )
                } else if (!store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    this.findRecentGames(store.state.summoner2.summoner.id).then(
                        response => {
                            var tempSummonerRecentGames = JSON.parse(response.body);
                            store.commit('assignSummoner2RecentGameList', tempSummonerRecentGames);
                        }
                    ).catch(
                        response => {
                            console.log("Error in Recent Games Data!");
                            console.log(response);
                        }
                    )
                } else if (store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    // I make temporary variables like this so that I can commit both of them at the same time which fixes
                    // the problem of the menu being loaded once the first dataset is loaded, but then reloading once the second is done
                    var tempRecentGames1 = {};
                    var tempRecentGames2 = {};
                    this.findRecentGames(store.state.summoner1.summoner.id).then(
                        response => {
                            tempRecentGames1 = JSON.parse(response.body);
                            return this.findRecentGames(store.state.summoner2.summoner.id);
                        }
                    ).then(
                        response => {
                            tempRecentGames2 = JSON.parse(response.body);
                            store.commit('assignSummoner1RecentGameList', tempRecentGames1);
                            store.commit('assignSummoner2RecentGameList', tempRecentGames2);
                            console.log("Done loading after shit was cached or whatever");
                        }
                    ).catch(
                        response => {
                            console.log("Error in Ranked Champions Data!");
                            console.log(response);
                        }
                    )
                }
                console.log("Done loading full steam ahead!");
                store.commit('assignLoading', false);
            },

            staticChampion : function(id) {
                for (var champion in store.state.staticInfo.champions) {
                    if (store.state.staticInfo.champions[champion].key == id) {
                        return store.state.staticInfo.champions[champion].id;
                    }
                }
            },

            getInfo : function(summonerNumber) {
                this.findSummoner(summonerNumber).then(
                    response => {
                        if (summonerNumber == "1") {
                            store.commit('assignSummoner1Summoner', JSON.parse(response.body));
                            store.commit('assignSummoner1Loaded', true);
                        } else {
                            store.commit('assignSummoner2Summoner', JSON.parse(response.body));
                            store.commit('assignSummoner2Loaded', true);
                        }
                    }
                ).catch(
                    response => {
                        console.log(response)
                    }
                );

                $('.summoner-info').show();
            },

            setStaticData : function() {
                this.$http.get('/jsonfiles/champion.json').then(
                    response => {
                        var champions = response.body.data;

                        var tempChampionsList = [];
                        for (var champion in champions) {
                            tempChampionsList.push(champions[champion]);
                        };

                        tempChampionsList.sort(function(championA, championB) {
                            if (parseInt(championA.key) < parseInt(championB.key)) {
                                return -1;
                            } else {
                                return 1;
                            }
                        });
                        store.commit('assignChampions', tempChampionsList);
                    }
                );
            },

            // ChangeMenu :
            // Changes the states current menu, and sets the current sub menu item to "" in case you were already looking at a view
            // Then, depending on the menu that was selected, it will load data that the sub menu might need
            changeMenu : function(menuItem) {
                store.commit('assignCurrentMenuItem', menuItem);
                store.commit('assignCurrentSubMenuItem', "");
                switch (menuItem) {
                    case "Summary" :
                        this.assignSummonerSummaryData();
                        break;
                    case "Ranked" :
                        break;
                    case "Champions" :
                        this.assignChampionData();
                        break;
                    case "Recent Games" :
                        this.assignRecentGames();
                        break;
                }
                $('.sub-tab').show();
            },
            changeSubMenu : function(menuItem) {
                // Change the sub menu in the current state, then, depending on what the menu item is, load the data for that item
                this.currentSubMenu = menuItem;
                store.commit('assignCurrentSubMenuItem', menuItem);

                switch (store.state.currentMenuItem) {
                    case "Summary" :
                        //load the summary data;
                        break;
                    case "Ranked" :
                        store.commit('assignSummoner1MatchLoaded', false);
                        store.commit('assignSummoner2MatchLoaded', false);
                        store.commit('assignSummoner1SelectedMatch', {});
                        store.commit('assignSummoner2SelectedMatch', {});
                        this.assignRankedMatchList();
                        break;
                    case "Champions" :
                        //load the ranked data
                        break;
                    case "Stats" :
                        //load the stats data
                        break;
                }
            }

        },
        watch : {
            currentYear : function(newYear) {
                store.commit('assignCurrentYear', newYear);
                store.commit('assignCurrentSubMenuItem', "");
                store.commit('assignLoading', true);

                switch (store.state.currentMenuItem) {
                    case "Summary" :
                        this.assignSummonerSummaryData();
                        break;
                    case "Ranked" :

                        break;
                    case "Champions" :
                        this.assignChampionData();
                        break;
                    case "Recent Games" :
                        this.assignRecentGames();
                        break;
                    case "Stats" :
                        this.findSummonerRankedData(this.summoner.id).then(
                            response => {
                                this.summonerAverageData = JSON.parse(response.body);
                                this.summonerAverageData = this.summonerAverageData.champions;
                                for (var championData in this.summonerAverageData) {
                                    if (this.summonerAverageData[championData].id == "0") {
                                        this.summonerAverageData = this.summonerAverageData[championData].stats;
                                        break;
                                    }
                                }
                            }
                        );
                        break;
                }
                store.commit('assignLoading', false);
            },
        }
    }
</script>

<style scoped>


    .summoner2-form-wrapper {
        text-align: right;
    }

    .summoner2-form-wrapper > input {
        float: right;
    }


    .form-control {
        margin-top: 60px;
        background-color: transparent;
        color: ghostwhite;
    }

    .main-tab, .sub-tab {
        outline-style: solid;
        outline-width: 1px;
        outline-color: #2e3436;
    }

    .sub-tab {
        display: none;
    }

    .main-tab > ul > * {
        width: 20%;
    }

    .header-row {
        margin-bottom: 20px;
        margin-top: 15px;
    }

    .main-content > * {
        color: ghostwhite;
        text-align: center;
    }

    input {
        background-color: #404040;
        color: ghostwhite;
        width: 65%;
        border-width: 1px;

        height: 34px;
        text-align: center;
    }

    button.btn {
        width: 33%;
        float: right;
    }

    .well {
        display: none;
        margin-top: 10px;
        color: #2e3436;
    }

    .icon-wrapper {
        margin-top: 30px;
    }

</style>