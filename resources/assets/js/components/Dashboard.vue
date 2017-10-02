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
                <div class="right-floated-icon">
                    <img v-if="summoner1Loaded" :src="summoner1ProfileIconUrl" />
                </div>
                <div class="summoner-form" style="float: right; text-align: right;">
                    <h2>SUMMONER NAME1: </h2>
                    <input v-model="summoner1Id" placeholder="Summoner Name" v-on:keyup.enter="getInfo(1)"/>
                </div>
            </div>
            <div class="right floated column summoner-column">
                <div class="ui one column grid">
                    <div class="sixteen wide column">
                        <div class="ui raised segment" :class="{'loading': loading}">
                            <h2>SUMMONER NAME2: </h2>
                            <input v-model="summoner2Id" placeholder="Summoner Name" v-on:keyup.enter="getAllSummonerData('2')"/>
                            <div v-if="summoner2Loaded" class="season-container">
                                <span>Season 6: </span>
                                <span>Season 5:  </span>
                                <span>Season 4: </span>
                            </div>
                            <div v-if="summoner2Loaded" class="ui grid ranked-info-container">
                                <div class="two column row">
                                    <div class="four wide column">
                                        <img class="ui centered small image" v-if="summoner2Loaded" :src="summoner2ProfileIconUrl" />
                                    </div>
                                    <div class="twelve wide column">
                                        <p>Current Rank</p>
                                        <p>LP / W / L</p>
                                        <p>Win Ratio</p>
                                        <p>League Name</p>
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

                    });
                } else {
                    this.$http.get('/summoner/' + this.summoner2Id + '/allData').then((resp) => {
                        resp = JSON.parse(resp.body);
                        console.log(_.cloneDeep(resp));
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

                        store.commit('assignLoading', false);
                    });
                }
            },

            findSummoner : function(summonerNumber) {
                if (summonerNumber == "1") {
                    store.commit('assignSummoner1Loaded', true);
                    return this.$http.get('/summoner/' + this.summoner1Id);
                } else {
                    store.commit('assignSummoner2Loaded', true);
                    return this.$http.get('/summoner/' + this.summoner2Id);
                }
            },
            findMatchList : function(identifier, matchlistType, parameters) {
                var body = {'matchlistType': matchlistType, 'params' : parameters};
                var headers = {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')};
                return this.$http.post('/summoner/' + identifier + '/matchlist', body, {headers: headers});
            },

            // assignRankedMatchList()
            //      gets the ranked matchlist, parses it, then puts it into the stored object
            assignRankedMatchList : function() {
                // created the ranked parameters needed by the back-end
                var tempParams = {'queue' : [410, 420, 440, 6, 41, 42], 'season' : [9], 'endIndex' : 20};
                var params = [];
                for (var key in tempParams) {params.push(key+'='+encodeURIComponent(tempParams[key]));}
                params = params.join('&');

                if (store.state.summoner1.loaded && !store.state.summoner2.loaded) {
                    this.findMatchList(store.state.summoner1.summoner.accountId, 'ranked', params).then(
                        response => {
                            var tempRankedMatchList = JSON.parse(response.body);
                            tempRankedMatchList.matches = JSON.parse(tempRankedMatchList.matches);
                            store.commit('assignSummoner1RankedMatchList', tempRankedMatchList);
                        }
                    );
                } else if (!store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    this.findMatchList(store.state.summoner2.summoner.accountId, 'ranked', params).then(
                        response => {
                            var tempMatchList = JSON.parse(response.body);
                            var tempMatchListDefined = tempMatchList.matches_defined;
                            tempMatchList = JSON.parse(tempMatchList.matches.matches);
                            // Now we're going to add the defined match to each of the regular matches
                            _.forEach(tempMatchList, (match) => {
                                _.forEach(tempMatchListDefined, (defined_match) => {
                                    if (match.gameId == defined_match.gameId) {
                                        match.defined_match = defined_match;
                                    }
                                })
                            })

                            store.commit('assignSummoner2RankedMatchList', tempMatchList);
                        }
                    );
                } else if (store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    // I make temporary variables like this so that I can commit both of them at the same time which fixes
                    // the problem of the menu being loaded once the first dataset is loaded, but then reloading once the second is done
                    var tempRankedMatchList1 = {};
                    var tempRankedMatchList2 = {};
                    this.findMatchList(this.summoner1.summoner.accountId, 'ranked', params).then(response => {
                        var tempRankedMatchList = JSON.parse(response.body);
                        tempRankedMatchList.matches = JSON.parse(tempRankedMatchList.matches);
                        tempRankedMatchList1 = tempRankedMatchList;
                        return this.findMatchList(this.summoner2.summoner.accountId, 'ranked', params)
                    }).then(resp => {
                        var tempRankedMatchList = JSON.parse(response.body);
                        tempRankedMatchList.matches = JSON.parse(tempRankedMatchList.matches);
                        tempRankedMatchList2 = tempRankedMatchList;

                        store.commit('assignSummoner1RankedMatchList', tempRankedMatchList1);
                        store.commit('assignSummoner2RankedMatchList', tempRankedMatchList2);
                    });
                }
            },

            // assignNormalMatchList()
            //      gets the normal match list, parses it, then puts it into the stored object
            assignNormalMatchList : function() {
                // created the ranked parameters needed by the back-end
                var tempParams = {'queue' : [2, 8, 14, 400, 430, 460], 'season' : [9], 'endIndex' : 20};
                var params = [];
                for (var key in tempParams) {params.push(key+'='+encodeURIComponent(tempParams[key]));}
                params = params.join('&');

                if (store.state.summoner1.loaded && !store.state.summoner2.loaded) {
                    this.findMatchList(store.state.summoner1.summoner.accountId, 'normal', params).then(
                        response => {
                            var tempMatchList = JSON.parse(response.body);
                            tempMatchList.matches = JSON.parse(tempMatchList.matches.matches);
                            store.commit('assignSummoner1NormalMatchList', tempMatchList);
                        }
                    );
                } else if (!store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    this.findMatchList(store.state.summoner2.summoner.accountId, 'normal', params).then(
                        response => {
                            var tempMatchList = JSON.parse(response.body);
                            var tempMatchListDefined = tempMatchList.matches_defined;
                            tempMatchList = JSON.parse(tempMatchList.matches.matches);
                            // Now we're going to add the defined match to each of the regular matches
                            _.forEach(tempMatchList, (match) => {
                                _.forEach(tempMatchListDefined, (defined_match) => {
                                    if (match.gameId == defined_match.gameId) {
                                        match.defined_match = defined_match;
                                    }
                                })
                            })

                            store.commit('assignSummoner2NormalMatchList', tempMatchList);
                        }
                    );
                } else if (store.state.summoner1.loaded && store.state.summoner2.loaded) {
                    // I make temporary variables like this so that I can commit both of them at the same time which fixes
                    // the problem of the menu being loaded once the first dataset is loaded, but then reloading once the second is done
                    var tempMatchList1 = {};
                    var tempMatchList2 = {};
                    this.findMatchList(this.summoner1.summoner.accountId, 'normal', params).then(response => {
                        var tempMatchList = JSON.parse(response.body);
                        tempMatchList.matches = JSON.parse(tempMatchList.matches);
                        tempMatchList1 = tempMatchList;
                        return this.findMatchList(this.summoner2.summoner.accountId, 'normal', params)
                    }).then(resp => {
                        var tempMatchList = JSON.parse(response.body);
                        tempMatchList.matches = JSON.parse(tempMatchList.matches);
                        tempMatchList2 = tempMatchList;

                        store.commit('assignSummoner1NormalMatchList', tempMatchList1);
                        store.commit('assignSummoner2NormalMatchList', tempMatchList2);
                    });
                }
            },

            // This getInfo method is called when the user loads up a summoner.
            /*
             It:
                Finds the summoner data from the database/api including mastery, masteries, leagues, and runes
                Finds the ranked matchlist for specified user from the database/api
                Finds the normal matchlist for specified user from the database/api
             */
            getInfo : function(summonerNumber) {
                store.commit('assignLoading', true);
                store.commit('assignSummoner1Loaded', false);
                store.commit('assignSummoner2Loaded', false);
                this.clearData(summonerNumber);
                this.findSummoner(summonerNumber).then(response => {
                    if (summonerNumber == "1") {
                        store.commit('assignSummoner1Summoner', JSON.parse(response.body));
                        store.commit('assignSummoner1Loaded', true);
                    } else {
                        store.commit('assignSummoner2Summoner', JSON.parse(response.body));
                        store.commit('assignSummoner2Loaded', true);
                    }
                    return this.assignRankedMatchList();
                }).then(response => {
                    return this.assignNormalMatchList();
                }).then(response => {
                    store.commit('assignLoading', false);
                }).catch(
                    response => {
                        console.log(response)
                    }
                );
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
                    }
                );
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
</style>