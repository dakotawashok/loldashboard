import $ from 'jquery';
import Promise from 'es6-promise'

export default {
    install(Vue, options) {
        var empty_summoner_object = {
            summonerName : '',
            summonerNumber : '',
            accountId :'',

            championMastery : [],
            profileIconId : '',
            summonerLevel : '',
            rankedData : [],

            rankedMatchList : [],
            normalMatchList : [],
            otherMatchList : [],

            definedRankedMatchList : [],
            definedNormalMatchList : [],
            definedOtherMatchList : [],

            loading : false,
            loaded : false,
        }

        Vue.prototype.$summoner_service = {}

        Vue.prototype.$summoner_service.make_new_summoner = function() {
            return empty_summoner_object;
        }

        Vue.prototype.$summoner_service.load_new_summoner = function(summoner, identity, isSummonerName) {
            var temp_summoner = _.cloneDeep(summoner);

            if (isSummonerName) {
                temp_summoner.summonerName = identity;
            } else {
                temp_summoner.summonerNumber = identity;
            }

            return $.get('/summoner/' + (temp_summoner.summonerName != '' ? temp_summoner.summonerName : temp_summoner.summonerNumber)).then((resp) => {
                _parseSummonerDataFromSummonerResponse(resp, temp_summoner);
                return _getMatchList('ranked', null, temp_summoner);
            }).then((resp) => {
                var matchList = JSON.parse(JSON.parse(resp).matches);
                temp_summoner.rankedMatchList = matchList;
                return _getDefinedMatchList('ranked', null, temp_summoner);
            }).then((resp) => {
                var definedMatchList = JSON.parse(resp);
                temp_summoner.definedRankedMatchList = definedMatchList;
                _parseMatchListDataFromResponse(temp_summoner.rankedMatchList, temp_summoner.definedRankedMatchList);
                _assignRankedData(temp_summoner);
                return temp_summoner;
            }).catch((resp) => {
                console.log(resp);
                console.log('error');
            })
        }

        Vue.prototype.$summoner_service.load_matchlist  = function(matchListType, $params, summoner) {
            var temp_summoner = _.cloneDeep(summoner);
            return _getMatchList(matchListType, $params, summoner).then((resp) => {
                return JSON.parse(JSON.parse(resp).matches);
            })
        }

        Vue.prototype.$summoner_service.load_defined_matchlist = function(matchListType, $params, summoner) {
            var temp_summoner = _.cloneDeep(summoner);
            return _getDefinedMatchList(matchListType, $params, summoner).then((resp) => {
                return JSON.parse(resp);
            })
        }

        Vue.prototype.$summoner_service.parse_match_list_data = function(tempMatchList, tempMatchListDefined) {
            _parseMatchListDataFromResponse(tempMatchList, tempMatchListDefined);
        }

        Vue.prototype.$summoner_service.assemble_profile_icon_url = function(API_VERSION, profileIconId) {
            return "http://ddragon.leagueoflegends.com/cdn/" + API_VERSION + "/img/profileicon/" + profileIconId + ".png";
        }

        function _parseSummonerDataFromSummonerResponse(response, summoner) {
            var parsedResponse = JSON.parse(response);
            summoner.summonerName = parsedResponse.name;
            summoner.summonerNumber = parsedResponse.id;
            summoner.accountId = parsedResponse.accountId;
            summoner.profileIconId = parsedResponse.profileIconId;
            summoner.summonerLevel = parsedResponse.summonerLevel;
            summoner.revisionDate = parsedResponse.revisionDate;
            summoner.created_at = parsedResponse.created_at;
            summoner.updated_at = parsedResponse.updated_at;

            summoner.championMastery = (typeof summoner.championMastery == 'string' ? JSON.parse(summoner.championMastery) : summoner.championMastery);
            summoner.league = (typeof summoner.league == 'string' ? JSON.parse(summoner.league) : summoner.league);
            summoner.masteries = (typeof summoner.masteries == 'string' ? JSON.parse(summoner.masteries) : summoner.masteries);
            summoner.runes = (typeof summoner.runes == 'string' ? JSON.parse(summoner.runes) : summoner.runes);
        }

        function _getMatchList(matchListType, $params, summoner) {
            var data_object = {
                'matchListType' : matchListType,
                'accountId' : summoner.accountId,
            }

            if ($params != undefined || $params != null) {
                data_object.params = $params;
            }

            return $.get('/summoner/' + (summoner.summonerNumber) + '/getMatchList', data_object)
        }

        function _getDefinedMatchList(matchListType, $params, summoner) {
            var data_object = {
                'matchListType' : matchListType,
                'accountId' : summoner.accountId,
            }

            if ($params != undefined || $params != null) {
                data_object.params = $params;
            }

            return $.get('/summoner/' + (summoner.summonerNumber) + '/getDefinedMatchList', data_object)
        }

        function _parseMatchListDataFromResponse(tempMatchList, tempMatchListDefined) {
            // Now we're going to add the defined match to each of the regular matches
            _.forEach(tempMatchList, (match) => {
                _.forEach(tempMatchListDefined, (defined_match) => {
                    if (match.gameId == defined_match.gameId) {
                        match.defined_match = defined_match;
                    }
                })
            })
        }

        // Go through all the summoner league ranked data and find the specific data that matches with this summoner
        function _assignRankedData(summoner) {
            if (summoner != undefined && summoner.league != undefined) {
                _.forEach(summoner.league, (league) => {
                    if (league.queueType == 'RANKED_SOLO_5x5') {
                        summoner.rankedData.freshBlood = league.freshBlood;
                        summoner.rankedData.hotStreak = league.hotStreak;
                        summoner.rankedData.inactive = league.inactive;
                        summoner.rankedData.leagueId = league.leagueId;
                        summoner.rankedData.losses = league.losses;
                        summoner.rankedData.playerOrTeamId = league.playerOrTeamId;
                        summoner.rankedData.playerOrTeamName = league.playerOrTeamName;
                        summoner.rankedData.queueType = league.queueType;
                        summoner.rankedData.rank = league.rank;
                        summoner.rankedData.tier = league.tier;
                        summoner.rankedData.veteran = league.veteran;
                        summoner.rankedData.wins = league.wins;
                        summoner.rankedData.leaguePoints = league.leaguePoints;
                        summoner.rankedData.leagueName = league.leagueName;
                    }
                })
            }
        }
    }
}