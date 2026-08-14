(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const canvasEl = document.getElementById('cosyMonthlyChart');
        if (!canvasEl || typeof Chart === 'undefined') {
            return;
        }

        const labels = (typeof cosyDashboardData !== 'undefined' && cosyDashboardData.labels) ? cosyDashboardData.labels : [];
        const data = (typeof cosyDashboardData !== 'undefined' && cosyDashboardData.data) ? cosyDashboardData.data : [];

        const ctx = canvasEl.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Bookings',
                    data: data,
                    borderColor: '#9b4593',
                    backgroundColor: 'rgba(155,69,147,0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#9b4593',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#94a3b8', font: { size: 12 } },
                        grid: { color: '#f5eaf4' }
                    },
                    x: {
                        ticks: { color: '#94a3b8', font: { size: 12 } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
})();
