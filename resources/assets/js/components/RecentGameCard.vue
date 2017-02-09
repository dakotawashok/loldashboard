<template>
    <div class="wrapper col-sm-3 col-md-2" v-on:click="changeCurrentGameId(game.gameId)">
        <a href="#" v-bind:class="{won: won}" v-bind:style="styleObject">
            <div class="game-item">
                <div>{{game.gameId}}</div>
            </div>
            <div class="game-item">
                <div>{{gameDate}}</div>
            </div>


        </a>
    </div>
</template>

<script>
    export default {
        props : [
            'game'
        ],
        data : function() {
            return {
                won : this.game.stats.win,
            }
        },
        computed : {
            gameDate : function() {
                var date = new Date(this.game.createDate);
                return date.toLocaleDateString();
            },
            styleObject : function() {
                return {
                    backgroundImage : "url('http://ddragon.leagueoflegends.com/cdn/6.8.1/img/map/map" + this.game.mapId + ".png')",
                }
            }

        },
        methods : {
            changeCurrentGameId : function(id) {
                this.$emit('changeCurrentGameId', id);
            }
        },
        mounted() {

        },
    }

</script>

<style scoped>
    a {
        border-style: solid;
        border-width: 3px;
        border-color: darkred;
        display: inline-block;
        width: 100%;
        height: 100%;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
    }

    a.won {
        border-color: green;
    }

    .wrapper {
        padding: 5px;
        height: 150px
    }

    div.game-item {
        background-color: rgba(0,0,0,.75);
        position: relative;
        width: 100%;
    }
</style>