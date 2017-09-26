<template>
    <div class="ui segment match-card" :class="{'green-win' : match_won, 'red-loss' : !match_won}" @click="showModal()">
        <div class="ui grid">
            <div class="two column row">
                <div class="ten wide column">
                    <img class="ui top aligned spaced rounded tiny image summoner-champion-icon" :src="champion_image_url">
                    <span>Role: {{card_title}}</span><br />
                    <span>Date: {{date}}</span><br />
                    <span>Duration: {{duration}}</span><br />
                    <span>KDR: {{kill_death_ratio}}</span><br />
                </div>
                <div class="six wide column">

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import store from '../store.js';
    import mixin from '../mixin.js';
    var moment = require('moment');

    export default {
        props : [
            "match",
            "match_type",
            "summoner_number"
        ],
        mixins: [
            mixin
        ],
        methods : {
            setChampionData : function() { this.champion = this.staticChampion(this.match.champion) },

            loadIntroductoryData : function() {
                var summonerId = (this.summoner_number == '1' ? this.summoner1.summoner.accountId : this.summoner2.summoner.accountId);
                var summonerTeamId = 0;
                var enemyTeamId = 0;

                // for every participant, add the id to that object
                _.forEach(this.defined_match.participants, (participant) => {
                    _.forEach(this.defined_match.participant_identities, (identitity) => {
                        if (participant.participantId === identitity.participantId) {
                            participant.identity = identitity;
                        }
                    });
                    if (participant.identity.accountId === summonerId) {
                        this.summoner_participant_data = participant;
                    }
                });

                // parse all the json data for the participants
                this.parseMatchParticipantData();

                // load the teams into their respective object
                if (this.summoner_participant_data.teamId === '100') {
                    summonerTeamId = '100';
                    enemyTeamId = '200';
                } else {
                    summonerTeamId = '200';
                    enemyTeamId = '100';
                }
                this.summoner_team = _.find(this.defined_match.teams, ['teamId', summonerTeamId]);
                this.enemy_team = _.find(this.defined_match.teams, ['teamId', enemyTeamId]);


                // determine if the summoner won this match or not
                if (this.summoner_team.win === 'Win') {
                    this.match_won = true;
                } else {
                    this.match_won = false;
                }

                // get the summoner stats for this game
                this.stats = this.summoner_participant_data.stats;
            },

            parseMatchParticipantData : function() {
                _.forEach(this.defined_match.participants, (participant) => {
                    // json parse out all the data!
                    participant.masteries = (participant.masteries != '' ? JSON.parse(participant.masteries) : []);
                    participant.runes = (participant.runes != '' ? JSON.parse(participant.runes) : []);
                    participant.stats = (participant.stats != '' ? JSON.parse(participant.stats) : []);
                    participant.timeline = (participant.timeline != '' ? JSON.parse(participant.timeline) : []);
                });
            },

            showModal : function() {
                $('.ui.modal').modal('show');
            },
        },
        mounted() {
            this.setChampionData();
            this.loadIntroductoryData();
        },
        data : function() {
            return {
                champion : {},
                match_won : false,
                summoner_team : {},
                enemy_team : {},
                stats: {},
                summoner_participant_data : {},
            }
        },
        computed :  {
            styleObject : function() {
                return "";
            },

            champion_image_url : function() {
                if (this.champion != undefined && this.champion.name != undefined) {
                    var parsedChampName = this.champion.name.split(' ').join('');
                    return 'http://ddragon.leagueoflegends.com/cdn/7.18.1/img/champion/'+parsedChampName+'.png';
                } else {
                    return 'http://ddragon.leagueoflegends.com/cdn/7.18.1/img/champion/aatrox.png';
                }
            },

            date : function() {
                return moment.unix(this.match.timestamp / 1000).format("MM/DD/YYYY");
            },

            duration : function() {
                if (this.defined_match != undefined && this.defined_match.gameDuration != undefined) {
                    var seconds = parseInt(this.defined_match.gameDuration) % 60;
                    var minutes = (parseInt(this.defined_match.gameDuration) - seconds) / 60;

                    return minutes + ' minutes ' + seconds + ' seconds';
                }
            },

            defined_match : function() {
                return this.match.defined_match;
            },

            kill_death_ratio : function() {
                return this.stats.kills + ' / ' + this.stats.deaths + ' / ' + this.stats.assists;
            },

            card_title : function() {
                var lane = '';
                switch (this.match.lane) {
                    case 'TOP' :
                        lane = "Top";
                        break;
                    case 'JUNGLE' :
                        lane = "Jungle";
                        break;
                    case 'BOTTOM' :
                        lane = (this.match.role == 'DUO_SUPPORT' ? 'Support' : 'AD Carry');
                        break;
                    case 'MID' :
                        lane = "Mid";
                        break;
                    default :
                        lane = "";
                        break;
                }
                return lane + " " + this.champion.name;
            }

        },
        watch : {
            'match' : function(newMatch) {
                this.setChampionData();
            }
        }
    }
</script>

<style scoped>
    .match-card {
        font-size: 12px;
    }
    .summoner-champion-icon {
        float: left;
        margin-right: 15px!important;
    }
    .green-win {
        background-color: #beffbe!important;
    }
    .red-loss {
        background-color: #ffc3be!important;
    }
</style>