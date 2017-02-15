<template>
    <div class="stats-view-wrapper container-fluid">
        <h3>{{currentSubMenu}}</h3>
        <div class="row">
            <div class="col-sm-6 summoner1">
                <ul class="average-stats">
                    <li v-for="(key, value) in summoner1AverageRankedData">{{value}} : {{key}}</li>
                </ul>
            </div>
            <div class="col-sm-6 summoner2">
                <ul class="average-stats">
                    <li v-for="(key, value) in summoner2AverageRankedData">{{value}} : {{key}}</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import store from '../store';

    export default {
        props : [

        ],
        data : function() {
            return {

            }
        },
        computed : {
            summoner1AverageRankedData : function() { return store.state.summoner1.stats.rankedStats; },
            summoner2AverageRankedData : function() { return store.state.summoner2.stats.rankedStats; },

            currentSubMenu : function() { return store.state.currentSubMenuItem; },
        },
        methods : {

        },
        mounted () {

        }
    }
</script>

<style scoped>
    .summoner1 > .average-stats {
        list-style: none;
        text-align: left;
    }

    .summoner2 > .average-stats {
        list-style: none;
        text-align: right;
    }
</style>