<template>
    <div class="ui inverted segment" :class="{'green' : match_won, 'red' : !match_won}" @click="showModal()">
        <div class="ui grid">
            <div class="two column row">
                <div class="four wide column">
                    <img class="ui rounded tiny image" :src="champion_image_url">
                </div>
                <div class="twelve wide column">
                    <div class="content">
                        <span>{{match.role + ' ' + match.lane}}</span><br />
                        <span>{{date}}</span><br />
                        <span>{{duration}}</span><br />
                    </div>
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
                this.stats = JSON.parse(this.summoner_participant_data.stats);
            },

            showModal : function() {
                $('.ui.modal').modal()
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
        },
        watch : {
            'match' : function(newMatch) {
                this.setChampionData();
            }
        }
    }
</script>

<style scoped>

</style>