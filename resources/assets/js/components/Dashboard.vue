<template>
    <div class="ui grid">
        <div class="ui modal">
            <i class="close icon"></i>
            <div class="header">
                Modal Title
            </div>
            <div class="image content">
                <div class="image">
                    An image can appear on left or an icon
                </div>
                <div class="description">
                    A description can appear on the right
                </div>
            </div>
            <div class="actions">
                <div class="ui button">Cancel</div>
                <div class="ui button">OK</div>
            </div>
        </div>
        <div class="two column row" id="main-grid-container">
            <div class="left floated column summoner-column">
                <div class="ui one column grid">
                    <div class="sixteen wide column">
                        <div class="ui raised segment" :class="{'loading': summoner1Loading}">
                            <h2>SUMMONER NAME1: </h2>
                            <input v-model="summoner1Id" placeholder="Summoner Name" v-on:keyup.enter="getAllSummonerData('1')"/>
                            <!--<div v-if="summoner1Loaded" class="season-container">-->
                                <!--<span>Season 6: </span>-->
                                <!--<span>Season 5:  </span>-->
                                <!--<span>Season 4: </span>-->
                            <!--</div>-->
                            <div v-if="summoner1Loaded" class="ui grid ranked-info-container">
                                <div class="two column row">
                                    <div class="four wide column">
                                        <img class="ui centered small image" v-if="summoner1Loaded" :src="summoner1ProfileIconUrl" />
                                    </div>
                                    <div class="twelve wide column ranked-stats-container">
                                        <p>Current Rank: {{summoner1CurrentRank}}
                                            <i class="refresh icon" @click="refreshSummonerRankedData('1', summoner1.summoner.accountId)"></i></p>
                                        <p>{{summoner1Ratio}}</p>
                                        <p>Win Ratio: {{summoner1RatioPercent}}</p>
                                        <p>League Name: {{summoner1RankName}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sixteen wide column" v-if="summoner1Loaded && !summoner1Loading">
                        <div class="ui two item top attached menu">
                            <a class="item"
                               :class="{'active' : (currentlyViewedMatchList=='ranked')}"
                               @click="changeView('ranked')">Ranked Games</a>
                            <a class="item"
                               :class="{'active' : (currentlyViewedMatchList=='normal')}"
                               @click="changeView('normal')">Normal Games</a>
                        </div>
                        <template v-if="currentlyViewedMatchList=='ranked'">
                            <matchcard v-for="(match, key, index) in summoner1RankedMatchList" :summoner_number="'1'" :match_type="'ranked'" :match="match"></matchcard>
                        </template>
                        <template v-if="currentlyViewedMatchList=='normal'">
                            <matchcard v-for="(match, key, index) in summoner1NormalMatchList" :summoner_number="'1'" :match_type="'normal'" :match="match"></matchcard>
                        </template>
                    </div>
                </div>
            </div>
            <div class="right floated column summoner-column">
                <div class="ui one column grid">
                    <div class="sixteen wide column">
                        <div class="ui raised segment" :class="{'loading': summoner2Loading}">
                            <h2>SUMMONER NAME2: </h2>
                            <input v-model="summoner2Id" placeholder="Summoner Name" v-on:keyup.enter="getAllSummonerData('2')"/>
                            <!--<div v-if="summoner2Loaded" class="season-container">-->
                                <!--<span>Season 6: </span>-->
                                <!--<span>Season 5:  </span>-->
                                <!--<span>Season 4: </span>-->
                            <!--</div>-->
                            <div v-if="summoner2Loaded" class="ui grid ranked-info-container">
                                <div class="two column row">
                                    <div class="four wide column">
                                        <img class="ui centered small image" v-if="summoner2Loaded" :src="summoner2ProfileIconUrl" />
                                    </div>
                                    <div class="twelve wide column ranked-stats-container">
                                        <p>Current Rank: {{summoner2CurrentRank}}
                                            <i class="refresh icon" @click="refreshSummonerRankedData('2', summoner2.summoner.accountId)"></i></p>
                                        <p>{{summoner2Ratio}}</p>
                                        <p>Win Ratio: {{summoner2RatioPercent}}</p>
                                        <p>League Name: {{summoner2RankName}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sixteen wide column" v-if="summoner2Loaded && !summoner2Loading">
                        <div class="ui two item top attached menu">
                            <a class="item"
                               :class="{'active' : (currentlyViewedMatchList=='ranked')}"
                               @click="changeView('ranked')">Ranked Games</a>
                            <a class="item"
                               :class="{'active' : (currentlyViewedMatchList=='normal')}"
                               @click="changeView('normal')">Normal Games</a>
                        </div>
                        <template v-if="currentlyViewedMatchList=='ranked'">
                            <matchcard v-for="(match, key, index) in summoner2RankedMatchList" :summoner_number="'2'" :match_type="'ranked'" :match="match"></matchcard>
                        </template>
                        <template v-if="currentlyViewedMatchList=='normal'">
                            <matchcard v-for="(match, key, index) in summoner2NormalMatchList" :summoner_number="'2'" :match_type="'normal'" :match="match"></matchcard>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import 'semantic.min.css'
    import 'semantic'
    import store from '../store.js';
    import mixin from '../mixin.js';

    import MatchCard from '../components/MatchCard.vue';

    export default {
        mixins: [
            mixin
        ],
        components: [
            MatchCard,
        ],
        mounted() {
            this.setStaticData();

            $('.ui.modal').modal({
                closable  : true,
                detachable: true,
                onApprove: () => {
                    console.log('closed');
                }
            })


        },
        data : function() {
            return {
                summoner1Id : "",
                summoner2Id : "",

                currentYear : 9,

                currentlyViewedMatchList : 'ranked'
            }
        },
        computed : {

        },
        methods : {
            clearData : function(summonerNumber) {
                if (summonerNumber == '1') {
                    store.commit('assignSummoner1Summoner', {});
                    store.commit('assignSummoner1NormalMatchList', {});
                    store.commit('assignSummoner1RankedMatchList', {});
                    store.commit('assignSummoner1DefinedRankedMatchList', {});
                    store.commit('assignSummoner1DefinedNormalMatchList', {});
                } else if (summonerNumber == '2'){
                    store.commit('assignSummoner2Summoner', {});
                    store.commit('assignSummoner2NormalMatchList', {});
                    store.commit('assignSummoner2RankedMatchList', {});
                    store.commit('assignSummoner2DefinedRankedMatchList', {});
                    store.commit('assignSummoner2DefinedNormalMatchList', {});

                } else {
                    console.log('errorrrrr');
                }
            },

            setStaticData : function() {
                this.$http.get('/jsonfiles/champion.json').then(
                    response => {
                        var champions = response.body.data;

                        var tempChampionsList = [];
                        for (var champion in champions) {
                            tempChampionsList.push(champions[champion]);
                        };

                        tempChampionsList.sort(function(championA, championB) {
                            if (parseInt(championA.key) < parseInt(championB.key)) {
                                return -1;
                            } else {
                                return 1;
                            }
                        });
                        store.commit('assignChampions', tempChampionsList);
                        return this.$http.get('/jsonfiles/summonerspells.json');
                    }
                ).then((resp) => {
                    var spells = resp.body.data;

                    var tempSpellList = [];
                    for (var spell in spells) {
                        tempSpellList.push(spells[spell]);
                    };

                    tempSpellList.sort(function(spellA, spellB) {
                        if (parseInt(spellA.key) < parseInt(spellB.key)) {
                            return -1;
                        } else {
                            return 1;
                        }
                    });
                    store.commit('assignSpells', tempSpellList);
                });
            },

            changeView : function(view) {
                this.currentlyViewedMatchList = view;
            },
        },
        watch : {
            summoner1Id : function(newName) {
                store.commit('assignSummonerName', {summonerNumber: 1, summonerName: newName});
            },

            summoner2Id : function(newName) {
                store.commit('assignSummonerName', {summonerNumber: 2, summonerName: newName});
            },


        }
    }
</script>

<style scoped>
    #main-grid-container > *:first-child {
        border-right-color: #555557;
        border-right-style: solid;
        border-right-width: 2px;
    }

    .summoner-header {
        margin: 15px!important;
    }

    .summoner-column {
        padding: 30px!important;
    }

    input {
        margin-bottom: 12px;
    }

    .ranked-stats-container > p {
        line-height: 10px;
    }
</style>