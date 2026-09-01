/**
 * ADMIN DASHBOARD CHART.JS RENDERER MODULE
 * 
 * USE CASE:
 * Renders interactive monthly appointment booking line chart on WP Admin -> CC Booking -> Dashboard.
 * 
 * HOW TO USE:
 * Executed automatically on DOMContentLoaded when hook === 'toplevel_page_cosy-booking-dashboard'.
 * Data is passed from DashboardAdmin.php via cosyDashboardData localization object.
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Checks for canvas element #cosyMonthlyChart and Chart.js global library.
 * 2. Extracts labels and booking count dataset array from cosyDashboardData.
 * 3. Instantiates Chart.js line graph with custom gradient brand colors (#9b4593).
 */
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
