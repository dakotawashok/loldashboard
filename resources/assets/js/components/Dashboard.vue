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
                    <img v-if="summonerLoaded" :src="computedSummonerRankUrl" />
                </div>
            </div>
            <div class="col-sm-2 season-ranklist-wrapper">
                <div v-if="summonerLoaded" class="season-ranklist">
                    {{summoner.id}}
                </div>
            </div>
            <div class="col-sm-5 season-friends-wrapper">
                <div v-if="summonerLoaded" class="season-friends">

                </div>
            </div>
        </div>
        <div class="row main-tab">
            <ul class="nav nav-tabs" v-if="summonerLoaded">
                <li role="presentation"><a href="#">Summary</a></li>
                <li role="presentation"><a href="#">Champions</a></li>
                <li role="presentation"><a href="#">Recent Games</a></li>
                <li role="presentation"><a href="#">Stats</a></li>
                <li role="presentation"><a href="#">MISC</a></li>
            </ul>
        </div>
        <div class="row sub-tab">
            <ul class="nav nav-tabs" v-if="summonerLoaded">
                <li role="presentation"><a href="#">Summary</a></li>
                <li role="presentation"><a href="#">Champions</a></li>
                <li role="presentation"><a href="#">Recent Games</a></li>
                <li role="presentation"><a href="#">Stats</a></li>
                <li role="presentation"><a href="#">MISC</a></li>
            </ul>
        </div>
        <div class="row main-content-wrapper">
            <div class="main-content row" v-if="summonerLoaded">
                <div v-for="game in recentGames">
                    <recentgamecard v-bind:game="game"></recentgamecard>
                </div>
            </div>
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
                summonerSummaryData : {},
                summonerRankedData : {},
                summonerAverageData : {},
                identifier : "",
                matchlist : {},
                matches : {},
                recentGames : {},
            }
        },
        computed : {
            computedProfileIconUrl : function() {
                return "http://ddragon.leagueoflegends.com/cdn/7.2.1/img/profileicon/" + this.summoner.profileIconId + ".png";
            },
            computedSummonerRankUrl : function() {

            }
        },
        methods : {
            findSummoner : function() {
                    return this.$http.get('/summoner/' + this.identifier);
            },

            findSummonerSummaryData : function(identifier) {
                return this.$http.get('/summoner/' + identifier + '/summary/data/2016');
            },

            findSummonerRankedData : function(identifier) {
                return this.$http.get('/summoner/' + identifier + '/ranked/data/2016');
            },

            findMatchList : function() {
                return this.$http.get('/summoner/' + this.identifier + '/matchlist');
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
                        return this.findMatchList();
                    }
                ).then(
                    response => {
                        this.matchlist = response.body.matches;
                        return this.findRecentGames(this.summoner.id);
                    }
                ).then(
                    response => {
                        this.recentGames = response.body.games;
                        return this.findSummonerSummaryData(this.summoner.id);
                    }
                ).then(
                    response => {
                        this.summonerSummaryData = JSON.parse(response.body);
                        this.summonerSummaryData = this.summonerSummaryData.playerStatSummaries;
                        console.log("Summoner Data: ");
                        console.log(this.summonerSummaryData);
                        return this.findSummonerRankedData(this.summoner.id);
                    }
                ).then(
                    response => {
                        this.summonerRankedData = JSON.parse(response.body);
                        console.log("Ranked Data: ");
                        console.log(this.summonerRankedData);
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
            }
        }
    }
</script>

<style scoped>
    .main-tab, .sub-tab {
        outline-style: solid;
        outline-width: 1px;
        outline-color: #2e3436;
    }

    .main-tab > ul > * {
        width: 20%;
    }

    .header-row {
        margin-bottom: 20px;
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