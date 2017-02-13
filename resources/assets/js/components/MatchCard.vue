<template>
    <a href="#" class="match-card col-sm-4 col-md-3" v-bind:style="styleObject" v-on:click="logMatchData(match.matchId)">
        {{match.lane}}
        {{match.role}}
        {{match.champion}}
    </a>
</template>

<script>
    import store from '../store.js';

    export default {
        props : [
            "match",
            "summonerId"
        ],
        methods : {
            logMatchData : function(matchId) {
                this.$http.get('/summoner/' + this.summonerId + '/match/' + matchId).then(
                    response => {
                        if (store.state.summoner1.summoner.id == this.summonerId) {
                            store.commit('assignSummoner1SelectedMatch', JSON.parse(response.body));
                            store.commit('assignSummoner1MatchLoaded', true);
                        } else if (store.state.summoner2.summoner.id == this.summonerId) {
                            store.commit('assignSummoner2SelectedMatch', JSON.parse(response.body));
                            store.commit('assignSummoner2MatchLoaded', true);
                        } else {
                            console.log("something fucked up when doing matchcard shit");
                        }
                    },
                    response => {
                        console.log('match failure');
                    }
                );
            },
        },
        mounted() {
        },
        data : function() {
            return {
                champion : {},
            }
        },
        computed :  {
            styleObject : function() {
                return "";
            }
        }
    }
</script>

<style scoped>
    .match-card {
        height: 150px;
    }
</style>