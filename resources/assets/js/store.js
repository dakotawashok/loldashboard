var Vuex = require('vuex');

var store = new Vuex.Store({
    state : {
        staticInfo : {
            champions : []
        }
    },
    mutations : {
        assignChampions (state, championsList) {
            state.staticInfo.champions = championsList;
        }
    }
});

export default store;