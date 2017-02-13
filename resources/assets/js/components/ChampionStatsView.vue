<template>
    <div class="champion-stats-view-wrapper">
        <h4>Champion Stats:</h4>
        <div class="container-fluid" v-if="!loading">
            <div class="row">
                <a class="col-sm-1" v-for="champion in championList" v-on:click="selectChampion(champion.champId)" href="#"><img :src="championImageUrl(champion.champName)" /></a>
            </div>
            <div class="row" v-if="champSelected != -1">
                <h4>Stats for {{staticChampion(champSelected)}}</h4>
                <div class="col-sm-6">
                    <ul>
                        <li v-for="(key, value) in summoner1ChampionStats">{{value}} : {{key}}</li>
                    </ul>
                </div>
                <div class="col-sm-6">
                    <ul>
                        <li class="summoner2" v-for="(key, value) in summoner2ChampionStats">{{value}} : {{key}}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import store from '../store.js';

    export default {
        props : [

        ],
        methods : {
            staticChampion : function(id) {
                for (var champion in store.state.staticInfo.champions) {
                    if (store.state.staticInfo.champions[champion].key == id) {
                        return store.state.staticInfo.champions[champion].id;
                    }
                }
            },

            championImageUrl : function(id) {
                return "http://ddragon.leagueoflegends.com/cdn/7.3.1/img/champion/" + id + ".png";
            },

            selectChampion : function(id) {
                this.champSelected = id;
            },
        },
        data : function() {
            return {
                champSelected : -1,
            }
        },
        computed : {
            championList : function() {
                var tempChampionList = [];
                for (var champion in store.state.summoner1.rankedData.champions) {
                    if (store.state.summoner1.rankedData.champions[champion].id != 0) {
                        tempChampionList.push(store.state.summoner1.rankedData.champions[champion].id);
                    }
                }
                for (var champion in store.state.summoner2.rankedData.champions) {
                    var found = false;
                    for (var summoner1Champ in tempChampionList) {
                        if (store.state.summoner2.rankedData.champions[champion].id == tempChampionList[summoner1Champ]) {
                            found = true;
                        }
                    }
                    if (!found && store.state.summoner2.rankedData.champions[champion].id != 0) {
                        tempChampionList.push(store.state.summoner2.rankedData.champions[champion].id)
                    }
                }
                for (var champion in tempChampionList) {
                    var tempChampId = tempChampionList[champion];
                    tempChampionList[champion] = {
                        'champId' : tempChampId,
                        'champName' : this.staticChampion(tempChampionList[champion])
                    };
                }

                tempChampionList.sort(function(champA, champB) {
                    if (champA.champName > champB.champName) {
                        return 1;
                    } else {
                        return -1;
                    }
                });

                var champList = tempChampionList;
                return champList;
            },

            loading : function() { return store.state.loading; },

            summoner1ChampionStats : function() {
                var tempChampStats = {};
                for (var tempChamp in store.state.summoner1.rankedData.champions) {
                    if (store.state.summoner1.rankedData.champions[tempChamp].id == this.champSelected) {
                        return store.state.summoner1.rankedData.champions[tempChamp].stats;
                    }
                }
                return null;
            },
            summoner2ChampionStats : function() {
                var tempChampStats = {};
                for (var tempChamp in store.state.summoner2.rankedData.champions) {
                    if (store.state.summoner2.rankedData.champions[tempChamp].id == this.champSelected) {
                        return store.state.summoner2.rankedData.champions[tempChamp].stats;
                    }
                }
                return null;
            },
        },
        mounted() {

        }
    }

</script>

<style scoped>
    img {
        height: 75px;
        width: 75px;
        margin: 5px;
        padding: 0px;
    }

    li {
        list-style: none;
        text-align: left;
    }

    li.summoner2 {
        text-align: right;
    }
</style>