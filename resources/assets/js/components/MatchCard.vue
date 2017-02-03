<template>
    <a href="#" class="match-card col-sm-4 col-md-3" v-bind:style="styleObject">
        {{match.lane}}
        {{match.role}}
        {{match.champion}}
    </a>
</template>

<script>
    export default {
        props : [
            "match",
        ],
        mounted() {
        },
        data : function() {
            return {
                champion : {},
            }
        },
        computed :  {
            styleObject : function() {
                return "";
            }
        }
    }
</script>

<style scoped>
    .match-card {
        height: 150px;
    }
</style>