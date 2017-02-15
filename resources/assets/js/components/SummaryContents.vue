<template>
    <div class="summary-contents container-fluid">
        <div class="row">
            <h3>Summary Contents</h3>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <canvas id="summary-contents-chart" width="400" height="400"></canvas>
            </div>
            <div class="col-sm-6 summoner1-stats">
                <h5 v-if="summoner1NoData">No stats found for this Queue for the current selected year</h5>
                <h6 v-if="!summoner1NoData">Wins: {{summoner1Wins}}</h6>
                <ul class="summary-list">
                    <li v-for="(key, value) in summoner1SummaryData">
                        {{value}} : {{key}}
                    </li>
                </ul>
            </div>
            <div class="col-sm-6 summoner2-stats">
                <h5 v-if="summoner2NoData">No stats found for this Queue for the current selected year</h5>
                <h6 v-if="!summoner2NoData">Wins: {{summoner2Wins}}</h6>
                <ul class="summary-list2">
                    <li v-for="(key, value) in summoner2SummaryData">
                        {{value}} : {{key}}
                    </li>
                </ul>
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
            // This method gets the aggregated stats for a specific summoner, and if the stats aren't find for the specific type,
            // it sets the nodata flag as true.
            getSummaryModeAggregatedStats : function(summonerData, type, summonerNumber) {
                var found = false;
                for (var summary in summonerData) {
                    if (summonerData === undefined) {
                        console.log("That's weird");
                    } else {
                        if (summonerData[summary].playerStatSummaryType == type) {
                            if (jQuery.isEmptyObject(summonerData[summary].aggregatedStats || summonerData[summary].aggregatedStats == undefined)) {
                                if (summonerNumber == "1") {
                                    this.summoner1NoData = true;
                                } else if (summonerNumber == "2") {
                                    this.summoner2NoData = true;
                                }
                            } else {
                                if (summonerNumber == "1") {
                                    this.summoner1NoData = false;
                                    this.summoner1Wins = summonerData[summary].wins;
                                    this.summoner1Losses = summonerData[summary].losses;
                                } else if (summonerNumber == "2") {
                                    this.summoner2NoData = false;
                                    this.summoner2Wins = summonerData[summary].wins;
                                    this.summoner2Losses = summonerData[summary].losses;
                                }
                            }
                            return summonerData[summary].aggregatedStats;

                        }
                    }

                }
                if (!found) {
                    if (summonerNumber == "1") {
                        this.summoner1NoData = true;
                    } else if (summonerNumber == "2") {
                        this.summoner2NoData = true;
                    }
                }
                store.commit('assignLoading', false);
            }
        },
        data : function() {
            return {
                summoner1NoData : false,
                summoner1Wins : "",
                summoner1Losses : "",
                summoner2NoData : false,
                summoner2Wins : "",
                summoner2Losses : "",
            }
        },
        computed : {
            summoner1SummaryData : function() { return this.getSummaryModeAggregatedStats(store.state.summoner1.summaryData, store.state.currentSubMenuItem, "1"); },
            summoner2SummaryData : function() { return this.getSummaryModeAggregatedStats(store.state.summoner2.summaryData, store.state.currentSubMenuItem, "2"); },

            summoner1Loaded : function() { return store.state.summoner1.loaded; },
            summoner2Loaded : function() { return store.state.summoner2.loaded; },
        },
        mounted() {
            var ctx = $('#summary-contents-chart');
            var summaryChart = new Chart(ctx, {
                type : 'bar',
                data: {
                    labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
                    datasets: [{
                        label: '# of Votes',
                        data: [12, 19, 3, 5, 2, 3],
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
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true
                            }
                        }]
                    }
                }
            });
        }
    }
</script>

<style scoped>
    .summary-list {
        text-align: left;
        list-style: none;
    }

    .summary-list2 {
        text-align: right;
        list-style: none;
    }
</style>