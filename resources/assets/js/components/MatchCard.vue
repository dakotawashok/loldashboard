<template>
    <div class="ui segment">
        {{match}}
    </div>
</template>

<script>
    import store from '../store.js';
    import mixin from '../mixin.js';

    export default {
        props : [
            "match"
        ],
        mixins: [
            mixin
        ],
        methods : {

        },
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

</style>