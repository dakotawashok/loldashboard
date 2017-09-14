<template>
    <div class="row">
        <h3>Summary Contents</h3>
        <div class="col-sm-6 view-form-group" style="text-align: right;">
            <label>Graph View: </label> <input type="radio" name="view" value="graph" v-model="view"/><br />
            <label>Table View: </label> <input type="radio" name="view" value="table" v-model="view"/>
        </div>
        <div class="col-sm-6 stat-form-group" style="text-align: left;">
            <label>Normal Stats: </label> <input type="radio" name="stat" value="normal" v-model="stat"/><br />
            <label>Average Stats: </label> <input type="radio" name="stat" value="average" v-model="stat"/>
        </div>

    </div>
</template>

<script>
    import store from '../store.js';

    export default {
        props : [

        ],
        methods : {


        },
        data : function() {
            return {

            }
        },
        computed : {
            'summoner1': function() {
                return store.state.summoner1;
            },
            'summoner2': function() {
                return store.state.summoner2;
            }
        },
        mounted() {

        }
    }
</script>

<style scoped>

</style>