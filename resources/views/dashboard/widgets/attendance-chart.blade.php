{{-- Attendance Chart Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
    <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Відвідуваність богослужінь</h2>
    <div class="h-48">
        <canvas id="attendanceChart"></canvas>
    </div>
    <div class="mt-4 flex items-center justify-center gap-4 text-sm text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-primary-500"></span>
            <span>За останні 4 тижні</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
onPageReady(function() {
    const ctx = document.getElementById('attendanceChart');
    if (ctx) {
        var old = Chart.getChart(ctx); if (old) old.destroy();
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? '#374151' : '#f3f4f6';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json(collect($attendanceData)->pluck('date')),
                datasets: [{
                    label: 'Відвідуваність',
                    data: @json(collect($attendanceData)->pluck('count')),
                    borderColor: '{{ $currentChurch->primary_color ?? "#3b82f6" }}',
                    backgroundColor: '{{ $currentChurch->primary_color ?? "#3b82f6" }}20',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '{{ $currentChurch->primary_color ?? "#3b82f6" }}',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor, stepSize: 10 }
                    }
                }
            }
        });
    }
});
</script>
@endpush
