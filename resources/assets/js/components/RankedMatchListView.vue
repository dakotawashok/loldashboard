<template>
    <div class="ranked-matchlist-wrapper container-fluid">
        <div class="row ranked-match-view container-fluid col-sm-12" v-if="matchLoaded">
            <h3 class="row">Match Data: </h3>
            <div class="row">
                <div class="summoner1-match col-sm-6" v-if="summoner1MatchLoaded">
                    <ul>
                        <li v-for="stat in summoner1Match">{{stat}}</li>
                    </ul>
                </div>
                <div class="summoner2-match col-sm-6" v-if="summoner2MatchLoaded">
                    <ul>
                        <li v-for="stat in summoner2Match">{{stat}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 summoner1-ranked-matchlist">
                <div v-for="match in summoner1RankedMatchList"><matchcard v-bind:match="match" :summonerId="summoner1Id"></matchcard></div>
            </div>
            <div class="col-sm-6 summoner2-ranked-matchlist">
                <div v-for="match in summoner2RankedMatchList"><matchcard v-bind:match="match" :summonerId="summoner2Id"></matchcard></div>
            </div>
        </div>
    </div>
</template>

<script>
    import store from '../store.js';

    export default {
        props : [

        ],
        methods : {

        },
        data : function() {
            return {

            }
        },
        computed : {
            summoner1RankedMatchList : function() { return store.state.summoner1.rankedMatchList.matches; },
            summoner2RankedMatchList : function() { return store.state.summoner2.rankedMatchList.matches; },

            summoner1Loaded : function() { return store.state.summoner1.loaded; },
            summoner2Loaded : function() { return store.state.summoner2.loaded; },

            summoner1Id : function() { return store.state.summoner1.summoner.id; },
            summoner2Id : function() { return store.state.summoner2.summoner.id; },

            matchLoaded : function() { return (store.state.summoner1.matchLoaded || store.state.summoner2.matchLoaded); },
            summoner1MatchLoaded : function() { return store.state.summoner1.matchLoaded; },
            summoner2MatchLoaded : function() { return store.state.summoner2.matchLoaded; },

            summoner1Match : function() { return store.state.summoner1.selectedMatch; },
            summoner2Match : function() { return store.state.summoner2.selectedMatch; },
        },
        mounted() {

        }
    }
</script>

<style scoped>
    .ranked-match-view {
        margin-bottom: 30px;
    }

    li {
        list-style: none;
        text-align: left;
    }
</style>