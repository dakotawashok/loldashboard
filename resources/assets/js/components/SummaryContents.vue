<template>
    <div class="summary-contents container-fluid">
        <div class="row">
            <h3>Summary Contents</h3>
            <label>Graph View: </label> <input type="radio" name="view" value="graph" v-model="view"/><br />
            <label>Table View: </label> <input type="radio" name="view" value="table" v-model="view"/>
        </div>

        <div class="row">
            <template v-if="view == 'table'">
                <div class="col-sm-6 summoner1-stats">
                    <h5 v-if="!summoner1HasData">No stats found for this Queue for the current selected year</h5>
                    <h6 v-if="summoner1HasData">Wins: {{summoner1Wins}}</h6>
                    <h6 v-if="summoner1Losses != undefined && summoner1Losses != '' && summoner1HasData">Losses: {{summoner1Losses}}</h6>
                    <ul class="summary-list">
                        <li v-for="(key, value) in summoner1SummaryData" :style="isGreater(value, key)">
                            {{value}} : {{key}}
                        </li>
                    </ul>
                </div>
                <div class="col-sm-6 summoner2-stats">
                    <h5 v-if="!summoner2HasData">No stats found for this Queue for the current selected year</h5>
                    <h6 v-if="summoner2HasData">Wins: {{summoner2Wins}}</h6>
                    <h6 v-if="summoner2Losses != undefined && summoner2Losses != '' && summoner2HasData">Losses: {{summoner1Losses}}</h6>
                    <ul class="summary-list2">
                        <li v-for="(key, value) in summoner2SummaryData" :style="isGreater(value, key)">
                            {{key}} : {{value}}
                        </li>
                    </ul>
                </div>
            </template>
            <template v-else-if="view == 'graph' && hasData">
                <div v-for="(value, key) in summoner1SummaryData" class="col-sm-4 graph-wrapper">
                    <summarystatsgraph
                            :chartData="createChartData(key)"
                            :dataKey="key"
                    ></summarystatsgraph>
                    <h4>{{key}}</h4>
                </div>
            </template>
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
                        this.summoner1Wins = undefined;
                        this.summoner1Losses = undefined;
                    } else if (summonerNumber == "2") {
                        this.summoner2NoData = true;
                        this.summoner2Wins = undefined;
                        this.summoner2Losses = undefined;
                    }
                }
                store.commit('assignLoading', false);
            },

            summonerHasNoData : function(summonerData, type) {
                var found = false;
                for (var summary in summonerData) {
                    if (summonerData === undefined) {
                        console.log("That's weird");
                    } else {
                        if (summonerData[summary].playerStatSummaryType == type) {
                            if (jQuery.isEmptyObject(summonerData[summary].aggregatedStats || summonerData[summary].aggregatedStats == undefined)) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }

                }
                if (!found) {
                    return true;
                }
            },

            createChartData : function(key) {
                if (this.summoner1SummaryData != undefined && this.summoner2SummaryData != undefined) {
                    var tempData = {};
                    var stat1 = this.summoner1SummaryData[key];
                    var stat2 = this.summoner2SummaryData[key];

                    tempData.labels = [store.state.summoner1.summoner.name, store.state.summoner2.summoner.name];
                    tempData.datasets = [{
                        label: key,
                        backgroundColor: [
                            'rgba(255, 153, 0, 0.2)',
                            'rgba(0, 51, 204, 0.2)',
                        ],
                        borderColor: [
                            'rgba(255, 153, 0, 1)',
                            'rgba(54, 162, 235, 1)',
                        ],
                        borderWidth: 3,
                        data: [stat1, stat2]
                    }];
                    return tempData;
                }

            },

            isGreater : function(key, value) {
                var summoner1Stats = this.getSummaryModeAggregatedStats(store.state.summoner1.summaryData, store.state.currentSubMenuItem, "1");
                var summoner2Stats = this.getSummaryModeAggregatedStats(store.state.summoner2.summaryData, store.state.currentSubMenuItem, "2");

                if (jQuery.isEmptyObject(summoner1Stats) || jQuery.isEmptyObject(summoner2Stats) || summoner1Stats == undefined || summoner2Stats == undefined) {
                    return { 'font-weight' : 'bolder', 'color' : '#3097D1'};
                }

                var tempValue1 = summoner1Stats[key];
                var tempValue2 = summoner2Stats[key];

                if (value > tempValue1 || value > tempValue2) {
                    return { 'font-weight' : 'bolder', 'color' : '#3097D1'};
                } else {
                    return {};
                }
            },
        },
        data : function() {
            return {
                summoner1NoData : false,
                summoner1Wins : "",
                summoner1Losses : "",
                summoner2NoData : false,
                summoner2Wins : "",
                summoner2Losses : "",
                view : "",
            }
        },
        computed : {
            summoner1SummaryData : function() { return this.getSummaryModeAggregatedStats(store.state.summoner1.summaryData, store.state.currentSubMenuItem, "1"); },
            summoner2SummaryData : function() { return this.getSummaryModeAggregatedStats(store.state.summoner2.summaryData, store.state.currentSubMenuItem, "2"); },

            hasData : function() {
                if (!this.summonerHasNoData(store.state.summoner1.summaryData, store.state.currentSubMenuItem) && !this.summonerHasNoData(store.state.summoner2.summaryData, store.state.currentSubMenuItem)) {
                    return true;
                } else {
                    return false;
                }
            },
            summoner1HasData : function() { return (!this.summonerHasNoData(store.state.summoner1.summaryData, store.state.currentSubMenuItem));},
            summoner2HasData : function() { return (!this.summonerHasNoData(store.state.summoner2.summaryData, store.state.currentSubMenuItem));},

            summoner1Loaded : function() { return store.state.summoner1.loaded; },
            summoner2Loaded : function() { return store.state.summoner2.loaded; },
        },
        mounted() {
            console.log('Hello there');
        }
    }
</script>

<style scoped>
    .summary-list {
        text-align: right;
        list-style: none;
    }

    .summary-list2 {
        text-align: left;
        list-style: none;
        padding-left: 0px;
    }

    .graph-wrapper {
        margin-bottom: 15px;

    }
</style>