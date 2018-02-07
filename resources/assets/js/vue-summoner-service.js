import $ from 'jquery';
import Promise from 'es6-promise'

export default {
    install(Vue, options) {
        var empty_summoner_object = {
            summonerName : '',
            summonerNumber : '',
            accountNumber :'',

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
                return temp_summoner;
            }).catch((resp) => {
                console.log(resp);
                console.log('error');
            })
        }

        Vue.prototype.$summoner_service.load_matchlist = function(matchListType, $params, summoner) {
            var temp_summoner = _.cloneDeep(summoner);
            var match_list = _getMatchList(matchListType, $params, summoner)

            switch (matchListType) {
                case 'ranked' :
                    temp_summoner.rankedMatchList = match_list;
                    break;
                case 'normal' :
                    temp_summoner.normalMatchList = match_list;
                    break;
                case 'other' :
                    temp_summoner.otherMatchList = match_list;
                    break;
            }

            return temp_summoner;
        }

        Vue.prototype.$summoner_service.load_defined_matchlist = function(matchListType, $params, summoner) {
            var temp_summoner = _.cloneDeep(summoner);
            var match_list = _getDefinedMatchList(matchListType, $params, summoner)

            switch (matchListType) {
                case 'ranked' :
                    temp_summoner.definedRankedMatchList = match_list;
                    break;
                case 'normal' :
                    temp_summoner.definedRankedMatchList = match_list;
                    break;
                case 'other' :
                    temp_summoner.definedRankedMatchList = match_list;
                    break;
            }

            return temp_summoner;
        }


        function _parseSummonerDataFromSummonerResponse(response, summoner) {
            var parsedResponse = JSON.parse(response);
            summoner.summonerName = parsedResponse.name;
            summoner.summonerNumber = parsedResponse.id;
            summoner.accountNumber = parsedResponse.accountId;
            summoner.championMastery = JSON.parse(parsedResponse.championMastery);
            summoner.profileIconId = parsedResponse.profileIconId;
            summoner.summonerLevel = parsedResponse.summonerLevel;
            summoner.league = JSON.parse(parsedResponse.league);
            summoner.revisionDate = parsedResponse.revisionDate;
            summoner.created_at = parsedResponse.created_at;
            summoner.updated_at = parsedResponse.updated_at;
        }

        function _getMatchList(matchListType, $params, summoner) {
            console.log('getting match list');
            var data_object = {
                'matchListType' : matchListType,
                'accountId' : summoner.accountNumber,
            }

            if ($params != undefined || $params != null) {
                data_object.params = $params;
            }

            return $.get('/summoner/' + (summoner.summonerNumber) + '/getMatchList', data_object)
            return JSON.parse(JSON.parse(resp).matches);
        }

        function _getDefinedMatchList(matchListType, $params, summoner) {
            console.log('getting defined match list');
            var data_object = {
                'matchListType' : matchListType,
                'accountId' : summoner.accountNumber,
            }

            if ($params != undefined || $params != null) {
                data_object.params = $params;
            }

            return $.get('/summoner/' + (summoner.summonerNumber) + '/getDefinedMatchList', data_object)
        }
    }
}