import store from '../js/store.js';

export default {
    created: function() {

    },

    data () {
        return {
            API_VERSION: '8.2.1'
        }
    },

    methods: {
        staticItem : function(id) {
            for (var item in store.state.staticInfo.items.data) {
                if (item == id) {
                    return store.state.staticInfo.items.data[item];
                }
            }
        },

        staticChampion : function(id) {
            for (var champion in store.state.staticInfo.champions) {
                if (parseInt(store.state.staticInfo.champions[champion].key) == id) {
                    return store.state.staticInfo.champions[champion];
                }
            }
        },

        staticSpell : function(id) {
            for (var spell in store.state.staticInfo.spells) {
                if (parseInt(store.state.staticInfo.spells[spell].key) == id) {
                    return store.state.staticInfo.spells[spell];
                }
            }
        },

        staticSeason : function(id) {
            var return_season = {};
            _.forEach(store.state.staticInfo.seasons, (season, season_index) => {
                if (season.id == id) {
                    return_season = season;
                }
            });
            return return_season;
        },

        staticMatchmakingQueue : function(id) {
            var return_queue = {};
            _.forEach(store.state.staticInfo.matchmaking_queues, (queue, queue_index) => {
                if (queue.id == id) {
                    return_queue = queue;
                }
            });
            return return_queue;
        },

        staticMapName : function(id) {
            _.forEach(store.state.staticInfo.map_names, (map_name, map_name_index) => {
                if (map_name.id == id) {
                    return map_name;
                }
            });
        },

        getAllSummonerData : function(summonerNumber, useAccountId = false, currentlyViewedMatchList) {
            console.log('getting all summoner data');
            if (summonerNumber == "1") {
                var tempSummoner = this.$summoner_service.make_new_summoner();
                store.commit('assignSummonerLoading', {'summonerNumber' : 1, 'loading' : true});
                this.$summoner_service.load_new_summoner(tempSummoner, (useAccountId ? this.summoner1.accountId : this.summoner1.summonerName), !useAccountId, currentlyViewedMatchList).then((resp) => {
                    store.commit('assignSummoner', {'summonerNumber' : summonerNumber, 'summoner' : resp });
                    store.commit('assignSummonerLoading', {'summonerNumber' : 1, 'loading' : false});
                    store.commit('assignSummoner1Loaded', true);
                }).catch((resp) => {
                    this.$notify.error('Summoner not found');
                    console.log(resp);
                    store.commit('assignSummonerLoading', {'summonerNumber' : 1, 'loading' : false});
                    store.commit('assignSummoner1Loaded', true);
                });
            } else {
                var tempSummoner = this.$summoner_service.make_new_summoner();
                store.commit('assignSummonerLoading', {'summonerNumber' : 2, 'loading' : true});
                this.$summoner_service.load_new_summoner(tempSummoner, (useAccountId ? this.summoner2.accountId : this.summoner2.summonerName), !useAccountId, currentlyViewedMatchList).then((resp) => {
                    store.commit('assignSummoner', {'summonerNumber' : summonerNumber, 'summoner' : resp });
                    store.commit('assignSummonerLoading', {'summonerNumber' : 2, 'loading' : false});
                    store.commit('assignSummoner2Loaded', true);
                }).catch((resp) => {
                    this.$notify.error('Summoner not found');
                    console.log(resp);
                    store.commit('assignSummonerLoading', {'summonerNumber' : 2, 'loading' : false});
                    store.commit('assignSummoner2Loaded', true);
                });
            }
        },

        /*
         refreshSummonerRankedData()
         sends a request to the server to get new ranked data for the summoner
         */
        refreshSummonerRankedData(summonerNumber, accountId) {
            store.commit('assignSummonerLoading', {'summonerNumber' : summonerNumber, 'loading' : true});
            this.$http.get('/summoner/'+accountId+'/refreshRankedStats').then((resp) => {
                if (summonerNumber == 1) {
                    // get the summoner, put the new ranked data in that summoner, then reassign the summoner
                    // then we have to recall the assignRankedData to parse it all back out baby
                    var tempSummoner = _.cloneDeep(this.summoner1.summoner);
                    tempSummoner.league = JSON.parse(resp.body);
                    store.commit('assignSummoner1Summoner', tempSummoner);
                } else if (summonerNumber == 2) {
                    // get the summoner, put the new ranked data in that summoner, then reassign the summoner
                    // then we have to recall the assignRankedData to parse it all back out baby
                    var tempSummoner = _.cloneDeep(this.summoner2.summoner);
                    tempSummoner.league = JSON.parse(resp.body);
                    store.commit('assignSummoner2Summoner', tempSummoner);
                }
                this.assignRankedData(summonerNumber);
                store.commit('assignSummonerLoading', {'summonerNumber' : summonerNumber, 'loading' : false});
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
                        if (league.queueType == 'RANKED_SOLO_5x5') {
                            tempSummoner.rankedData.freshBlood = league.freshBlood;
                            tempSummoner.rankedData.hotStreak = league.hotStreak;
                            tempSummoner.rankedData.inactive = league.inactive;
                            tempSummoner.rankedData.leagueId = league.leagueId;
                            tempSummoner.rankedData.losses = league.losses;
                            tempSummoner.rankedData.playerOrTeamId = league.playerOrTeamId;
                            tempSummoner.rankedData.playerOrTeamName = league.playerOrTeamName;
                            tempSummoner.rankedData.queueType = league.queueType;
                            tempSummoner.rankedData.rank = league.rank;
                            tempSummoner.rankedData.tier = league.tier;
                            tempSummoner.rankedData.veteran = league.veteran;
                            tempSummoner.rankedData.wins = league.wins;
                            tempSummoner.rankedData.leaguePoints = league.leaguePoints;
                            tempSummoner.rankedData.leagueName = league.leagueName;
                            store.commit('assignSummoner1Summoner', tempSummoner);
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
                        if (league.queueType == 'RANKED_SOLO_5x5') {
                            tempSummoner.rankedData.freshBlood = league.freshBlood;
                            tempSummoner.rankedData.hotStreak = league.hotStreak;
                            tempSummoner.rankedData.inactive = league.inactive;
                            tempSummoner.rankedData.leagueId = league.leagueId;
                            tempSummoner.rankedData.losses = league.losses;
                            tempSummoner.rankedData.playerOrTeamId = league.playerOrTeamId;
                            tempSummoner.rankedData.playerOrTeamName = league.playerOrTeamName;
                            tempSummoner.rankedData.queueType = league.queueType;
                            tempSummoner.rankedData.rank = league.rank;
                            tempSummoner.rankedData.tier = league.tier;
                            tempSummoner.rankedData.veteran = league.veteran;
                            tempSummoner.rankedData.wins = league.wins;
                            tempSummoner.rankedData.leaguePoints = league.leaguePoints;
                            tempSummoner.rankedData.leagueName = league.leagueName;
                            store.commit('assignSummoner2Summoner', tempSummoner);
                        }
                    })
                }
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
        },

        openMatchModal(matchId) {
            store.commit('assignMatchModalLoading', true);
            $('#match-modal').modal('show')

            this.$http.get('/match/' + matchId).then((resp) => {
                resp = JSON.parse(resp.body);

                var parsedMatch = this.parseMatchDataFromResponse(resp);

                store.commit('assignModalMatch', parsedMatch);
                store.commit('assignMatchModalLoading', false);
            });
        },

        // Go through all the data in the match and parse out all the json text in it
        parseMatchDataFromResponse($match) {
            var tempMatch = _.cloneDeep($match);
            _.forEach(tempMatch.matchTeams, (match, matchIndex) => {
                if (match.bans != undefined) {
                    var parsedBans = JSON.parse(match.bans);
                    tempMatch.matchTeams[matchIndex].bans = parsedBans;
                }
            })

            // while we're going through each match participant in the match, lets put the respective match participant identies in the object
            // so it's easier to manipulate later in the code
            _.forEach(tempMatch.matchParticipants, (participant, participantIndex) => {
                var parsedStats = JSON.parse(participant.stats);
                tempMatch.matchParticipants[participantIndex].stats = parsedStats;

                if (participant.runes != undefined && participant.runes != "") {
                    var parsedRunes = JSON.parse(participant.runes);
                    tempMatch.matchParticipants[participantIndex].runes = parsedRunes;
                }

                if (participant.timeline != undefined && participant.timeline != "") {
                    var parsedTimeline = JSON.parse(participant.timeline);
                    tempMatch.matchParticipants[participantIndex].timeline = parsedTimeline;
                }

                var tempParsedIdentity = {};
                _.forEach(tempMatch.MatchParticipantIdentities, (identity, identityIndex) => {
                    if (identity.participantId === participant.participantId) {
                        tempParsedIdentity = identity;
                    }
                });
                tempMatch.matchParticipants[participantIndex].participantIdentity = tempParsedIdentity;
            })

            delete tempMatch.participant_identities;
            delete tempMatch.participants;
            delete tempMatch.teams;


            return tempMatch;
        },
    },

    computed: {
        testSummoner1 : function() { return store.state.testSummoner1; },
        testSummoner2 : function() { return store.state.testSummoner2; },

        modalMatch : function() { return store.state.modalMatch; },
        matchModalLoading : function() { return store.state.matchModalLoading; },

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
        summoner1OtherMatchList : function() {
            return store.state.summoner1.otherMatchList;
        },

        summoner2RankedMatchList : function() {
            return store.state.summoner2.rankedMatchList;
        },
        summoner2NormalMatchList : function() {
            return store.state.summoner2.normalMatchList;
        },
        summoner2OtherMatchList : function() {
            return store.state.summoner2.otherMatchList;
        },


        summoner1CurrentRank : function() {
            return this.summoner1.rankedData == undefined || _.isEmpty(this.summoner1.rankedData)
                ? 'Unranked' : this.summoner1.rankedData.tier + ' ' + this.summoner1.rankedData.rank
        },
        summoner1RankName : function() {
            return this.summoner1.rankedData == undefined || _.isEmpty(this.summoner1.rankedData)
                ? 'Unranked' : this.summoner1.rankedData.leagueName
        },
        summoner1Ratio : function() {
            return this.summoner1.rankedData == undefined || _.isEmpty(this.summoner1.rankedData)
                ? 'Unranked' :
                this.summoner1.rankedData.leaguePoints + ' LP / ' +
                this.summoner1.rankedData.wins + ' wins / ' +
                this.summoner1.rankedData.losses + ' losses';
        },
        summoner1RatioPercent : function() {
            if (this.summoner1.rankedData == undefined || _.isEmpty(this.summoner1.rankedData) ) {
                return 'Unranked';
            } else {
                var wins = parseInt(this.summoner1.rankedData.wins);
                var total = parseInt(this.summoner1.rankedData.wins) + parseInt(this.summoner1.rankedData.losses);
                return ((wins / total) * 100).toFixed(2) + '%';
            }
        },
        summoner2CurrentRank : function() {
            return this.summoner2.rankedData == undefined || _.isEmpty(this.summoner2.rankedData)
                ? 'Unranked' : this.summoner2.rankedData.tier + ' ' + this.summoner2.rankedData.rank
        },
        summoner2RankName : function() {
            return this.summoner2.rankedData == undefined || _.isEmpty(this.summoner2.rankedData)
                ? 'Unranked' : this.summoner2.rankedData.leagueNam
        },
        summoner2Ratio : function() {
            return this.summoner2.rankedData == undefined || _.isEmpty(this.summoner2.rankedData)
                ? 'Unranked' :
                this.summoner2.rankedData.leaguePoints + ' LP / ' +
                this.summoner2.rankedData.wins + ' wins / ' +
                this.summoner2.rankedData.losses + ' losses';
        },
        summoner2RatioPercent : function() {
            if (this.summoner2.rankedData == undefined || _.isEmpty(this.summoner2.rankedData)) {
                return 'Unranked';
            } else {
                var wins = parseInt(this.summoner2.rankedData.wins);
                var total = parseInt(this.summoner2.rankedData.wins) + parseInt(this.summoner2.rankedData.losses);
                return ((wins / total) * 100).toFixed(2) + '%';
            }
        },
    }
}