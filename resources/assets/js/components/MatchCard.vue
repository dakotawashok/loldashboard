<template>
    <div class="ui segment match-card"
         :class="{'green-win' : match_won, 'red-loss' : !match_won, 'loading' : match_loading}"
         @click="showModal()">
        <div class="ui grid">
            <div class="two column row">
                <div class="eight wide column">
                    <img class="ui middle aligned spaced rounded tiny image summoner-champion-icon" :src="champion_image_url">
                    Role: <span class="card-data right">{{card_title}}</span><br />
                    Date: <span class="card-data right">{{date}}</span><br />
                    Duration: <span class="card-data right">{{duration}}</span><br />
                    KDR: <span class="card-data right">{{kill_death_ratio}}</span><br />
                    <div class="summoner-spells-and-items">
                        <div class="summoner-spells">
                            <img class="ui rounded left floated image" v-for="item in summoner_items" :src="item" />
                        </div><br />
                        <div class="summoner-items">
                            <img class="ui rounded left floated image" v-for="item in summoner_spells" :src="item" />
                        </div>
                    </div>
                </div>
                <div class="eight wide column" v-if="!match_loading">
                    <div class="summoner-team-container">
                        <div class="summoner-participant" v-for="participant in summoner_team.participants"
                             @click="loadSummonerFromMatchCard(participant.identity.accountId)">
                            <img class="ui middle aligned spaced rounded tiny image small-champion-icon" :src="getChampionImageUrl(participant.championId)">
                            <span>{{participant.identity.summonerName}}</span>
                        </div>
                    </div>
                    <div class="enemy-team-container">
                        <div class="enemy-participant" v-for="participant in enemy_team.participants"
                             @click="loadSummonerFromMatchCard(participant.identity.accountId)">
                            <span>{{participant.identity.summonerName}}</span>
                            <img class="ui middle aligned spaced rounded tiny image small-champion-icon" :src="getChampionImageUrl(participant.championId)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="match-modal-button">
            <i class="external icon" @click="openMatchModal(match.gameId)"></i>
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

            setSpellData : function(id) { return this.staticSpell(id); },

            getChampionImageUrl : function(id) {
                var tempChamp =  this.staticChampion(id);
                if (tempChamp != undefined && tempChamp.id != undefined) {
                    var parsedChampName = tempChamp.id.split(' ').join('').split('\'').join('');
                    return 'http://ddragon.leagueoflegends.com/cdn/'+this.API_VERSION+'/img/champion/'+parsedChampName+'.png';
                } else {
                    var vue = (this);
                }
            },

            loadIntroductoryData : function() {
                var summonerId = (this.summoner_number == '1' ? this.summoner1.summoner.accountId : this.summoner2.summoner.accountId);
                var summonerTeamId = 0;
                var enemyTeamId = 0;
                this.match_loading = true;

                // for every participant, add the id to that object
                _.forEach(this.defined_match.participants, (participant) => {
                    _.forEach(this.defined_match.participant_identities, (identitity) => {
                        if (parseInt(participant.participantId) == parseInt(identitity.participantId)) {
                            participant.identity = identitity;
                        }
                    });
                    if (participant.identity.accountId == summonerId) {
                        this.summoner_participant_data = participant;
                    }
                });

                // parse all the json data for the participants
                this.parseMatchParticipantData();

                // load the teams into their respective object
                if (this.summoner_participant_data.teamId === '100') {
                    summonerTeamId = 100;
                    enemyTeamId = 200;
                } else {
                    summonerTeamId = 200;
                    enemyTeamId = 100;
                }

                this.summoner_team = _.find(this.defined_match.teams, function(o) {
                       return (parseInt(o.teamId) === summonerTeamId);
                });
                this.enemy_team = _.find(this.defined_match.teams, function(o) {
                    return (parseInt(o.teamId) === enemyTeamId);
                });

                // put each participant into their respective team
                // first the summoner team
                var summoner_participant_array = [];
                var enemy_participant_array = [];
                _.forEach(this.defined_match.participants, (participant) => {
                    if (participant.teamId == summonerTeamId) {
                        summoner_participant_array.push(participant);
                    } else {
                        enemy_participant_array.push(participant);
                    }
                });
                this.summoner_team.participants = summoner_participant_array;
                this.enemy_team.participants = enemy_participant_array;


                // determine if the summoner won this match or not
                if (this.summoner_team.win === 'Win') {
                    this.match_won = true;
                } else {
                    this.match_won = false;
                }

                // get the summoner stats for this game
                this.stats = JSON.parse(this.summoner_participant_data.stats);
                this.match_loading = false;
            },

            parseMatchParticipantData : function() {
                _.forEach(this.defined_match.participants, (participant) => {
                    // TODO:: FIX THIS SHIT
                    // json parse out all the data! If they're not a string, then they're already parsed or something not sure why but fuck it I'll figure it out later
                    if (typeof participant.masteries === 'string') {
                        participant.stats = (participant.stats != '' ? JSON.parse(participant.stats) : []);
                    }
                });
            },

            showModal : function() {
//                $('.ui.modal').modal('show');
            },

            loadSummonerFromMatchCard : function(accountId) {
                if (this.summoner_number == '1') {
                    store.commit('assignSummonerAccountId', {summonerNumber: 2, accountId: accountId});
                    this.getAllSummonerData('2', true);
                } else if (this.summoner_number == '2') {
                    store.commit('assignSummonerAccountId', {summonerNumber: 1, accountId: accountId});
                    this.getAllSummonerData('1', true);
                }

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

                match_loading: true,
            }
        },
        computed :  {
            champion_image_url : function() {
                if (this.champion != undefined && this.champion.id != undefined) {
                    var parsedChampName = this.champion.id.split(' ').join('').split('\'').join('');
                    return 'http://ddragon.leagueoflegends.com/cdn/'+this.API_VERSION+'/img/champion/'+parsedChampName+'.png';
                } else {
                    return 'http://ddragon.leagueoflegends.com/cdn/'+this.API_VERSION+'/img/champion/aatrox.png';
                }
            },

            date : function() {
                return moment.unix(this.match.timestamp / 1000).format("MM/DD/YYYY");
            },

            duration : function() {
                if (this.defined_match != undefined && this.defined_match.gameDuration != undefined) {
                    var seconds = parseInt(this.defined_match.gameDuration) % 60;
                    var minutes = (parseInt(this.defined_match.gameDuration) - seconds) / 60;

                    return minutes + 'm ' + seconds + 's';
                }
            },

            defined_match : function() {
                return this.match.defined_match;
            },

            kill_death_ratio : function() {
                this.stats.kills = ((this.stats.kills != undefined) ? this.stats.kills : 0);
                this.stats.deaths = ((this.stats.deaths != undefined) ? this.stats.deaths : 0);
                this.stats.assists = ((this.stats.assists != undefined) ? this.stats.assists : 0);
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
            },

            summoner_items : function() {
                var item_array = [];
                if (this.stats.item0 != undefined && this.stats.item0 != '') {item_array.push(this.stats.item0) }
                if (this.stats.item1 != undefined && this.stats.item1 != '') {item_array.push(this.stats.item1) }
                if (this.stats.item2 != undefined && this.stats.item2 != '') {item_array.push(this.stats.item2) }
                if (this.stats.item3 != undefined && this.stats.item3 != '') {item_array.push(this.stats.item3) }
                if (this.stats.item4 != undefined && this.stats.item4 != '') {item_array.push(this.stats.item4) }
                if (this.stats.item5 != undefined && this.stats.item5 != '') {item_array.push(this.stats.item5) }
                _.forEach(item_array, (item, key) => {
                    item_array[key] = 'http://ddragon.leagueoflegends.com/cdn/'+this.API_VERSION+'/img/item/'+item+'.png';
                });
                return item_array;
            },

            summoner_spells : function() {
                var spell_array = [];
                var spell1 = this.setSpellData(this.summoner_participant_data.spell1Id);
                var spell2 = this.setSpellData(this.summoner_participant_data.spell2Id);
                if (this.summoner_participant_data.spell1Id != undefined && this.summoner_participant_data.spell1Id != '') {
                    spell_array.push('http://ddragon.leagueoflegends.com/cdn/'+this.API_VERSION+'/img/spell/'+spell1.id+'.png');
                }
                if (this.summoner_participant_data.spell2Id != undefined && this.summoner_participant_data.spell2Id != '') {
                    spell_array.push('http://ddragon.leagueoflegends.com/cdn/'+this.API_VERSION+'/img/spell/'+spell2.id+'.png');
                }
                if (this.stats.item6 != undefined && this.stats.item6 != '') {
                    spell_array.push('http://ddragon.leagueoflegends.com/cdn/'+this.API_VERSION+'/img/item/'+this.stats.item6 +'.png');
                }
                return spell_array;
            },

        },
        watch : {
            'match' : function(newMatch) {
                this.setChampionData();
                this.loadIntroductoryData();
            }
        }
    }
</script>

<style scoped>
    .match-card {
        font-size: 12px;
        line-height: 12px;
        height: 134px;
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
    .card-data.right {
        float: right;
    }

    .summoner-spells-and-items {
        float: left;
    }

    .summoner-items, .summoner-spells {
        margin-top: 5px;
        margin-bottom: 0px;
        display: block;
        float: left;
        width: 100%;
    }

    .summoner-items > img, .summoner-spells > img {
        width: 25px!important;
        height: auto;
        margin-right: 2px!important;
        margin-bottom: 0px!important;
    }
    .summoner-team-container, .enemy-team-container {
        margin: 0px!important;
        padding: 0px!important;
        width: 50%;
        height: 100%;
        position: absolute;
    }
    .summoner-team-container {
        left: 0px;
    }
    .enemy-team-container {
        right: 0px;
    }
    .summoner-participant, .enemy-participant {
        display: inline-block;
        width: 100%;
        height: 22px;
    }
    .summoner-participant > span {
        position: absolute;
        left: 25px;
        padding-left: 5px;
        padding-top: 2px;
    }
    .summoner-participant > .small-champion-icon {
        width: 20px!important;
        height: auto;
        position: absolute;
        left: 0;
    }
    .enemy-participant > span {
        position: absolute;
        right: 45px;
        padding-right: 10px;
        padding-top: 2px;
    }
    .enemy-participant > .small-champion-icon {
        width: 20px!important;
        height: auto;
        position: absolute;
        right: 0;
        margin-right: 28px;
    }
    .small-champion-icon {
        width: 22px!important;
        cursor: pointer;
    }
</style>