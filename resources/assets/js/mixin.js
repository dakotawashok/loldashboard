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