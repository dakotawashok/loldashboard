<script>
    import { Bar, mixins } from 'vue-chartjs';
    import store from '../store.js';

    export default Bar.extend({
        mixins: [mixins.reactiveProp],
        props : [
            'chartData',
        ],
        mounted() {
            this.renderChart(this.chartData, {
                legend : {
                    labels : {
                        fontColor: "#f8f8ff"
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                scales : {
                    xAxes: [{
                        stacked: true
                    }],
                    yAxes: [{
                        stacked: true
                    }]
                }
            });
        }
    });
</script>