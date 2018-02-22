<script>
    import { Line, mixins } from 'vue-chartjs';
    import store from '../store.js';

    export default {
        extends: Line,
        mixins: [mixins.reactiveProp],
        props : [
            'chartData',
            'dataKey',
        ],
        mounted() {
            this.renderChart(this.chartData, {
                legend : {
                    labels : {
                        fontColor: "#2d2d2d"
                    }
                }
            });
        }
    };
</script>