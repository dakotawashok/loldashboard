import store from '../js/store.js';

export default {
    created: function() {

    },

    data () {
        return {

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
    },

    computed: {
        summoner1ProfileIconUrl : function() { return "http://ddragon.leagueoflegends.com/cdn/7.18.1/img/profileicon/" + this.summoner1.summoner.profileIconId + ".png"; },
        summoner2ProfileIconUrl : function() { return "http://ddragon.leagueoflegends.com/cdn/7.18.1/img/profileicon/" + this.summoner2.summoner.profileIconId + ".png"; },

        staticInfo : function() { return store.state.staticInfo; },
        staticChampions : function() { return store.state.staticInfo.champions; },

        summoner1 : function() { return store.state.summoner1; },
        summoner2 : function() { return store.state.summoner2; },

        summoner1Loaded : function() { return store.state.summoner1.loaded; },
        summoner2Loaded : function() { return store.state.summoner2.loaded; },

        summoner2RankedMatchList : function() {
            return store.state.summoner2.rankedMatchList;
        },
        summoner2NormalMatchList : function() {
            return store.state.summoner2.normalMatchList;
        },

        loading : function() { return store.state.loading; },
    }
}