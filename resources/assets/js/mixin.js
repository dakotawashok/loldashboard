import store from '../js/store.js';

export default {
    created: function() {

    },

    data () {
        return {
            API_VERSION: '7.19.1'
        }
    },

    methods: {
        staticChampion : function(id) {
            for (var champion in store.state.staticInfo.champions) {
                if (store.state.staticInfo.champions[champion].key == id) {
                    return store.state.staticInfo.champions[champion];
                }
            }
        },

        staticSpell : function(id) {
            for (var spell in store.state.staticInfo.spells) {
                if (store.state.staticInfo.spells[spell].key == id) {
                    return store.state.staticInfo.spells[spell];
                }
            }
        },

        getAllSummonerData : function(summonerNumber, useAccountId = false) {
            if (summonerNumber == "1") {
                store.commit('assignSummonerLoading', {'summonerNumber' : 1, 'loading' : true});
                this.$http.get('/summoner/' + (useAccountId ?  this.summoner1.accountId : this.summoner1.summonerName) + '/allData').then((resp) => {
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

                    store.commit('assignSummonerLoading', {'summonerNumber' : 1, 'loading' : false});
                    $('#summoner1-input').text(this.summoner1.summoner.name);
                    console.log($('#summoner1-input').text());
                });
            } else {
                store.commit('assignSummonerLoading', {'summonerNumber' : 2, 'loading' : true});
                this.$http.get('/summoner/' + (useAccountId ?  this.summoner2.accountId : this.summoner2.summonerName) + '/allData').then((resp) => {
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

                    store.commit('assignSummonerLoading', {'summonerNumber' : 2, 'loading' : false});
                    console.log(this.summoner2.summoner.name);
                    $('#summoner2-input').text(this.summoner2.summoner.name);
                    console.log($('#summoner2-input').text());
                })
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

    computed: {
        summoner1ProfileIconUrl : function() { return "http://ddragon.leagueoflegends.com/cdn/"+this.API_VERSION+"/img/profileicon/" + this.summoner1.summoner.profileIconId + ".png"; },
        summoner2ProfileIconUrl : function() { return "http://ddragon.leagueoflegends.com/cdn/"+this.API_VERSION+"/img/profileicon/" + this.summoner2.summoner.profileIconId + ".png"; },

        staticInfo : function() { return store.state.staticInfo; },
        staticChampions : function() { return store.state.staticInfo.champions; },
        staticSpells : function() { return store.state.staticInfo.spells; },

        summoner1 : function() { return store.state.summoner1; },
        summoner2 : function() { return store.state.summoner2; },

        summoner1Loaded : function() { return store.state.summoner1.loaded; },
        summoner2Loaded : function() { return store.state.summoner2.loaded; },

        summoner1Loading : function() { return store.state.summoner1.loading; },
        summoner2Loading : function() { return store.state.summoner2.loading; },

        summoner1RankedMatchList : function() {
            return store.state.summoner1.rankedMatchList;
        },
        summoner1NormalMatchList : function() {
            return store.state.summoner1.normalMatchList;
        },

        summoner2RankedMatchList : function() {
            return store.state.summoner2.rankedMatchList;
        },
        summoner2NormalMatchList : function() {
            return store.state.summoner2.normalMatchList;
        },

        loading : function() { return store.state.loading; },



        summoner1CurrentRank : function() {
            return this.summoner1.summoner.rankedData.tier + ' ' + this.summoner1.summoner.rankedData.rank;
        },
        summoner1RankName : function() {
            return this.summoner1.summoner.rankedData.name;
        },
        summoner1Ratio : function() {
            return this.summoner1.summoner.rankedData.leaguePoints + ' LP / ' +
                this.summoner1.summoner.rankedData.wins + ' wins / ' +
                this.summoner1.summoner.rankedData.losses + ' losses';
        },
        summoner1RatioPercent : function() {
            var wins = parseInt(this.summoner1.summoner.rankedData.wins);
            var total = parseInt(this.summoner1.summoner.rankedData.wins) + parseInt(this.summoner1.summoner.rankedData.losses);
            return ((wins / total) * 100).toFixed(2) + '%';
        },
        summoner2CurrentRank : function() {
            return this.summoner2.summoner.rankedData.tier + ' ' + this.summoner2.summoner.rankedData.rank;
        },
        summoner2RankName : function() {
            return this.summoner2.summoner.rankedData.name;
        },
        summoner2Ratio : function() {
            return this.summoner2.summoner.rankedData.leaguePoints + ' LP / ' +
                this.summoner2.summoner.rankedData.wins + ' wins / ' +
                this.summoner2.summoner.rankedData.losses + ' losses';
        },
        summoner2RatioPercent : function() {
            var wins = parseInt(this.summoner2.summoner.rankedData.wins);
            var total = parseInt(this.summoner2.summoner.rankedData.wins) + parseInt(this.summoner2.summoner.rankedData.losses);
            return ((wins / total) * 100).toFixed(2) + '%';
        },
    }
}