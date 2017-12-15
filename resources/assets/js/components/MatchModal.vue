<template>
    <div id="match-modal-container">
        <div class="ui top attached tabular menu">
            <div class="active item" data-tab="overview">Overview</div>
            <div class="item" data-tab="timeline">Timeline</div>
            <div class="item" data-tab="misc">Misc</div>
        </div>
        <div class="ui bottom attached active tab segment" data-tab="overview" id="overview-container">
            <div class="ui segments">
                <div class="ui horizontal segments">
                    <div class="ui segment blue-team-header">
                        <h3 v-if="blue_team.win == 'Win'">Winners</h3>
                        <h3 v-if="blue_team.win != 'Win'">Losers</h3>
                        <h4>Blue Team</h4>
                    </div>
                    <div class="ui segment red-team-header">
                        <h3 v-if="red_team.win == 'Win'">Winners</h3>
                        <h3 v-if="red_team.win != 'Win'">Losers</h3>
                        <h4>Red Team</h4>
                    </div>
                </div>
                <div class="ui horizontal segments" id="team-stats">
                    <div class="ui segment">stat 1</div>
                    <div class="ui segment">stat 2</div>
                </div>
                <div class="ui horizontal segments summoner-data" v-for="i in 5">
                    <div class="ui segment left-summoner">{{i-1}}</div>
                    <div class="ui segment right-summoner">{{i-1}}</div>
                </div>
            </div>
        </div>
        <div class="ui bottom attached tab segment" data-tab="timeline">
            <h3>Timeline</h3>
        </div>
        <div class="ui bottom attached tab segment" data-tab="misc">
            <h3>Misc</h3>
        </div>
    </div>
</template>

<script>
    import store from '../store.js';
    import mixin from '../mixin.js';

    export default {
        mixins: [
            mixin
        ],
        components: [

        ],
        mounted() {
            // initialize the tabs for the modal
            $('.menu .item').tab();

            console.log('wtf is going on ');
            console.log(this.match);
            this.calculateStats();
        },
        props : [
            "match",
        ],
        data : function() {
            return {
                red_team: {
                    win: false,
                },
                blue_team: {
                    win: false,
                },
                blue_team_participants : [],
                red_team_participants : [],
            }
        },
        computed : {

        },
        methods : {
            calculateStats() {
                console.log('calculating stats');
                this.red_team = this.match.matchTeams[1];
                this.blue_team = this.match.matchTeams[0];

                _.forEach(this.match.matchParticipants, (participant, participant_index) => {
                    if (participant.teamId == "100") {
                        this.blue_team_participants.push(participant);
                    } else {
                        this.red_team_participants.push(participant);
                    }
                });
            },
        },
        watch : {
            match : function(val) {
                this.calculateStats();
            }
        }
    }
</script>

<style scoped>
    #red-team-container {
        background-color: rgb(255, 230, 231);
        background-color: rgb(237, 232, 255);
    }
    .blue-team-header, .red-team-header {
        text-align: center;
    }
    .blue-team-header > *, .red-team-header > * {
        margin: 0px!important
    }
</style>