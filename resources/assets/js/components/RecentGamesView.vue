<template>
    <div class="recent-games-wrapper">
        <div class="recent-games-cards">
            <div v-for="game in recentGames"><recentgamecard v-on:changeCurrentGameId="changeCurrentGameId(arguments[0])" v-bind:game="game"></recentgamecard></div>
        </div><br />
        <div class="recent-game" v-if="currentGameId != 0">
            <h3>Game:</h3>
            <ul class="recent-game-stats">
                <li v-for="(key, value) in currentGame">{{value}} : {{key}}</li>
            </ul>
        </div>
    </div>
</template>

<script>
    import store from '../store.js';

    export default {
        props : [
            'recentGames',
        ],
        data : function() {
            return {
                currentGameId : -1
            }
        },
        computed : {
            currentGame : function() {
                if (this.currentGameId != 0) {
                    for (var tempGame in this.recentGames) {
                        if (this.recentGames[tempGame].gameId == this.currentGameId) {
                            return this.recentGames[tempGame];
                        }
                    }
                }
            }
        },
        methods : {
            changeCurrentGameId : function(id) {
                this.currentGameId = id;
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
</style>