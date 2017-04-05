<template>
    <div class="ranked-matchlist-wrapper container-fluid">
        <div class="row ranked-match-view container-fluid col-sm-12" v-if="matchLoaded">
            <h3 class="row">Match Data: </h3>
            <template v-if="summoner1MatchLoaded || summoner2MatchLoaded">
                <div class="row">
                    <rankedmatchlistgraph :chartData="graphData"></rankedmatchlistgraph>
                </div>
            </template>
            <div class="row">
                <div class="summoner1-match col-sm-6" v-if="summoner1MatchLoaded">
                    <ul>
                        <h3>Participants: </h3>
                        <li v-for="(value, key) in summoner1Match.participants">{{key}} : {{value}}</li><br />
                        <h3>Participants Identities: </h3>
                        <li v-for="(value, key) in summoner1Match.participantIdentities">{{key}} : {{value}}</li><br />
                        <h3>Teams: </h3>
                        <li v-for="(value, key) in summoner1Match.teams">{{key}} : {{value}}</li><br />
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
                graphSelected : ['kills', 'deaths', 'assists'],
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

            graphData : function() {
                var tempChartData = {
                    'labels' : [],
                    'datasets' : []
                };
                var summoner1Stats = {'participants' : {}, 'participantIdentities' : {}, 'teams' : {}};
                var summoner2Stats = {'participants' : {}, 'participantIdentities' : {}, 'teams' : {}};
                if (store.state.summoner1.matchLoaded) {
                    summoner1Stats.participants = store.state.summoner1.selectedMatch.participants;
                    summoner1Stats.participantIdentities = store.state.summoner1.selectedMatch.participantIdentities;
                    summoner1Stats.teams = store.state.summoner1.selectedMatch.teams;
                }
                if (store.state.summoner2.matchLoaded) {
                    summoner2Stats.participants = store.state.summoner2.selectedMatch.participants;
                    summoner2Stats.participantIdentities = store.state.summoner2.selectedMatch.participantIdentities;
                    summoner2Stats.teams = store.state.summoner2.selectedMatch.teams;
                }

                for (var participant in summoner1Stats.participantIdentities) {
                    console.log(summoner1Stats.participantIdentities[participant]);
                    tempChartData.labels.push(summoner1Stats.participantIdentities[participant].player.summonerName)
                }


                for (var stat in this.graphSelected) {
                    switch (this.graphSelected[stat]) {
                        case 'kills' :
                            var tempDataSet = {
                                label: 'Kills',
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(153, 102, 255, 0.2)',
                                    'rgba(255, 159, 64, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(255,99,132,1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)'
                                ],
                                borderWidth: 1,
                                data: [],

                            };
                            for (var participant in summoner1Stats.participants) {
                                console.log(summoner1Stats.participants[participant]);
                                tempDataSet.data.push(summoner1Stats.participants[participant].stats.kills);
                            }
                            tempChartData.datasets.push(tempDataSet);
                            break;
                        case 'deaths' :
                            var tempDataSet = {
                                label: 'Deaths',
                                backgroundColor: [
                                    'rgba(255, 99, 152, 0.2)',
                                    'rgba(54, 162, 255, 0.2)',
                                    'rgba(255, 206, 106, 0.2)',
                                    'rgba(75, 192, 212, 0.2)',
                                    'rgba(153, 102, 275, 0.2)',
                                    'rgba(255, 159, 84, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(255,99,132,1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)'
                                ],
                                borderWidth: 1,
                                data: []
                            };
                            for (var participant in summoner1Stats.participants) {
                                console.log(summoner1Stats.participants[participant]);
                                tempDataSet.data.push(summoner1Stats.participants[participant].stats.deaths);
                            }
                            tempChartData.datasets.push(tempDataSet);
                            break;
                        case 'assists' :
                            var tempDataSet = {
                                label: 'Assists',
                                backgroundColor: [
                                    'rgba(255, 99, 112, 0.2)',
                                    'rgba(54, 162, 215, 0.2)',
                                    'rgba(255, 206, 66, 0.2)',
                                    'rgba(75, 192, 172, 0.2)',
                                    'rgba(153, 102, 235, 0.2)',
                                    'rgba(255, 159, 44, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(255,99,132,1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)'
                                ],
                                borderWidth: 1,
                                data: []
                            };
                            for (var participant in summoner1Stats.participants) {
                                console.log(summoner1Stats.participants[participant]);
                                tempDataSet.data.push(summoner1Stats.participants[participant].stats.assists);
                            }
                            tempChartData.datasets.push(tempDataSet);
                            break;
                    }
                }


                switch (stat) {
                    case '' :
                        for (var participant in summoner1Stats.participantIdentities) {
                            console.log(summoner1Stats.participantIdentities[participant]);
                            tempChartData.labels.push(summoner1Stats.participantIdentities[participant].player.summonerName)
                        }
                        var tempDataSet = {
                            label: 'Kills',
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.2)',
                                'rgba(54, 162, 235, 0.2)',
                                'rgba(255, 206, 86, 0.2)',
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                                'rgba(255, 159, 64, 0.2)'
                            ],
                            borderColor: [
                                'rgba(255,99,132,1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1,
                            data: []
                        };
                        for (var participant in summoner1Stats.participants) {
                            console.log(summoner1Stats.participants[participant]);
                            tempDataSet.data.push(summoner1Stats.participants[participant].stats.kills);
                        }
                        tempChartData.datasets.push(tempDataSet);
                        break;
                }

                return tempChartData;

            },
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