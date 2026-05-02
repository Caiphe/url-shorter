import Chart from 'chart.js/auto';

document.addEventListener('alpine:init', () => {
    Alpine.data('urlClickBarChart', (labels, values) => ({
        chart: null,

        init() {
            const ctx = this.$refs.canvas.getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Clicks',
                            data: values,
                            backgroundColor: 'rgba(99, 102, 241, 0.65)',
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });
        },

        destroy() {
            if (this.chart !== null) {
                this.chart.destroy();
                this.chart = null;
            }
        },
    }));
});
