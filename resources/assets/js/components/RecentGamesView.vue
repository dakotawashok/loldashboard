<template>
    <div class="recent-games-wrapper container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="recent-games-cards">
                    <div v-for="game in summoner1RecentGames"><recentgamecard v-on:changeCurrentGameId="changeCurrentGameId('1', arguments[0])" :game="game"></recentgamecard></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="recent-games-cards">
                    <div v-for="game in summoner2RecentGames"><recentgamecard v-on:changeCurrentGameId="changeCurrentGameId('2', arguments[0])" :game="game"></recentgamecard></div>
                </div>
            </div>
        </div>
        <div class="recent-game container-fluid">
            <h3>Game:</h3>
            <div class="row">
                <div class="col-md-6 summoner1">
                    <ul class="recent-game-stats" v-if="summoner1GameId != -1">
                        <li v-for="(key, value) in summoner1SelectedGame">{{value}} : {{key}}</li>
                    </ul>
                </div>
                <div class="col-sm-6 summoner2">
                    <ul class="recent-game-stats2" v-if="summoner2GameId != -1">
                        <li v-for="(key, value) in summoner2SelectedGame">{{value}} : {{key}}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import store from '../store.js';

    export default {
        props : [

        ],
        data : function() {
            return {
                summoner1GameId : -1,
                summoner2GameId : -1,
            }
        },
        computed : {
            summoner1RecentGames : function() { return store.state.summoner1.recentGameList; },
            summoner2RecentGames : function() { return store.state.summoner2.recentGameList; },

            summoner1SelectedGame : function() { return store.state.summoner1.selectedGame; },
            summoner2SelectedGame : function() { return store.state.summoner2.selectedGame; },
        },
        methods : {
            changeCurrentGameId : function(summonerNumber, gameId) {
                if (summonerNumber == "1") {
                    this.summoner1GameId = gameId;
                    var tempList = store.state.summoner1.recentGameList;
                    for(var game in tempList) {
                        if (tempList[game].gameId === gameId) {
                            store.commit('assignSummoner1SelectedGame', tempList[game]);
                            break;
                        }
                    }
                } else {
                    this.summoner2GameId = gameId;
                    var tempList = store.state.summoner2.recentGameList;
                    for(var game in tempList) {
                        if (tempList[game].gameId == gameId) {
                            store.commit('assignSummoner2SelectedGame', tempList[game]);
                            break;
                        }
                    }
                }
            }
        },
        mounted () {

        }
    }

</script>

<style scoped>
    .recent-game, .recent-games-cards {
        display: inline-block;
        width: 100%;
    }

    .recent-game-stats {
        list-style: none;
        text-align: left;
    }

    .recent-game-stats2 {
        list-style: none;
        text-align: right;
    }
</style>