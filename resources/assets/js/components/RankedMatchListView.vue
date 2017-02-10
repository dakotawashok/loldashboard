<template>
    <div class="ranked-matchlist-wrapper container-fluid">
        <div class="row">
            <div class="col-sm-6 summoner1-ranked-matchlist">
                <div v-for="match in summoner1RankedMatchList"><matchcard v-bind:match="match"></matchcard></div>
            </div>
            <div class="col-sm-6 summoner2-ranked-matchlist">
                <div v-for="match in summoner2RankedMatchList"><matchcard v-bind:match="match"></matchcard></div>
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
        },
        mounted() {

        }
    }
</script>

<style scoped>

</style>