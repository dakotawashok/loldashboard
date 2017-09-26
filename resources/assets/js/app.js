
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 *
 *
 */

Vue.component('example', require('./components/Example.vue'));
Vue.component('dashboard', require('./components/Dashboard.vue'));
Vue.component('matchcard', require('./components/MatchCard.vue'));
Vue.component('rankedmatchlistview', require('./components/RankedMatchListView.vue'));
Vue.component('recentgamecard', require('./components/RecentGameCard.vue'));
Vue.component('recentgamesview', require('./components/RecentGamesView.vue'));
Vue.component('summarycontents', require('./components/SummaryContents.vue'));
Vue.component('championcard', require('./components/ChampionCard.vue'));
Vue.component('statsview', require('./components/StatsView.vue'));
Vue.component('championstatsview', require('./components/ChampionStatsView.vue'));

Vue.component('summarystatsgraph', require('./components/SummaryStatsGraph.vue'));
Vue.component('rankedmatchlistgraph', require('./components/RankedMatchListGraph.vue'));

var VueResource = require('vue-resource');
Vue.use(VueResource);

import VueCharts from 'vue-chartjs';


var store = require('./store.js');

const app = new Vue({
    el: '#app',
    store,
});
