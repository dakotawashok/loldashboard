<template>
    <div class="summary-contents">
        <h3>Summary Contents</h3>

        <h4>{{currentData.playerStatSummaryType}}</h4>
        <h5 v-if="noData">No stats found for this Queue for the current selected year</h5>
        <ul class="summary-list">
            <li v-for="(key, value) in currentData.aggregatedStats">
                {{value}} : {{key}}
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        props : [
            'type',
            'summaryData',
        ],
        methods : {

        },
        data : function() {
            return {
                noData : false
            }
        },
        computed : {
            currentData : function() {
                for (var summary in this.summaryData) {
                    if (this.summaryData === undefined) {
                        console.log('wow thats weird');
                    } else {
                        if (this.summaryData[summary].playerStatSummaryType == this.type) {
                            if (jQuery.isEmptyObject(this.summaryData[summary].aggregatedStats)) {
                                console.log("No data in this");
                                this.noData = true;
                            } else {
                                this.noData = false;
                            }
                            return this.summaryData[summary];
                        }
                    }
                }
            }
        },
        mounted() {

        }
    }
</script>

<style scoped>
    .summary-list {
        text-align: left;
        list-style: none;
    }
</style>