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
        </div>
        <div class="row main-content-wrapper">
            <div class="main-content row" v-if="currentMenu == 'Summary' && currentSubMenu != ''"><summarycontents v-bind:type="currentSubMenu" v-bind:summaryData="summonerSummaryData"></summarycontents></div>
            <div class="main-content row" v-if="currentMenu == 'Ranked' && currentSubMenu !=''"><div v-for="match in matchlist"><matchcard v-bind:match="match"></matchcard></div></div>
            <div class="main-content row" v-if="currentMenu == 'Champions'"></div>
            <div class="main-content row" v-if="currentMenu == 'Recent Games'"><div v-for="game in recentGames"><recentgamecard v-bind:game="game"></recentgamecard></div></div>
            <div class="main-content row" v-if="currentMenu == 'Stats'"></div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.getStaticData();
        },
        data : function() {
            return {
                staticData : {
                    champions : {}
                },
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

            getStaticData : function() {
                this.$http.get('/jsonfiles/champion.json').then(
                    response => {
                        this.staticData.champions = response.body.data;
                        for (var champ in this.staticData.champions) {
                            //console.log(this.staticData.champions[champ].key);
                        }
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
            },
            currentSubMenu : function(newMenuItem) {
                console.log(this.currentSubMenu);
                if (this.currentMenu == "Ranked" && this.currentSubMenu != "") {
                    this.findMatchList(this.summoner.id, this.currentSubMenu).then(
                        response => {
                            this.matchlist = JSON.parse(response.body);
                            this.matchlist = this.matchlist.matches;
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
</style>