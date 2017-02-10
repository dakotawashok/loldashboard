<template>
    <div class="stats-view-wrapper">
        <h3>{{currentSubMenu}}</h3>
        <ul class="average-stats">
            <li v-for="(key, value) in averageData">{{value}} : {{key}}</li>
        </ul>
    </div>
</template>

<script>
    import store from '../store';

    export default {
        props : [
            'currentSubMenu',
            'averageData',
        ],
        data : function() {
            return {

            }
        },
        computed : {

        },
        methods : {

        },
        mounted () {

        }
    }
</script>

<style scoped>
    .average-stats {
        list-style: none;
        text-align: left;
    }
</style>