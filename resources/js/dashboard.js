import Chart from 'chart.js/auto';

const initializeDashboard = () => {
    const dataElement = document.getElementById('dashboardChartData');
    if (!dataElement || dataElement.dataset.initialized === '1') return;
    dataElement.dataset.initialized = '1';

    document.querySelectorAll('.dashboard-nav-link').forEach(link => link.addEventListener('click', event => {
        if (event.defaultPrevented || event.button > 0 || event.ctrlKey || event.metaKey || event.shiftKey) return;
        link.classList.add('is-navigating');
        link.setAttribute('aria-busy', 'true');
        const feedback = document.getElementById('dashboardActionFeedback');
        feedback?.classList.add('show'); feedback?.setAttribute('aria-hidden', 'false');
    }));

    const charts = JSON.parse(dataElement.textContent);
    const baseFont = { family: 'Inter, Segoe UI, sans-serif', size: 11 };
    const highestCondition = Math.max(...charts.conditions.values.map(Number), 1);
    const conditionScale = Math.max(5, Math.ceil((highestCondition * 1.18) / 5) * 5);

    const centerText = {
        id: 'centerText',
        afterDraw(chart) {
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const x = (chartArea.left + chartArea.right) / 2;
            const y = (chartArea.top + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.fillStyle = '#0F172A';
            ctx.font = '700 24px Inter, Segoe UI, sans-serif';
            ctx.fillText(new Intl.NumberFormat('id-ID').format(charts.total), x, y - 3);
            ctx.fillStyle = '#64748B';
            ctx.font = '11px Inter, Segoe UI, sans-serif';
            ctx.fillText('Total Aset', x, y + 18);
            ctx.restore();
        },
    };

    const categoryCanvas = document.getElementById('categoryChart');
    const conditionCanvas = document.getElementById('conditionChart');
    Chart.getChart(categoryCanvas)?.destroy();
    Chart.getChart(conditionCanvas)?.destroy();

    new Chart(categoryCanvas, {
        type: 'doughnut',
        data: { labels: charts.categories.labels, datasets: [{ data: charts.categories.values, backgroundColor: charts.categories.colors, borderColor: '#FFFFFF', borderWidth: 3, hoverOffset: 3 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '67%', animation: { duration: 450 }, plugins: { legend: { display: false }, tooltip: { bodyFont: baseFont, titleFont: baseFont, padding: 10 } } },
        plugins: [centerText],
    });

    new Chart(conditionCanvas, {
        type: 'bar',
        data: { labels: charts.conditions.labels, datasets: [{ data: charts.conditions.values, backgroundColor: charts.conditions.colors, borderRadius: 4, borderSkipped: false, maxBarThickness: 58 }] },
        options: { responsive: true, maintainAspectRatio: false, animation: { duration: 500, easing: 'easeOutQuart' }, scales: { x: { grid: { display: false }, border: { display: false }, ticks: { color: '#475569', font: baseFont } }, y: { beginAtZero: true, suggestedMax: conditionScale, border: { display: false }, grid: { color: '#E9EEF4', drawTicks: false }, ticks: { precision: 0, maxTicksLimit: 6, color: '#64748B', padding: 8, font: baseFont } } }, plugins: { legend: { display: false }, tooltip: { bodyFont: baseFont, titleFont: baseFont, padding: 10, callbacks: { label: context => ` ${new Intl.NumberFormat('id-ID').format(context.raw)} barang` } } } },
    });
};

initializeDashboard();
document.addEventListener('turbo:load', initializeDashboard);
