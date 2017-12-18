<template>
    <div class="ui fullscreen modal" id="match-modal">
        <div class="header">
            Match {{match.gameId}}
        </div>
        <div class="content">
            <div class="ui top attached tabular menu">
                <div class="active item" data-tab="overview">Overview</div>
                <div class="item" data-tab="timeline">Timeline</div>
                <div class="item" data-tab="misc">Misc</div>
            </div>
            <div class="ui bottom attached active tab segment" data-tab="overview" id="overview-container">
                <div class="ui segments" v-if="match.gameId != 0">
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
                        <div class="ui segment">
                            <div class="ui five column grid stats-container">
                                <div class="column">
                                    <span>First Blood: <i v-if="blue_team.firstBlood == '1'" class="green checkmark icon"></i><i v-if="blue_team.firstBlood != '1'" class="red remove icon"></i></span>
                                </div>
                                <div class="column">
                                    <span>First Dragon: <i v-if="blue_team.firstDragon == '1'" class="green checkmark icon"></i><i v-if="blue_team.firstDragon != '1'" class="red remove icon"></i></span><br />
                                    <span>Dragon Kills: {{blue_team.dragonKills}}</span>
                                </div>
                                <div class="column">
                                    <span>First Baron: <i v-if="blue_team.firstBaron == '1'" class="green checkmark icon"></i><i v-if="blue_team.firstBaron != '1'" class="red remove icon"></i></span><br />
                                    <span>Baron Kills: {{blue_team.baronKills}}</span>
                                </div>
                                <div class="column">
                                    <span>First Tower: <i v-if="blue_team.firstTower == '1'" class="green checkmark icon"></i><i v-if="blue_team.firstTower != '1'" class="red remove icon"></i></span><br />
                                    <span>Tower Kills: {{blue_team.towerKills}}</span>
                                </div>
                                <div class="column">
                                    <span>First Inhibitor: <i v-if="blue_team.firstInhibitor == '1'" class="green checkmark icon"></i><i v-if="blue_team.firstInhibitor != '1'" class="red remove icon"></i></span><br />
                                    <span>Inhibitor Kills: {{blue_team.inhibitorKills}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="ui segment">
                            <div class="ui five column grid stats-container">
                                <div class="column">
                                    <span>First Blood: <i v-if="red_team.firstBlood == '1'" class="green checkmark icon"></i><i v-if="red_team.firstBlood != '1'" class="red remove icon"></i></span>
                                </div>
                                <div class="column">
                                    <span>First Dragon: <i v-if="red_team.firstDragon == '1'" class="green checkmark icon"></i><i v-if="red_team.firstDragon != '1'" class="red remove icon"></i></span><br />
                                    <span>Dragon Kills: {{red_team.dragonKills}}</span>
                                </div>
                                <div class="column">
                                    <span>First Baron: <i v-if="red_team.firstBaron == '1'" class="green checkmark icon"></i><i v-if="red_team.firstBaron != '1'" class="red remove icon"></i></span><br />
                                    <span>Baron Kills: {{red_team.baronKills}}</span>
                                </div>
                                <div class="column">
                                    <span>First Tower: <i v-if="red_team.firstTower == '1'" class="green checkmark icon"></i><i v-if="red_team.firstTower != '1'" class="red remove icon"></i></span><br />
                                    <span>Tower Kills: {{red_team.towerKills}}</span>
                                </div>
                                <div class="column">
                                    <span>First Inhibitor: <i v-if="red_team.firstInhibitor == '1'" class="green checkmark icon"></i><i v-if="red_team.firstInhibitor != '1'" class="red remove icon"></i></span><br />
                                    <span>Inhibitor Kills: {{red_team.inhibitorKills}}</span>
                                </div>
                            </div>
                        </div>
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
            $('#match-modal').modal({
                closable  : true,
                detachable: true,
                onHidden: () => {

                }
            })

            this.calculateStats();

            // initialize the tabs for the modal
            $('.menu .item').tab();
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
    .stats-container > .column {
        padding: 5px 10px!important;
        font-size: 10px;
    }
    .stats-container > * > span > i {
        display: inline;
    }
</style>