<script>
    import { Pie, mixins } from 'vue-chartjs';
    import store from '../store.js';

    export default Pie.extend({
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
    });
</script>