<script>
    import { Bar, mixins } from 'vue-chartjs';
    import store from '../store.js';

    export default {
        extends: Bar,
        mixins: [mixins.reactiveProp],
        props : [
            'chartData',
            'dataKey',
        ],
        mounted() {
            this.renderChart(this.chartData, {
                legend : {
                    labels : {
                        fontColor: "#1a1a1a"
                    }
                }
            });
        }
    };
</script>