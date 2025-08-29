/**
 * WhatsPro Premium Analytics Dashboard
 * Professional charts and data visualization
 */

class WhatsProAnalytics {
    constructor() {
        this.charts = {};
        this.apiBase = '/wp-json/whatspro/v1';
        this.currentPeriod = 30;
        this.refreshInterval = null;
        this.isRealtimeEnabled = false;

        this.init();
    }

    init() {
        this.loadChartJS();
        this.bindEvents();
        this.initializeCharts();
        this.loadInitialData();
    }

    loadChartJS() {
        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = () => this.initializeCharts();
            document.head.appendChild(script);
        }
    }

    bindEvents() {
        // Period selector
        const periodSelect = document.getElementById('analytics-period');
        if (periodSelect) {
            periodSelect.addEventListener('change', (e) => {
                this.currentPeriod = parseInt(e.target.value);
                this.refreshAllCharts();
            });
        }

        // Chart type selector
        const typeSelect = document.getElementById('chart-type');
        if (typeSelect) {
            typeSelect.addEventListener('change', (e) => {
                this.updateMainChart(e.target.value);
            });
        }

        // Real-time toggle
        const realtimeToggle = document.getElementById('realtime-toggle');
        if (realtimeToggle) {
            realtimeToggle.addEventListener('change', (e) => {
                this.toggleRealtime(e.target.checked);
            });
        }

        // Export buttons
        document.querySelectorAll('.export-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const format = e.target.dataset.format;
                this.exportData(format);
            });
        });

        // Refresh button
        const refreshBtn = document.getElementById('refresh-analytics');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshAllCharts());
        }
    }

    initializeCharts() {
        this.createMainChart();
        this.createGeographicChart();
        this.createSourcesChart();
        this.createConversionFunnel();
        this.createNamePatternsChart();
        this.createReferrersChart();
    }

    createMainChart() {
        const ctx = document.getElementById('main-analytics-chart');
        if (!ctx) return;

        this.charts.main = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Messages',
                    data: [],
                    borderColor: '#FFD700',
                    backgroundColor: 'rgba(255, 215, 0, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#FFD700',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            maxRotation: 45
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    createGeographicChart() {
        const ctx = document.getElementById('geographic-chart');
        if (!ctx) return;

        this.charts.geographic = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#FFD700', '#FFA500', '#FF8C00', '#FF6347', '#FF4500',
                        '#FFE4B5', '#FFF8DC', '#F0E68C', '#BDB76B', '#DAA520'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1200
                }
            }
        });
    }

    createSourcesChart() {
        const ctx = document.getElementById('sources-chart');
        if (!ctx) return;

        this.charts.sources = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Leads by Source',
                    data: [],
                    backgroundColor: 'rgba(255, 215, 0, 0.8)',
                    borderColor: '#FFD700',
                    borderWidth: 2,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        callbacks: {
                            label: function(context) {
                                return `Leads: ${context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutBounce'
                }
            }
        });
    }

    createConversionFunnel() {
        const ctx = document.getElementById('conversion-funnel');
        if (!ctx) return;

        // Custom funnel chart implementation
        this.charts.funnel = {
            canvas: ctx,
            ctx: ctx.getContext('2d'),
            data: [],
            draw: function() {
                const ctx = this.ctx;
                const canvas = this.canvas;
                const data = this.data;

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (data.length === 0) return;

                const maxValue = Math.max(...data.map(d => d.value));
                const barHeight = canvas.height / data.length;
                const centerX = canvas.width / 2;

                data.forEach((item, index) => {
                    const y = index * barHeight;
                    const width = (item.value / maxValue) * canvas.width * 0.8;
                    const x = centerX - width / 2;

                    // Create gradient
                    const gradient = ctx.createLinearGradient(x, y, x + width, y);
                    gradient.addColorStop(0, '#FFD700');
                    gradient.addColorStop(1, '#FFA500');

                    ctx.fillStyle = gradient;
                    ctx.fillRect(x, y + 5, width, barHeight - 10);

                    // Add text
                    ctx.fillStyle = '#333';
                    ctx.font = 'bold 12px Arial';
                    ctx.textAlign = 'center';
                    ctx.fillText(item.label, centerX, y + barHeight / 2 + 4);

                    ctx.fillStyle = '#666';
                    ctx.font = '11px Arial';
                    ctx.fillText(item.value, centerX, y + barHeight - 5);
                });
            }
        };
    }

    createNamePatternsChart() {
        const ctx = document.getElementById('name-patterns-chart');
        if (!ctx) return;

        this.charts.namePatterns = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Name Initial Distribution',
                    data: [],
                    backgroundColor: 'rgba(255, 215, 0, 0.2)',
                    borderColor: '#FFD700',
                    borderWidth: 2,
                    pointBackgroundColor: '#FFD700',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        },
                        pointLabels: {
                            font: {
                                size: 11,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });
    }

    createReferrersChart() {
        const ctx = document.getElementById('referrers-chart');
        if (!ctx) return;

        this.charts.referrers = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Top Referrers',
                    data: [],
                    backgroundColor: 'rgba(255, 165, 0, 0.8)',
                    borderColor: '#FFA500',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });
    }

    async loadInitialData() {
        this.showLoading();

        try {
            await Promise.all([
                this.loadMainChartData(),
                this.loadGeographicData(),
                this.loadSourcesData(),
                this.loadMetaSummary(),
                this.loadConversionData()
            ]);
        } catch (error) {
            console.error('Error loading analytics data:', error);
            this.showError('Failed to load analytics data');
        }

        this.hideLoading();
    }

    async loadMainChartData(type = 'messages') {
        try {
            const response = await fetch(`${this.apiBase}/analytics/chart-data?type=${type}&period=${this.currentPeriod}`);
            const result = await response.json();

            if (result.success && this.charts.main) {
                this.charts.main.data.labels = result.data.labels;
                this.charts.main.data.datasets[0].data = result.data.values;
                this.charts.main.data.datasets[0].label = result.data.label;
                this.charts.main.update();
            }
        } catch (error) {
            console.error('Error loading main chart data:', error);
        }
    }

    async loadGeographicData() {
        try {
            const response = await fetch(`${this.apiBase}/analytics/geographic?period=${this.currentPeriod}`);
            const result = await response.json();

            if (result.success && this.charts.geographic) {
                this.charts.geographic.data.labels = result.data.labels;
                this.charts.geographic.data.datasets[0].data = result.data.values;
                this.charts.geographic.update();

                // Update stats
                this.updateStat('total-leads', result.data.total);
                this.updateStat('unique-cities', result.data.unique_cities);
            }
        } catch (error) {
            console.error('Error loading geographic data:', error);
        }
    }

    async loadSourcesData() {
        try {
            const response = await fetch(`${this.apiBase}/analytics/sources?period=${this.currentPeriod}`);
            const result = await response.json();

            if (result.success && this.charts.sources) {
                this.charts.sources.data.labels = result.data.labels;
                this.charts.sources.data.datasets[0].data = result.data.values;
                this.charts.sources.update();

                this.updateStat('unique-sources', result.data.unique_sources);
            }
        } catch (error) {
            console.error('Error loading sources data:', error);
        }
    }

    async loadMetaSummary() {
        try {
            const response = await fetch(`${this.apiBase}/analytics/meta-summary?period=${this.currentPeriod}`);
            const result = await response.json();

            if (result.success) {
                this.updateMetaStats(result.data);
                this.updateNamePatterns(result.data.name_patterns);
                this.updateReferrers(result.data.referrers);
            }
        } catch (error) {
            console.error('Error loading meta summary:', error);
        }
    }

    async loadConversionData() {
        // Mock conversion funnel data - replace with real API call
        const funnelData = [
            { label: 'Website Visits', value: 1000 },
            { label: 'WhatsApp Opens', value: 750 },
            { label: 'Messages Sent', value: 500 },
            { label: 'Leads Generated', value: 150 },
            { label: 'Conversions', value: 45 }
        ];

        if (this.charts.funnel) {
            this.charts.funnel.data = funnelData;
            this.charts.funnel.draw();
        }
    }

    updateMainChart(type) {
        this.loadMainChartData(type);
    }

    updateMetaStats(data) {
        // Update various meta statistics displays
        Object.keys(data).forEach(key => {
            const element = document.getElementById(`meta-${key}`);
            if (element) {
                element.textContent = Array.isArray(data[key]) ? data[key].length : data[key];
            }
        });
    }

    updateNamePatterns(patterns) {
        if (!this.charts.namePatterns) return;

        const labels = patterns.map(p => p.initial);
        const values = patterns.map(p => p.count);

        this.charts.namePatterns.data.labels = labels;
        this.charts.namePatterns.data.datasets[0].data = values;
        this.charts.namePatterns.update();
    }

    updateReferrers(referrers) {
        if (!this.charts.referrers) return;

        // Take top 10 referrers
        const topReferrers = referrers.slice(0, 10);
        const labels = topReferrers.map(r => this.truncateUrl(r.referrer));
        const values = topReferrers.map(r => r.count);

        this.charts.referrers.data.labels = labels;
        this.charts.referrers.data.datasets[0].data = values;
        this.charts.referrers.update();
    }

    updateStat(statId, value) {
        const element = document.getElementById(statId);
        if (element) {
            element.textContent = value.toLocaleString();
        }
    }

    truncateUrl(url, maxLength = 30) {
        if (url.length <= maxLength) return url;
        return url.substring(0, maxLength - 3) + '...';
    }

    toggleRealtime(enabled) {
        this.isRealtimeEnabled = enabled;

        if (enabled) {
            this.startRealtimeUpdates();
        } else {
            this.stopRealtimeUpdates();
        }
    }

    startRealtimeUpdates() {
        this.refreshInterval = setInterval(() => {
            this.loadRealtimeData();
        }, 30000); // Update every 30 seconds
    }

    stopRealtimeUpdates() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    async loadRealtimeData() {
        try {
            const response = await fetch(`${this.apiBase}/analytics/realtime`);
            const result = await response.json();

            if (result.success) {
                this.updateRealtimeStats(result.data);
            }
        } catch (error) {
            console.error('Error loading realtime data:', error);
        }
    }

    updateRealtimeStats(data) {
        const realtimeIndicator = document.getElementById('realtime-indicator');
        if (realtimeIndicator) {
            realtimeIndicator.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
        }

        // Update specific realtime metrics
        Object.keys(data).forEach(key => {
            const element = document.getElementById(`realtime-${key}`);
            if (element) {
                element.textContent = data[key];
            }
        });
    }

    refreshAllCharts() {
        this.loadInitialData();
    }

    async exportData(format) {
        try {
            const response = await fetch(`${this.apiBase}/analytics/export`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    format: format,
                    period: this.currentPeriod,
                    type: 'all'
                })
            });

            if (response.ok) {
                // Trigger download
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `whatspro-analytics-${Date.now()}.${format}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            }
        } catch (error) {
            console.error('Error exporting data:', error);
            this.showError('Failed to export data');
        }
    }

    showLoading() {
        const loader = document.getElementById('analytics-loader');
        if (loader) {
            loader.style.display = 'block';
        }
    }

    hideLoading() {
        const loader = document.getElementById('analytics-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    showError(message) {
        const errorDiv = document.getElementById('analytics-error');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
    }
}

// Initialize analytics when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('main-analytics-chart')) {
        window.whatsProAnalytics = new WhatsProAnalytics();
    }
});

// Global functions for backward compatibility
function updateChart(type) {
    if (window.whatsProAnalytics) {
        window.whatsProAnalytics.updateMainChart(type);
    }
}

function exportReport(format) {
    if (window.whatsProAnalytics) {
        window.whatsProAnalytics.exportData(format);
    }
}

function refreshAnalytics() {
    if (window.whatsProAnalytics) {
        window.whatsProAnalytics.refreshAllCharts();
    }
}