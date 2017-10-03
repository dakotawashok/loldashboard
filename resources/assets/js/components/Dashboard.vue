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
                        <div class="ui raised segment" :class="{'loading': !summoner1Loaded && loading}">
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
                                    <div class="twelve wide column">
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
                    <div class="sixteen wide column" v-if="summoner1Loaded && !loading">
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
                        <div class="ui raised segment" :class="{'loading': !summoner2Loaded && loading}">
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
                    <div class="sixteen wide column" v-if="summoner2Loaded && !loading">
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
            getAllSummonerData : function(summonerNumber) {
                store.commit('assignLoading', true);
                if (summonerNumber == "1") {
                    this.$http.get('/summoner/' + this.summoner1Id + '/allData').then((resp) => {
                        resp = JSON.parse(resp.body);
                        // get the summoner information from the response
                        resp.summoner = this.parseSummonerDataFromResponse(resp.summoner);
                        resp.normalMatchList.matches = JSON.parse(resp.normalMatchList.matches);
                        resp.rankedMatchList.matches = JSON.parse(resp.rankedMatchList.matches);
                        this.parseMatchListDataFromResponse(resp.normalMatchList.matches, resp.normalDefinedMatchList);
                        this.parseMatchListDataFromResponse(resp.rankedMatchList.matches, resp.rankedDefinedMatchList);
                        store.commit('assignSummoner1Summoner', resp.summoner);
                        store.commit('assignSummoner1Loaded', true);
                        store.commit('assignSummoner1RankedMatchList', resp.rankedMatchList.matches);
                        store.commit('assignSummoner1DefinedRankedMatchList', resp.rankedDefinedMatchList);
                        store.commit('assignSummoner1NormalMatchList', resp.normalMatchList.matches);
                        store.commit('assignSummoner1DefinedNormalMatchList', resp.normalDefinedMatchList);

                        this.assignRankedData(summonerNumber);

                        store.commit('assignLoading', false);
                    });
                } else {
                    this.$http.get('/summoner/' + this.summoner2Id + '/allData').then((resp) => {
                        resp = JSON.parse(resp.body);
                        // get the summoner information from the response
                        resp.summoner = this.parseSummonerDataFromResponse(resp.summoner);
                        resp.normalMatchList.matches = JSON.parse(resp.normalMatchList.matches);
                        resp.rankedMatchList.matches = JSON.parse(resp.rankedMatchList.matches);
                        this.parseMatchListDataFromResponse(resp.normalMatchList.matches, resp.normalDefinedMatchList);
                        this.parseMatchListDataFromResponse(resp.rankedMatchList.matches, resp.rankedDefinedMatchList);
                        store.commit('assignSummoner2Summoner', resp.summoner);
                        store.commit('assignSummoner2Loaded', true);
                        store.commit('assignSummoner2RankedMatchList', resp.rankedMatchList.matches);
                        store.commit('assignSummoner2DefinedRankedMatchList', resp.rankedDefinedMatchList);
                        store.commit('assignSummoner2NormalMatchList', resp.normalMatchList.matches);
                        store.commit('assignSummoner2DefinedNormalMatchList', resp.normalDefinedMatchList);

                        this.assignRankedData(summonerNumber);

                        store.commit('assignLoading', false);
                    });
                }
            },

            /*
                refreshSummonerRankedData()
                    sends a request to the server to get new ranked data for the summoner
            */
            refreshSummonerRankedData(summonerNumber, accountId) {
                if (summonerNumber == '1') { store.commit('assignSummoner1Loaded', false);}
                if (summonerNumber == '2') { store.commit('assignSummoner2Loaded', false);}
                this.$http.get('/summoner/'+accountId+'/refreshRankedStats').then((resp) => {
                    if (summonerNumber == '1') {
                        // get the summoner, put the new ranked data in that summoner, then reassign the summoner
                        // then we have to recall the assignRankedData to parse it all back out baby
                        var tempSummoner = _.cloneDeep(this.summoner1.summoner);
                        tempSummoner.league = JSON.parse(resp.body);
                        store.commit('assignSummoner1Summoner', tempSummoner);
                    } else if (summonerNumber == '2') {
                        // get the summoner, put the new ranked data in that summoner, then reassign the summoner
                        // then we have to recall the assignRankedData to parse it all back out baby
                        var tempSummoner = _.cloneDeep(this.summoner2.summoner);
                        tempSummoner.league = JSON.parse(resp.body);
                        store.commit('assignSummoner2Summoner', tempSummoner);
                    }
                    this.assignRankedData(summonerNumber);
                    if (summonerNumber == '1') { store.commit('assignSummoner1Loaded', true);}
                    if (summonerNumber == '2') { store.commit('assignSummoner2Loaded', true);}
                })
            },

            findMatchList : function(identifier, matchlistType, parameters) {
                var body = {'matchlistType': matchlistType, 'params' : parameters};
                var headers = {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')};
                return this.$http.post('/summoner/' + identifier + '/matchlist', body, {headers: headers});
            },

            // Go through all the summoner league ranked data and find the specific data that matches with this summoner
            assignRankedData : function(summonerNumber) {
                if (summonerNumber == '1') {
                    // make a temp object that we will use to replace the summoner
                    var tempSummoner = _.cloneDeep(this.summoner1.summoner);
                    tempSummoner.rankedData = {};
                    if (tempSummoner != undefined && tempSummoner.league != undefined) {
                        _.forEach(tempSummoner.league, (league) => {
                            if (league.queue == 'RANKED_SOLO_5x5') {
                                tempSummoner.rankedData.tier = league.tier;
                                tempSummoner.rankedData.name = league.name;
                                tempSummoner.rankedData.queue = league.queue;
                                // now we have to go into every goddamn summoner in this league and find the one that
                                // matches the current summoner so we can find it's tier_league
                                var found = false;
                                _.forEach(league.entries, (league_summoner) => {
                                    if (league_summoner.playerOrTeamName === tempSummoner.name) {
                                        found = true;
                                        tempSummoner.rankedData.freshBlood = league_summoner.freshBlood;
                                        tempSummoner.rankedData.hotStreak = league_summoner.hotStreak;
                                        tempSummoner.rankedData.losses = league_summoner.losses;
                                        tempSummoner.rankedData.playerOrTeamId = league_summoner.playerOrTeamId;
                                        tempSummoner.rankedData.playerOrTeamName = league_summoner.playerOrTeamName;
                                        tempSummoner.rankedData.rank = league_summoner.rank;
                                        tempSummoner.rankedData.veteran = league_summoner.veteran;
                                        tempSummoner.rankedData.wins = league_summoner.wins;
                                        tempSummoner.rankedData.leaguePoints = league_summoner.leaguePoints;
                                    }
                                });
                                // if we found the summoner in all that data, lets get rid of all that data since we
                                // don't need it anymore
                                if (found) {
                                    delete tempSummoner.league;
                                    store.commit('assignSummoner1Summoner', tempSummoner);
                                }
                            }
                        })
                    } else {
                        return '';
                    }
                } else if (summonerNumber == '2') {
                    // make a temp object that we will use to replace the summoner
                    var tempSummoner = _.cloneDeep(this.summoner2.summoner);
                    tempSummoner.rankedData = {};
                    if (tempSummoner != undefined && tempSummoner.league != undefined) {
                        _.forEach(tempSummoner.league, (league) => {
                            if (league.queue == 'RANKED_SOLO_5x5') {
                                console.log(league);
                                tempSummoner.rankedData.tier = league.tier;
                                tempSummoner.rankedData.name = league.name;
                                tempSummoner.rankedData.queue = league.queue;
                                // now we have to go into every goddamn summoner in this league and find the one that
                                // matches the current summoner so we can find it's tier_league
                                var found = false;
                                _.forEach(league.entries, (league_summoner) => {
                                    if (league_summoner.playerOrTeamName === tempSummoner.name) {
                                        console.log(league_summoner);
                                        found = true;
                                        tempSummoner.rankedData.freshBlood = league_summoner.freshBlood;
                                        tempSummoner.rankedData.hotStreak = league_summoner.hotStreak;
                                        tempSummoner.rankedData.losses = league_summoner.losses;
                                        tempSummoner.rankedData.playerOrTeamId = league_summoner.playerOrTeamId;
                                        tempSummoner.rankedData.playerOrTeamName = league_summoner.playerOrTeamName;
                                        tempSummoner.rankedData.rank = league_summoner.rank;
                                        tempSummoner.rankedData.veteran = league_summoner.veteran;
                                        tempSummoner.rankedData.wins = league_summoner.wins;
                                        tempSummoner.rankedData.leaguePoints = league_summoner.leaguePoints;
                                    }
                                });
                                // if we found the summoner in all that data, lets get rid of all that data since we
                                // don't need it anymore
                                if (found) {
                                    delete tempSummoner.league;
                                    store.commit('assignSummoner2Summoner', tempSummoner);
                                } else {
                                }
                            }
                        })
                    } else {
                        console.log('super wtf');
                        return '';
                    }
                } else {
                    console.log('wtf');
                }
            },


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


            /*
                parseSummonerDataFromResponse()
                    turns the json string variables in the object into an actual object and then returns thew new summoner
             */
            parseSummonerDataFromResponse(summoner) {
                var parsedSummoner = summoner;
                parsedSummoner.championMastery = (typeof summoner.championMastery == 'string' ? JSON.parse(summoner.championMastery) : summoner.championMastery);
                parsedSummoner.league = (typeof summoner.league == 'string' ? JSON.parse(summoner.league) : summoner.league);
                parsedSummoner.masteries = (typeof summoner.masteries == 'string' ? JSON.parse(summoner.masteries) : summoner.masteries);
                parsedSummoner.runes = (typeof summoner.runes == 'string' ? JSON.parse(summoner.runes) : summoner.runes);

                return parsedSummoner;
            },

            /*
                Now we have to be able to turn each of the responses data into usable properties on the front end.
                Let's go through each of the matches, and attach their corresponding defined match to it
             */
            parseMatchListDataFromResponse(tempMatchList, tempMatchListDefined) {
                // Now we're going to add the defined match to each of the regular matches
                _.forEach(tempMatchList, (match) => {
                    _.forEach(tempMatchListDefined, (defined_match) => {
                        if (match.gameId == defined_match.gameId) {
                            match.defined_match = defined_match;
                        }
                    })
                })
            }

        },
        watch : {
            currentYear : function(newYear) {
                store.commit('assignCurrentYear', newYear);
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