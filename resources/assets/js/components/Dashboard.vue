<template>
    <div class="container">
        <div class="row header-row">
            <div class="col-sm-2 icon-wrapper">
                <div v-if="summonerLoaded" class="icon">
                    <img :src="computedProfileIconUrl" />
                </div>
            </div>
            <div class="col-sm-3 summoner-form-wrapper">
                <div class="summoner-form">
                    <h2>SUMMONER NAME: </h2>
                    <input v-model="identifier" placeholder="Summoner Name" v-on:keyup.enter="getInfo"/>
                    <select v-if="summonerLoaded" v-model="currentYear" class="form-control" id="currentYear">
                        <option>2017</option>
                        <option>2016</option>
                        <option>2015</option>
                        <option>2014</option>
                        <option>2013</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-2 season-ranklist-wrapper">
                <div v-if="summonerLoaded" class="season-ranklist">

                </div>
            </div>
            <div class="col-sm-5 season-friends-wrapper">
                <div v-if="summonerLoaded" class="season-friends">

                </div>
            </div>
        </div>
        <div class="row main-tab">
            <ul class="nav nav-tabs" v-if="summonerLoaded">
                <li role="presentation" v-for="menuItem in mainMenuItems"><a href="#" v-on:click="changeMenu(menuItem)">{{menuItem}}</a></li>
            </ul>
        </div>
        <div class="row sub-tab">
            <ul class="nav nav-tabs" v-if="currentMenu == 'Summary'">
                <li role="presentation" v-for="(value, key) in summonerSummaryData"><a href="#" v-on:click="changeSubMenu(value.playerStatSummaryType)">{{value.playerStatSummaryType}}</a></li>
            </ul>
            <ul class="nav nav-tabs" v-if="currentMenu == 'Ranked'">
                <li role="presentation" v-for="item in rankedSubMenuItems"><a href="#" v-on:click="changeSubMenu(item)">{{item}}</a></li>
            </ul>
            <ul class="nav nav-tabs" v-if="currentMenu == 'Champions'">
                <li role="presentation" v-for="item in summonerRankedData"><a href="#" v-on:click="changeSubMenu(item.id)">{{staticChampion(item.id)}}</a></li>
            </ul>
        </div>
        <div class="row main-content-wrapper">
            <div class="main-content row" v-if="currentMenu == 'Summary' && currentSubMenu != ''"><summarycontents v-bind:type="currentSubMenu" v-bind:summaryData="summonerSummaryData"></summarycontents></div>
            <div class="main-content row" v-if="currentMenu == 'Ranked' && currentSubMenu !=''"><div v-for="match in matchlist"><matchcard v-bind:match="match"></matchcard></div></div>
            <div class="main-content row" v-if="currentMenu == 'Champions' && currentSubMenu !=''"><championcard v-bind:currentSubMenu="currentSubMenu" v-bind:championStats="summonerRankedData"></championcard></div>
            <div class="main-content row" v-if="currentMenu == 'Recent Games'"><div><recentgamesview v-bind:recentGames="recentGames"></recentgamesview></div></div></div>
            <div class="main-content row" v-if="currentMenu == 'Stats'"></div>
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
                summonerLoaded : false,
                summoner : {},

                summonerRankedData : {},
                summonerSummaryData : {},
                summonerAverageData : {},

                currentMenu : "",
                currentSubMenu : "",
                identifier : "",
                currentYear : 2017,

                matchlist : {},
                matches : {},
                recentGames : {},
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
            }
        },
        computed : {
            computedProfileIconUrl : function() {
                return "http://ddragon.leagueoflegends.com/cdn/7.2.1/img/profileicon/" + this.summoner.profileIconId + ".png";
            },

            staticInfo : function() {
                return store.state.staticInfo;
            },

            staticChampions : function() {
                return store.state.staticInfo.champions;
            },

            sortedRankedStats : function() {
                var tempStats = this.summonerRankedData;
            }
        },
        methods : {
            findSummoner : function() {
                return this.$http.get('/summoner/' + this.identifier);
            },

            findSummonerSummaryData : function(identifier) {
                return this.$http.get('/summoner/' + identifier + '/summary/data/' + this.currentYear);
            },

            findSummonerRankedData : function(identifier) {
                return this.$http.get('/summoner/' + identifier + '/ranked/data/' + this.currentYear);
            },

            findMatchList : function(identifier, season) {
                return this.$http.get('/summoner/' + identifier + '/matchlist/' + season);
            },

            findMatch : function(matchId) {
                this.$http.get('/summoner/' + this.identifier + '/match/' + matchId).then(
                    response => {
                        return response.body;
                    },
                    response => {
                        console.log('match failure');
                    }
                );
            },

            findRecentGames : function(id) {
                return this.$http.get('summoner/' + id + '/recentgames');
            },

            staticChampion : function(id) {
                for (var champion in store.state.staticInfo.champions) {
                    if (store.state.staticInfo.champions[champion].key == id) {
                        return store.state.staticInfo.champions[champion].id;
                    }
                }
            },

            getInfo : function() {
                this.findSummoner().then(
                    response => {
                        this.summoner = JSON.parse(response.body);
                        this.summonerLoaded = true;
                        return this.findRecentGames(this.summoner.id);
                    }
                ).then(
                    response => {
                        this.recentGames = response.body.games;
                        return this.findSummonerRankedData(this.summoner.id);
                    }
                ).then(
                    response => {
                        this.summonerRankedData = JSON.parse(response.body);
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

            changeMenu : function(menuItem) {
                this.currentMenu = menuItem;
                this.currentSubMenu = "";
                $('.sub-tab').show();
            },

            changeSubMenu : function(menuItem) {
                this.currentSubMenu = menuItem;
            }

        },
        watch : {
            summoner : function(newSummoner) {
                this.findSummonerSummaryData(newSummoner.id).then(
                    response => {
                        this.summonerSummaryData = JSON.parse(response.body);
                        this.summonerSummaryData = this.summonerSummaryData.playerStatSummaries;
                    }
                ).catch(
                    response => {
                        console.log(response);
                    }
                );
            },
            currentYear : function(newYear) {
                this.currentSubMenu = "";
                switch (this.currentMenu) {
                    case "Summary" :
                        this.findSummonerSummaryData(this.summoner.id).then(
                            response => {
                                this.summonerSummaryData = JSON.parse(response.body);
                                this.summonerSummaryData = this.summonerSummaryData.playerStatSummaries;
                            }
                        ).catch(
                            response => {
                                console.log(response);
                            }
                        );
                        break;
                    case "Ranked" :

                        break;
                    case "Champions" :
                        this.findSummonerRankedData(this.summoner.id).then(
                            response => {
                                this.summonerRankedData = JSON.parse(response.body);
                                this.summonerRankedData = this.summonerRankedData.champions;
                            }
                        );
                        break;
                    case "Recent Games" :

                        break;
                    case "Stats" :

                        break;
                }


            },
            currentSubMenu : function(newMenuItem) {
                if (this.currentMenu == "Ranked" && this.currentSubMenu != "") {
                    this.findMatchList(this.summoner.id, this.currentSubMenu).then(
                        response => {
                            this.matchlist = JSON.parse(response.body);
                            this.matchlist = this.matchlist.matches;
                        }
                    );
                }
            },
            currentMenu : function(newMenuItem) {
                if (this.currentMenu == "Champions") {
                    this.findSummonerRankedData(this.summoner.id).then(
                        response => {
                            this.summonerRankedData = JSON.parse(response.body);
                            this.summonerRankedData = this.summonerRankedData.champions;
                        }
                    );
                }
            }
        }
    }
</script>

<style scoped>
    .form-control {
        margin-top: 10px;
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