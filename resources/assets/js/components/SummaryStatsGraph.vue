<script>
    import { Pie, mixins } from 'vue-chartjs';
    import store from '../store.js';

    export default {
        extends: Pie,
        mixins: [mixins.reactiveProp],
        props : [
            'chartData',
            'dataKey',
        ],
        mounted() {
            this.renderChart(this.chartData, {
                legend : {
                    labels : {
                        fontColor: "#f8f8ff"
                    }
                }
            });
        }
    };
</script>