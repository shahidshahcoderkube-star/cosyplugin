<?php

namespace Cosy\Appointments\Admin;

class DashboardAdmin
{
    public function render_booking_dashboard(): void
    {
        echo '<div class="wrap"><h1>' . __('Booking Dashboard', 'cosy-appointments') . '</h1>';

        // Chart container
        echo '<canvas id="cosyBookingChart" width="600" height="300"></canvas>';

        // Dummy data (later fetch dynamically from DB)
        $total_bookings = 120;
        $pending = 15;
        $completed = 95;
        $cancelled = 10;
        // $earnings = 4500;

        // Pass PHP data to JS
        echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
        echo '<script>
        const ctx = document.getElementById("cosyBookingChart").getContext("2d");
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["Pending", "Completed", "Cancelled", "Total"],
                datasets: [{
                    label: "Booking Stats",
                    data: [' . $pending . ', ' . $completed . ', ' . $cancelled . ', ' . $total_bookings . '],
                    backgroundColor: ["#f39c12","#27ae60","#c0392b","#2980b9"]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: "Booking Overview" }
                }
            }
        });
    </script>';
    }
}
