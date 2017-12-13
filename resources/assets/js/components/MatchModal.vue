<template>
    <div id="match-modal-container">
        <div class="ui top attached tabular menu">
            <div class="active item" data-tab="overview">Overview</div>
            <div class="item" data-tab="timeline">Timeline</div>
            <div class="item" data-tab="misc">Misc</div>
        </div>
        <div class="ui bottom attached active tab segment" data-tab="overview">
            <h3>Overview</h3>
        </div>
        <div class="ui bottom attached tab segment" data-tab="timeline">
            <h3>Timeline</h3>
        </div>
        <div class="ui bottom attached tab segment" data-tab="misc">
            <h3>Misc</h3>
        </div>
    </div>
</template>

<script>
    import store from '../store.js';
    import mixin from '../mixin.js';

    export default {
        mixins: [
            mixin
        ],
        components: [

        ],
        mounted() {
            // initialize the tabs for the modal
            $('.menu .item').tab();

            console.log(this.match);
        },
        props : [
            "match",
        ],
        data : function() {
            return {

            }
        },
        computed : {

        },
        methods : {

        },
        watch : {

        }
    }
</script>

<style scoped>

</style>