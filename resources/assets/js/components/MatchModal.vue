<template>
    <div class="ui fullscreen modal" id="match-modal" style="height: 1000px;">
        <div class="header">
            <h3>Match {{match.gameId}}</h3>
            <h4>{{date}}</h4>
            <h4>{{duration}}</h4>
            <h4>{{staticMatchmakingQueue(match.queueId).map}}</h4>
            <h4>{{staticMatchmakingQueue(match.queueId).description}}</h4>
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
                        <div class="ui segment left-summoner">
                            <div class="ui four column grid">
                                <div class="five wide column picture-column">
                                    <div class="ui grid">
                                        <div class="ui seven wide column">
                                            <div class="summoner-level-label">
                                                <a class="ui mini red circular label">{{blue_team_participants[i-1].stats.champLevel}}</a>
                                            </div>
                                            <img class="ui tiny image participant-summonerimage" :src="getChampionImageUrl(blue_team_participants[i-1].championId)">
                                            <img class="ui tiny image participant-underimage" :src="getSpellImageUrl(blue_team_participants[i-1].spell1Id)">
                                            <img class="ui tiny image participant-underimage" :src="getSpellImageUrl(blue_team_participants[i-1].spell2Id)">
                                            <img class="ui tiny image participant-underimage" :src="getItemImageUrl(blue_team_participants[i-1].stats.item6)">
                                        </div>
                                        <div class="ui nine wide column">
                                            <span class="summoner-info summoner-name">{{blue_team_participants[i-1].participantIdentity.summonerName}}</span>
                                            <a class="ui yellow label summoner-info" v-html="findParticipantKDA(blue_team_participants[i-1])"></a>
                                            <a class="ui olive label summoner-info" v-html="findParticipantCS(blue_team_participants[i-1])"></a>
                                            <a class="ui blue label summoner-info" v-html="findParticipantGold(blue_team_participants[i-1])"></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="two wide column item-column">
                                    <div class="ui grid">
                                        <div class="ui eight wide column">
                                            <img class="ui small rounded image participant-item-image" :src="getParticipantImageUrlFromIndex(blue_team_participants[i-1], n)" v-for="n in 6" v-if="n % 2 != 0">
                                        </div>
                                        <div class="ui eight wide column">
                                            <img class="ui small rounded image participant-item-image" :src="getParticipantImageUrlFromIndex(blue_team_participants[i-1], n)" v-for="n in 6" v-if="n % 2 == 0">
                                        </div>
                                    </div>
                                </div>
                                <div class="nine wide column">
                                    <a class="ui grey label summoner-info">
                                        Damage Dealt to Champions:
                                        <div class="detail">
                                            {{blue_team_participants[i-1].stats.totalDamageDealtToChampions }}
                                        </div>
                                    </a>
                                    <a class="ui label summoner-info">
                                        Damage Taken:
                                        <div class="detail">
                                            {{blue_team_participants[i-1].stats.totalDamageTaken }}
                                        </div>
                                    </a>
                                    <a class="ui grey label summoner-info">
                                        Total Amount Healed:
                                        <div class="detail">
                                            {{blue_team_participants[i-1].stats.totalHeal }}
                                        </div>
                                    </a>
                                    <a class="ui label summoner-info">
                                        Damage Dealt to Objectives:
                                        <div class="detail">
                                            {{blue_team_participants[i-1].stats.damageDealtToObjectives }}
                                        </div>
                                    </a>
                                    <a class="ui grey label summoner-info">
                                        Damage Self Mitigated:
                                        <div class="detail">
                                            {{blue_team_participants[i-1].stats.damageSelfMitigated }}
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="ui segment right-summoner">
                            <div class="ui four column grid">
                                <div class="five wide column picture-column">
                                    <div class="ui grid">
                                        <div class="ui seven wide column">
                                            <div class="summoner-level-label">
                                                <a class="ui mini red circular label">{{blue_team_participants[i-1].stats.champLevel}}</a>
                                            </div>
                                            <img class="ui tiny image participant-summonerimage" :src="getChampionImageUrl(red_team_participants[i-1].championId)">
                                            <img class="ui tiny image participant-underimage" :src="getSpellImageUrl(red_team_participants[i-1].spell1Id)">
                                            <img class="ui tiny image participant-underimage" :src="getSpellImageUrl(red_team_participants[i-1].spell2Id)">
                                            <img class="ui tiny image participant-underimage" :src="getItemImageUrl(red_team_participants[i-1].stats.item6)">
                                        </div>
                                        <div class="ui nine wide column">
                                            <span class="summoner-info summoner-name">{{red_team_participants[i-1].participantIdentity.summonerName}}</span>
                                            <a class="ui yellow label summoner-info" v-html="findParticipantKDA(red_team_participants[i-1])"></a>
                                            <a class="ui olive label summoner-info" v-html="findParticipantCS(red_team_participants[i-1])"></a>
                                            <a class="ui blue label summoner-info" v-html="findParticipantGold(red_team_participants[i-1])"></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="two wide column item-column" item-column>
                                    <div class="ui grid">
                                        <div class="ui eight wide column">
                                            <img class="ui small rounded image participant-item-image" :src="getParticipantImageUrlFromIndex(red_team_participants[i-1], n)" v-for="n in 6" v-if="n % 2 != 0">
                                        </div>
                                        <div class="ui eight wide column">
                                            <img class="ui small rounded image participant-item-image" :src="getParticipantImageUrlFromIndex(red_team_participants[i-1], n)" v-for="n in 6" v-if="n % 2 == 0">
                                        </div>
                                    </div>
                                </div>
                                <div class="nine wide column">
                                    <a class="ui grey label summoner-info">
                                        Damage Dealt to Champions:
                                        <div class="detail">
                                            {{red_team_participants[i-1].stats.totalDamageDealtToChampions }}
                                        </div>
                                    </a>
                                    <a class="ui label summoner-info">
                                        Damage Taken:
                                        <div class="detail">
                                            {{red_team_participants[i-1].stats.totalDamageTaken }}
                                        </div>
                                    </a>
                                    <a class="ui grey label summoner-info">
                                        Total Amount Healed:
                                        <div class="detail">
                                            {{red_team_participants[i-1].stats.totalHeal }}
                                        </div>
                                    </a>
                                    <a class="ui label summoner-info">
                                        Damage Dealt to Objectives:
                                        <div class="detail">
                                            {{red_team_participants[i-1].stats.damageDealtToObjectives }}
                                        </div>
                                    </a>
                                    <a class="ui grey label summoner-info">
                                        Damage Self Mitigated:
                                        <div class="detail">
                                            {{red_team_participants[i-1].stats.damageSelfMitigated }}
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
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

    var moment = require('moment');

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
                    total_data: {
                        assists: 0,
                        damageDealtToObjectives: 0,
                        damageDealtToTurrets: 0,
                        damageSelfMitigated: 0,
                        deaths: 0,
                        goldEarned: 0,
                        goldSpent: 0,
                        inhibitorKills: 0,
                        kills: 0,
                        largestCriticalStrike: 0,
                        largetsKillingSpree: 0,
                        largestMultiKill: 0,
                        longestTimeSpentLiving: 0,
                        magicDamageDealt: 0,
                        magicDamageDealtToChampions: 0,
                        magicalDamageTaken: 0,
                        neutralMinionsKilled: 0,
                        neutralMinionsKilledEnemyJungle: 0,
                        neutralMinionsKilledTeamJungle: 0,
                        pentaKills: 0,
                        physicalDamageDealt: 0,
                        physicalDamageDealtToChampions: 0,
                        physicalDamageTaken: 0,
                        quadraKills: 0,
                        sightWardsBoughtInGame: 0,
                        timeCCingOthers: 0,
                        totalDamageDealt: 0,
                        totalDamageDealtToChampions: 0,
                        totalDamageTaken: 0,
                        totalHeal: 0,
                        totalMinionsKilled: 0,
                        totalPlayerScore: 0,
                        totalScoreRank: 0,
                        totalTimeCrowdControlDealt: 0,
                        totalUnitsHealed: 0,
                        tripleKills: 0,
                        trueDamageDealt: 0,
                        trueDamageDealtToChampions: 0,
                        trueDamageTaken: 0,
                        turretKills: 0,
                        unrealKills: 0,
                        visionScore: 0,
                        visionWardsBoughtInGame: 0,
                        wardsKilled: 0,
                        wardsPlaced: 0,
                    },
                },
                blue_team: {
                    win: false,
                    total_data: {
                        assists: 0,
                        damageDealtToObjectives: 0,
                        damageDealtToTurrets: 0,
                        damageSelfMitigated: 0,
                        deaths: 0,
                        goldEarned: 0,
                        goldSpent: 0,
                        inhibitorKills: 0,
                        kills: 0,
                        largestCriticalStrike: 0,
                        largetsKillingSpree: 0,
                        largestMultiKill: 0,
                        longestTimeSpentLiving: 0,
                        magicDamageDealt: 0,
                        magicDamageDealtToChampions: 0,
                        magicalDamageTaken: 0,
                        neutralMinionsKilled: 0,
                        neutralMinionsKilledEnemyJungle: 0,
                        neutralMinionsKilledTeamJungle: 0,
                        pentaKills: 0,
                        physicalDamageDealt: 0,
                        physicalDamageDealtToChampions: 0,
                        physicalDamageTaken: 0,
                        quadraKills: 0,
                        sightWardsBoughtInGame: 0,
                        timeCCingOthers: 0,
                        totalDamageDealt: 0,
                        totalDamageDealtToChampions: 0,
                        totalDamageTaken: 0,
                        totalHeal: 0,
                        totalMinionsKilled: 0,
                        totalPlayerScore: 0,
                        totalScoreRank: 0,
                        totalTimeCrowdControlDealt: 0,
                        totalUnitsHealed: 0,
                        tripleKills: 0,
                        trueDamageDealt: 0,
                        trueDamageDealtToChampions: 0,
                        trueDamageTaken: 0,
                        turretKills: 0,
                        unrealKills: 0,
                        visionScore: 0,
                        visionWardsBoughtInGame: 0,
                        wardsKilled: 0,
                        wardsPlaced: 0,
                    },
                },
                blue_team_participants : [],
                red_team_participants : [],

                total_data: {
                    assists: 0,
                    damageDealtToObjectives: 0,
                    damageDealtToTurrets: 0,
                    damageSelfMitigated: 0,
                    deaths: 0,
                    goldEarned: 0,
                    goldSpent: 0,
                    inhibitorKills: 0,
                    kills: 0,
                    largestCriticalStrike: 0,
                    largetsKillingSpree: 0,
                    largestMultiKill: 0,
                    longestTimeSpentLiving: 0,
                    magicDamageDealt: 0,
                    magicDamageDealtToChampions: 0,
                    magicalDamageTaken: 0,
                    neutralMinionsKilled: 0,
                    neutralMinionsKilledEnemyJungle: 0,
                    neutralMinionsKilledTeamJungle: 0,
                    pentaKills: 0,
                    physicalDamageDealt: 0,
                    physicalDamageDealtToChampions: 0,
                    physicalDamageTaken: 0,
                    quadraKills: 0,
                    sightWardsBoughtInGame: 0,
                    timeCCingOthers: 0,
                    totalDamageDealt: 0,
                    totalDamageDealtToChampions: 0,
                    totalDamageTaken: 0,
                    totalHeal: 0,
                    totalMinionsKilled: 0,
                    totalPlayerScore: 0,
                    totalScoreRank: 0,
                    totalTimeCrowdControlDealt: 0,
                    totalUnitsHealed: 0,
                    tripleKills: 0,
                    trueDamageDealt: 0,
                    trueDamageDealtToChampions: 0,
                    trueDamageTaken: 0,
                    turretKills: 0,
                    unrealKills: 0,
                    visionScore: 0,
                    visionWardsBoughtInGame: 0,
                    wardsKilled: 0,
                    wardsPlaced: 0,
                },
            }
        },
        computed : {
            date : function() {
                return moment.unix(this.match.gameCreation / 1000).format('LLL');
            },

            duration : function() {
                if (this.match != undefined && this.match.gameDuration != undefined) {
                    var seconds = parseInt(this.match.gameDuration) % 60;
                    var minutes = (parseInt(this.match.gameDuration) - seconds) / 60;

                    return minutes + 'm ' + seconds + 's';
                }
            },
        },
        methods : {
            resetData() {
                this.total_data = {
                    assists: 0,
                    damageDealtToObjectives: 0,
                    damageDealtToTurrets: 0,
                    damageSelfMitigated: 0,
                    deaths: 0,
                    goldEarned: 0,
                    goldSpent: 0,
                    inhibitorKills: 0,
                    kills: 0,
                    largestCriticalStrike: 0,
                    largestMultiKill: 0,
                    longestTimeSpentLiving: 0,
                    magicDamageDealt: 0,
                    magicDamageDealtToChampions: 0,
                    magicalDamageTaken: 0,
                    neutralMinionsKilled: 0,
                    neutralMinionsKilledEnemyJungle: 0,
                    neutralMinionsKilledTeamJungle: 0,
                    pentaKills: 0,
                    physicalDamageDealt: 0,
                    physicalDamageDealtToChampions: 0,
                    physicalDamageTaken: 0,
                    quadraKills: 0,
                    sightWardsBoughtInGame: 0,
                    timeCCingOthers: 0,
                    totalDamageDealt: 0,
                    totalDamageDealtToChampions: 0,
                    totalDamageTaken: 0,
                    totalHeal: 0,
                    totalMinionsKilled: 0,
                    totalPlayerScore: 0,
                    totalScoreRank: 0,
                    totalTimeCrowdControlDealt: 0,
                    totalUnitsHealed: 0,
                    tripleKills: 0,
                    trueDamageDealt: 0,
                    trueDamageDealtToChampions: 0,
                    trueDamageTaken: 0,
                    turretKills: 0,
                    unrealKills: 0,
                    visionScore: 0,
                    visionWardsBoughtInGame: 0,
                    wardsKilled: 0,
                    wardsPlaced: 0,
                }
                this.blue_team_participants = [];
                this.red_team_participants = [];
                this.red_team = {};
                this.blue_team = {};
            },

            assignData() {
                this.red_team = _.cloneDeep(this.match.matchTeams[1]);
                this.blue_team = _.cloneDeep(this.match.matchTeams[0]);
                this.red_team.total_data = _.cloneDeep(this.total_data)
                this.blue_team.total_data = _.cloneDeep(this.total_data)
            },

            calculateStats() {
                _.forEach(this.match.matchParticipants, (participant, participant_index) => {
                    if (participant.teamId == "100") {
                        this.blue_team_participants.push(participant);
                    } else {
                        this.red_team_participants.push(participant);
                    }
                });

                this.calculateTotals();
            },

            findParticipantKDA(participant) {
                var kills = participant.stats.kills;
                var deaths = participant.stats.deaths;
                var assists = participant.stats.assists;
                return kills + ' / ' + deaths + ' / ' + assists + "<div class='detail' style='margin-right: 5px;'>"
                    + ((kills + assists) / deaths).toFixed(2) + ":1</div>";
            },

            findParticipantCS(participant) {
                var cs = participant.stats.neutralMinionsKilled + participant.stats.totalMinionsKilled;
                var csPM = (cs / (parseInt(this.match.gameDuration) / 60)).toFixed(2);
                return "CS <div class='detail' style='margin-right: 5px;'>" + cs + "</div>" + csPM;
            },

            findParticipantGold(participant) {
                var gold = participant.stats.goldEarned;
                var goldPM = (gold / (parseInt(this.match.gameDuration) / 60)).toFixed(2);
                return "Gold <div class='detail' style='margin-right: 5px;'>" + gold + "</div>" + goldPM;
            },

            getChampionImageUrl(id) {
                var tempChamp =  this.staticChampion(id);
                if (tempChamp != undefined && tempChamp.id != undefined) {
                    var parsedChampName = tempChamp.id.split(' ').join('').split('\'').join('');
                    return 'http://ddragon.leagueoflegends.com/cdn/'+this.API_VERSION+'/img/champion/'+parsedChampName+'.png';
                }
            },

            getSpellImageUrl(id) {
                var tempSpell = this.staticSpell(id);
                if (tempSpell != undefined && tempSpell.id != undefined) {
                    return 'http://ddragon.leagueoflegends.com/cdn/'+this.API_VERSION+'/img/spell/'+tempSpell.id+'.png'
                }
            },

            getItemImageUrl(id) {
                var tempItem = this.staticItem(id);
                if (tempItem != undefined && tempItem.image != undefined) {
                    return 'http://ddragon.leagueoflegends.com/cdn/'+this.API_VERSION+'/img/item/'+tempItem.image.full
                }
            },

            getParticipantImageUrlFromIndex(participant, index) {
                return this.getItemImageUrl(participant.stats['item'+(index-1)]);
            },

            // go through every participant in blue_team_participants and red_team_participants
            //  and calculate an aggregate of every stat
            calculateTotals() {
                _.forEach(this.blue_team_participants, (participant, participant_index) => {
                    _.forEach(this.blue_team.total_data, (variable_name, variable_index) => {
                        this.blue_team.total_data[variable_index] += participant.stats[variable_index]
                    });
                });

                _.forEach(this.red_team_participants, (participant, participant_index) => {
                    _.forEach(this.red_team.total_data, (variable_name, variable_index) => {
                        this.red_team.total_data[variable_index] += participant.stats[variable_index]
                    });
                });

                _.forEach(this.total_data, (variable_name, variable_index) => {
                    this.total_data[variable_index] = this.red_team.total_data[variable_index] + this.blue_team.total_data[variable_index]
                });
            },

            // make a data object for the graph module
            create_chart_data_object(participant, index) {
                var tempChartData = {
                    'labels' : ['Damage Done'],
                    'datasets' : [
                        {
                            label: 'Damage Done',
                            backgroundColor: '#f87979',
                            data: [40]
                        },
                        {
                            label: 'Damage Done',
                            backgroundColor: '#f8db27',
                            data: [20]
                        },
                        {
                            label: 'Damage Done',
                            backgroundColor: '#4cf863',
                            data: [20]
                        },
                    ],
                }

                return tempChartData;

            }
        },
        watch : {
            match : function(val) {
                this.resetData();
                this.assignData();
                this.calculateStats();
            }
        }
    }
</script>

<style>

</style>

<style scoped>
    .header {
        text-align: center;
    }
    h4 {
        margin-top: 5px!important;
        margin-bottom: 5px!important;
    }

    .stats-container > .column {
        padding: 5px 10px!important;
        font-size: 10px;
    }
    .stats-container > * > span > i {
        display: inline;
    }

    .picture-column > .ui.grid > .column {
        padding: 9px!important;
    }

    .participant-summonerimage {
        width: 66px!important;
        margin: 0px!important;
    }

    .participant-underimage {
        width: 22px!important;
        margin: 0px!important;
        float: left;
    }
    .summoner-level-label > .label {
        position: absolute;
        z-index: 100;
        bottom: 24px;
        right: 21px;
    }
    .summoner-info {
        font-size: 10px!important;
        text-align: center;
        display: block!important;
        margin-bottom: 5px!important;
    }
    .summoner-info.summoner-name {
        font-size: 12px!important;
    }
    .summoner-data > .segment, #team-stats > .segment, .blue-team-header, .red-team-header {
        width: 50%!important;
    }
    .item-column {
        padding: 2px;
    }
    .item-column > .ui.grid > .column {
        padding: 1rem 0px 1rem 10px;
    }
    .participant-item-image {
        padding: 2px;
    }
</style>