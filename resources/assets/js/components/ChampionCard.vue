<template>
    <div v-if="!disabled">
        <h3>Champion : {{champion.id}}</h3>
        <ul>
            <li v-for="(key, value) in stats.stats">{{key}} : {{value}}</li>
        </ul>
    </div>
</template>

<script>
    import store from '../store.js';


    export default {
        props : [
            'championStats',
            'currentSubMenu'
        ],
        methods : {

        },
        computed : {
            stats : function() {
                for (var stats in this.championStats) {
                    if (this.championStats[stats].id == this.currentSubMenu) {
                        console.log(this.championStats[stats]);
                        return this.championStats[stats];
                    }
                }
            },
            champion : function() {
                for (var champion in store.state.staticInfo.champions) {
                    if (store.state.staticInfo.champions[champion].key == this.currentSubMenu) {
                        console.log(store.state.staticInfo.champions[champion]);
                        return store.state.staticInfo.champions[champion];
                    }
                }
            }
        },
        data : function() {
            return {
                disabled : false
            }
        },
        mounted() {
            if (this.currentSubMenu == "0") {
                this.disabled = true;
            }
        }
    }
</script>

<style scoped>

</style>

