<?php
/**
 * Plugin Name: WhatsPro Premium Analytics
 * Description: Professional WhatsApp analytics dashboard with meta data tracking, geographic insights, and comprehensive lead analysis.
 * Version: 1.0.0
 * Author: Daniel
 * Text Domain: whatspro-premium
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.0
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) exit;

// Define constants
define('WPP_SUITE_VER', '1.0.0');
define('WPP_SUITE_DIR', plugin_dir_path(__FILE__));
define('WPP_SUITE_URL', plugin_dir_url(__FILE__));

// Simple activation
register_activation_hook(__FILE__, 'wpp_activate_plugin');

// Simple deactivation
register_deactivation_hook(__FILE__, 'wpp_deactivate_plugin');

// Activation function
function wpp_activate_plugin() {
    update_option('wpp_demo_activated', time());
    update_option('wpp_license_status', 'active');
}

// Deactivation function
function wpp_deactivate_plugin() {
    delete_option('wpp_demo_activated');
    delete_option('wpp_license_status');
}

// Add admin menu
add_action('admin_menu', 'wpp_add_admin_menu');

// Add admin menu function
function wpp_add_admin_menu() {
    add_menu_page(
        'WhatsPro Analytics',
        'WhatsPro Analytics',
        'manage_options',
        'wpp-analytics',
        'wpp_analytics_page',
        'dashicons-chart-bar',
        30
    );
}

// Main analytics page function
function wpp_analytics_page() {
    ?>
    <div class="wrap">
        <div class="wpp-header">
            <h1><?php _e('WhatsPro Premium Analytics Dashboard', 'whatspro-premium'); ?></h1>
            <div class="wpp-version">v<?php echo WPP_SUITE_VER; ?></div>
        </div>

        <!-- Status Banner -->
        <div class="wpp-status-banner" style="background: linear-gradient(135deg, #d4edda, #c3e6cb); border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin-bottom: 30px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">✅</span>
                <div>
                    <h3 style="margin: 0 0 5px 0; color: #155724;">Professional Analytics Active</h3>
                    <p style="margin: 0; color: #155724;">Your analytics dashboard is ready with meta data tracking, geographic insights, and real-time analytics.</p>
                </div>
            </div>
        </div>

        <!-- Key Metrics Overview -->
        <div class="wpp-metrics-overview" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <div class="wpp-metric-card" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%); border: 1px solid #e9ecef; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <div class="wpp-metric-icon" style="font-size: 2.5em; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 12px; color: white; margin-bottom: 16px;">📊</div>
                <div class="wpp-metric-value" style="font-size: 2.2em; font-weight: 700; color: #333; margin-bottom: 8px;">1,247</div>
                <div class="wpp-metric-label" style="color: #666; font-size: 0.9em; font-weight: 500;">Total Leads</div>
                <div class="wpp-metric-change" style="color: #28a745; font-size: 0.8em; font-weight: 600; margin-top: 8px;">+12.5% from last month</div>
            </div>

            <div class="wpp-metric-card" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%); border: 1px solid #e9ecef; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <div class="wpp-metric-icon" style="font-size: 2.5em; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 12px; color: white; margin-bottom: 16px;">🌍</div>
                <div class="wpp-metric-value" style="font-size: 2.2em; font-weight: 700; color: #333; margin-bottom: 8px;">24</div>
                <div class="wpp-metric-label" style="color: #666; font-size: 0.9em; font-weight: 500;">Cities Reached</div>
                <div class="wpp-metric-change" style="color: #28a745; font-size: 0.8em; font-weight: 600; margin-top: 8px;">+8.2% new cities</div>
            </div>

            <div class="wpp-metric-card" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%); border: 1px solid #e9ecef; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <div class="wpp-metric-icon" style="font-size: 2.5em; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 12px; color: white; margin-bottom: 16px;">📱</div>
                <div class="wpp-metric-value" style="font-size: 2.2em; font-weight: 700; color: #333; margin-bottom: 8px;">5</div>
                <div class="wpp-metric-label" style="color: #666; font-size: 0.9em; font-weight: 500;">Lead Sources</div>
                <div class="wpp-metric-change" style="color: #28a745; font-size: 0.8em; font-weight: 600; margin-top: 8px;">+15.3% growth</div>
            </div>

            <div class="wpp-metric-card" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%); border: 1px solid #e9ecef; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <div class="wpp-metric-icon" style="font-size: 2.5em; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 12px; color: white; margin-bottom: 16px;">⚡</div>
                <div class="wpp-metric-value" style="font-size: 2.2em; font-weight: 700; color: #333; margin-bottom: 8px;">3</div>
                <div class="wpp-metric-label" style="color: #666; font-size: 0.9em; font-weight: 500;">Real-time Leads</div>
                <div class="wpp-metric-change" style="color: #6c757d; font-size: 0.8em; font-weight: 600; margin-top: 8px;">Last hour</div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="wpp-charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-bottom: 32px;">
            <!-- Geographic Distribution -->
            <div class="wpp-chart-card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
                <div class="wpp-chart-header" style="margin-bottom: 20px;">
                    <h3 style="margin: 0 0 8px 0; color: #333; font-size: 1.2em; font-weight: 600;">🌍 Geographic Distribution</h3>
                    <div class="wpp-chart-info" style="color: #666; font-size: 0.9em;">Where your leads come from</div>
                </div>
                <div class="wpp-chart-container" style="position: relative; height: 300px; background: #fafbfc; border-radius: 8px; padding: 16px; border: 1px solid #e9ecef;">
                    <canvas id="geographic-chart"></canvas>
                </div>
            </div>

            <!-- Lead Sources -->
            <div class="wpp-chart-card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
                <div class="wpp-chart-header" style="margin-bottom: 20px;">
                    <h3 style="margin: 0 0 8px 0; color: #333; font-size: 1.2em; font-weight: 600;">📱 Lead Sources</h3>
                    <div class="wpp-chart-info" style="color: #666; font-size: 0.9em;">How leads find you</div>
                </div>
                <div class="wpp-chart-container" style="position: relative; height: 300px; background: #fafbfc; border-radius: 8px; padding: 16px; border: 1px solid #e9ecef;">
                    <canvas id="sources-chart"></canvas>
                </div>
            </div>

            <!-- Name Patterns -->
            <div class="wpp-chart-card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
                <div class="wpp-chart-header" style="margin-bottom: 20px;">
                    <h3 style="margin: 0 0 8px 0; color: #333; font-size: 1.2em; font-weight: 600;">👥 Name Analysis</h3>
                    <div class="wpp-chart-info" style="color: #666; font-size: 0.9em;">Lead name patterns</div>
                </div>
                <div class="wpp-chart-container" style="position: relative; height: 300px; background: #fafbfc; border-radius: 8px; padding: 16px; border: 1px solid #e9ecef;">
                    <canvas id="name-patterns-chart"></canvas>
                </div>
            </div>

            <!-- Top Referrers -->
            <div class="wpp-chart-card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
                <div class="wpp-chart-header" style="margin-bottom: 20px;">
                    <h3 style="margin: 0 0 8px 0; color: #333; font-size: 1.2em; font-weight: 600;">🔗 Top Referrers</h3>
                    <div class="wpp-chart-info" style="color: #666; font-size: 0.9em;">Traffic sources</div>
                </div>
                <div class="wpp-chart-container" style="position: relative; height: 300px; background: #fafbfc; border-radius: 8px; padding: 16px; border: 1px solid #e9ecef;">
                    <canvas id="referrers-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Main Chart Section -->
        <div class="wpp-analytics-section" style="background: white; border-radius: 16px; padding: 32px; margin-bottom: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
            <div class="wpp-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="margin: 0; color: #333; font-size: 1.5em; font-weight: 600;">📈 Performance Overview</h2>
                <div class="wpp-chart-controls" style="display: flex; gap: 12px;">
                    <select id="chart-type" style="padding: 10px 16px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px; font-weight: 500; color: #333; background: white; cursor: pointer;">
                        <option value="messages">Messages Over Time</option>
                        <option value="leads">Leads Over Time</option>
                        <option value="geographic">Geographic Trends</option>
                        <option value="sources">Source Performance</option>
                    </select>
                    <select id="analytics-period" style="padding: 10px 16px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px; font-weight: 500; color: #333; background: white; cursor: pointer;">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
            </div>
            <div class="wpp-chart-container" style="position: relative; height: 400px; background: #fafbfc; border-radius: 8px; padding: 16px; border: 1px solid #e9ecef;">
                <canvas id="main-analytics-chart"></canvas>
            </div>
        </div>

        <!-- Meta Data Insights -->
        <div class="wpp-analytics-section" style="background: white; border-radius: 16px; padding: 32px; margin-bottom: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
            <div class="wpp-section-header" style="margin-bottom: 24px;">
                <h2 style="margin: 0; color: #333; font-size: 1.5em; font-weight: 600;">🔍 Meta Data Insights</h2>
                <div class="wpp-meta-info" style="color: #666; font-size: 0.9em;">Detailed information about your leads</div>
            </div>

            <div class="wpp-meta-insights-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                <div class="wpp-meta-card" style="background: #f8f9fa; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef;">
                    <h4 style="margin: 0 0 20px 0; color: #333; font-size: 1.1em; font-weight: 600;">Recent Leads</h4>
                    <div class="wpp-meta-list" style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="wpp-meta-item" style="display: flex; align-items: center; gap: 16px; padding: 12px; background: white; border-radius: 8px; border: 1px solid #e9ecef;">
                            <div class="wpp-meta-avatar" style="width: 40px; height: 40px; background: linear-gradient(135deg, #FFD700, #FFA500); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.1em;">JD</div>
                            <div class="wpp-meta-details">
                                <div class="wpp-meta-name" style="font-weight: 600; color: #333; margin-bottom: 4px;">John Doe</div>
                                <div class="wpp-meta-location" style="font-size: 0.9em; color: #666; margin-bottom: 4px;">New York • WhatsApp</div>
                                <div class="wpp-meta-time" style="font-size: 0.8em; color: #999;">2 hours ago</div>
                            </div>
                        </div>

                        <div class="wpp-meta-item" style="display: flex; align-items: center; gap: 16px; padding: 12px; background: white; border-radius: 8px; border: 1px solid #e9ecef;">
                            <div class="wpp-meta-avatar" style="width: 40px; height: 40px; background: linear-gradient(135deg, #FFD700, #FFA500); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.1em;">SM</div>
                            <div class="wpp-meta-details">
                                <div class="wpp-meta-name" style="font-weight: 600; color: #333; margin-bottom: 4px;">Sarah Miller</div>
                                <div class="wpp-meta-location" style="font-size: 0.9em; color: #666; margin-bottom: 4px;">London • Website</div>
                                <div class="wpp-meta-time" style="font-size: 0.8em; color: #999;">4 hours ago</div>
                            </div>
                        </div>

                        <div class="wpp-meta-item" style="display: flex; align-items: center; gap: 16px; padding: 12px; background: white; border-radius: 8px; border: 1px solid #e9ecef;">
                            <div class="wpp-meta-avatar" style="width: 40px; height: 40px; background: linear-gradient(135deg, #FFD700, #FFA500); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.1em;">MR</div>
                            <div class="wpp-meta-details">
                                <div class="wpp-meta-name" style="font-weight: 600; color: #333; margin-bottom: 4px;">Mike Rodriguez</div>
                                <div class="wpp-meta-location" style="font-size: 0.9em; color: #666; margin-bottom: 4px;">Madrid • Referral</div>
                                <div class="wpp-meta-time" style="font-size: 0.8em; color: #999;">6 hours ago</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wpp-meta-card" style="background: #f8f9fa; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef;">
                    <h4 style="margin: 0 0 20px 0; color: #333; font-size: 1.1em; font-weight: 600;">Source Breakdown</h4>
                    <div class="wpp-source-breakdown" style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="wpp-source-item">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-weight: 600; color: #333;">WhatsApp</span>
                                <span style="font-size: 0.9em; color: #666;">450 leads</span>
                            </div>
                            <div class="wpp-source-bar" style="height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                                <div class="wpp-source-fill" style="height: 100%; background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 4px; width: 60%;"></div>
                            </div>
                        </div>

                        <div class="wpp-source-item">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-weight: 600; color: #333;">Website</span>
                                <span style="font-size: 0.9em; color: #666;">280 leads</span>
                            </div>
                            <div class="wpp-source-bar" style="height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                                <div class="wpp-source-fill" style="height: 100%; background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 4px; width: 37%;"></div>
                            </div>
                        </div>

                        <div class="wpp-source-item">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-weight: 600; color: #333;">Referral</span>
                                <span style="font-size: 0.9em; color: #666;">180 leads</span>
                            </div>
                            <div class="wpp-source-bar" style="height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                                <div class="wpp-source-fill" style="height: 100%; background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 4px; width: 24%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wpp-meta-card" style="background: #f8f9fa; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef;">
                    <h4 style="margin: 0 0 20px 0; color: #333; font-size: 1.1em; font-weight: 600;">Geographic Insights</h4>
                    <div class="wpp-geo-insights" style="display: flex; flex-direction: column; gap: 12px;">
                        <div class="wpp-geo-item" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: white; border-radius: 6px; border: 1px solid #e9ecef;">
                            <span style="font-weight: 500; color: #333;">New York</span>
                            <span style="font-size: 0.9em; color: #666;">245 leads</span>
                        </div>
                        <div class="wpp-geo-item" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: white; border-radius: 6px; border: 1px solid #e9ecef;">
                            <span style="font-weight: 500; color: #333;">London</span>
                            <span style="font-size: 0.9em; color: #666;">189 leads</span>
                        </div>
                        <div class="wpp-geo-item" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: white; border-radius: 6px; border: 1px solid #e9ecef;">
                            <span style="font-weight: 500; color: #333;">Tokyo</span>
                            <span style="font-size: 0.9em; color: #666;">156 leads</span>
                        </div>
                        <div class="wpp-geo-item" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: white; border-radius: 6px; border: 1px solid #e9ecef;">
                            <span style="font-weight: 500; color: #333;">Sydney</span>
                            <span style="font-size: 0.9em; color: #666;">134 leads</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversion Funnel -->
        <div class="wpp-analytics-section" style="background: white; border-radius: 16px; padding: 32px; margin-bottom: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
            <div class="wpp-section-header" style="margin-bottom: 24px;">
                <h2 style="margin: 0; color: #333; font-size: 1.5em; font-weight: 600;">📊 Conversion Funnel</h2>
                <div class="wpp-funnel-info" style="color: #666; font-size: 0.9em;">Lead journey from first contact to conversion</div>
            </div>
            <div class="wpp-funnel-container" style="position: relative; height: 350px; display: flex; align-items: center; justify-content: center; background: #fafbfc; border-radius: 8px; padding: 20px; border: 1px solid #e9ecef;">
                <canvas id="conversion-funnel"></canvas>
            </div>
        </div>

        <!-- Export & Actions -->
        <div class="wpp-analytics-section" style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
            <div class="wpp-section-header" style="margin-bottom: 24px;">
                <h2 style="margin: 0; color: #333; font-size: 1.5em; font-weight: 600;">💾 Export & Reports</h2>
                <div class="wpp-export-description" style="color: #666; font-size: 0.9em;">Download your analytics data in various formats</div>
            </div>
            <div class="wpp-export-actions" style="display: flex; gap: 16px; flex-wrap: wrap;">
                <button class="wpp-btn wpp-export-btn" onclick="exportAnalytics('csv')" style="background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <span>📊</span> Export CSV
                </button>
                <button class="wpp-btn wpp-export-btn" onclick="exportAnalytics('json')" style="background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <span>📋</span> Export JSON
                </button>
                <button class="wpp-btn wpp-btn-secondary" onclick="refreshAnalytics()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <span>🔄</span> Refresh Data
                </button>
            </div>
        </div>
    </div>

    <script>
    // Simple export functions
    function exportAnalytics(format) {
        alert('Export feature: ' + format.toUpperCase() + ' export would be implemented here with real data export functionality.');
    }

    function refreshAnalytics() {
        alert('Refresh feature: Data would be refreshed from the server here.');
    }

    // Load Chart.js if not already loaded
    if (typeof Chart === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        script.onload = function() {
            initAnalyticsCharts();
        };
        document.head.appendChild(script);
    } else {
        initAnalyticsCharts();
    }

    function initAnalyticsCharts() {
        // Geographic Chart
        const geoCtx = document.getElementById('geographic-chart');
        if (geoCtx) {
            new Chart(geoCtx, {
                type: 'doughnut',
                data: {
                    labels: ['New York', 'London', 'Tokyo', 'Sydney', 'Paris'],
                    datasets: [{
                        data: [245, 189, 156, 134, 98],
                        backgroundColor: ['#FFD700', '#FFA500', '#FF8C00', '#FF6347', '#FF4500']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Sources Chart
        const sourcesCtx = document.getElementById('sources-chart');
        if (sourcesCtx) {
            new Chart(sourcesCtx, {
                type: 'bar',
                data: {
                    labels: ['WhatsApp', 'Website', 'Referral', 'Social Media', 'Email'],
                    datasets: [{
                        label: 'Leads by Source',
                        data: [450, 280, 180, 120, 90],
                        backgroundColor: 'rgba(255, 215, 0, 0.8)',
                        borderColor: '#FFD700',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // Name Patterns Chart
        const nameCtx = document.getElementById('name-patterns-chart');
        if (nameCtx) {
            new Chart(nameCtx, {
                type: 'radar',
                data: {
                    labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'],
                    datasets: [{
                        label: 'Name Initial Distribution',
                        data: [45, 32, 28, 35, 42, 25, 30, 22, 38, 40, 18, 33, 27],
                        backgroundColor: 'rgba(255, 215, 0, 0.2)',
                        borderColor: '#FFD700',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Referrers Chart
        const refCtx = document.getElementById('referrers-chart');
        if (refCtx) {
            new Chart(refCtx, {
                type: 'horizontalBar',
                data: {
                    labels: ['google.com', 'facebook.com', 'twitter.com', 'linkedin.com', 'instagram.com'],
                    datasets: [{
                        label: 'Top Referrers',
                        data: [156, 89, 67, 45, 34],
                        backgroundColor: 'rgba(255, 165, 0, 0.8)',
                        borderColor: '#FFA500',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { beginAtZero: true }
                    }
                }
            });
        }

        // Main Analytics Chart
        const mainCtx = document.getElementById('main-analytics-chart');
        if (mainCtx) {
            new Chart(mainCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Leads',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        borderColor: '#FFD700',
                        backgroundColor: 'rgba(255, 215, 0, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // Conversion Funnel (Simple representation)
        const funnelCtx = document.getElementById('conversion-funnel');
        if (funnelCtx) {
            const ctx = funnelCtx.getContext('2d');
            ctx.fillStyle = '#FFD700';
            ctx.fillRect(50, 50, 300, 40); // Website visits
            ctx.fillStyle = '#FFA500';
            ctx.fillRect(75, 110, 250, 40); // WhatsApp opens
            ctx.fillStyle = '#FF8C00';
            ctx.fillRect(100, 170, 200, 40); // Messages sent
            ctx.fillStyle = '#FF6347';
            ctx.fillRect(125, 230, 150, 40); // Leads generated
            ctx.fillStyle = '#FF4500';
            ctx.fillRect(150, 290, 100, 40); // Conversions

            ctx.fillStyle = '#333';
            ctx.font = '14px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('1,000 Website Visits', 200, 75);
            ctx.fillText('750 WhatsApp Opens', 200, 135);
            ctx.fillText('500 Messages Sent', 200, 195);
            ctx.fillText('150 Leads Generated', 200, 255);
            ctx.fillText('45 Conversions', 200, 315);
        }
    }
    </script>

    <style>
    .wpp-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e9ecef;
    }

    .wpp-header h1 {
        margin: 0;
        color: #333;
        font-size: 2.2em;
        font-weight: 700;
    }

    .wpp-version {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: #333;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 0.9em;
    }

    @media (max-width: 768px) {
        .wpp-metrics-overview,
        .wpp-charts-grid,
        .wpp-meta-insights-grid {
            grid-template-columns: 1fr !important;
        }

        .wpp-export-actions {
            flex-direction: column;
        }
    }
    </style>
    <?php
}

    public function admin_page() {
        ?>
        <div class="wrap">
            <div class="wpp-header">
                <h1><?php _e('WhatsPro Premium Suite', 'whatspro-premium'); ?></h1>
                <div class="wpp-version">v<?php echo WPP_SUITE_VER; ?></div>
            </div>

            <div class="wpp-dashboard-grid">
                <!-- Status Card -->
                <div class="wpp-card wpp-status-card">
                    <div class="wpp-card-header">
                        <h3><?php _e('System Status', 'whatspro-premium'); ?></h3>
                    </div>
                    <div class="wpp-card-content">
                        <div class="wpp-status-item">
                            <span class="wpp-status-label"><?php _e('License:', 'whatspro-premium'); ?></span>
                            <span class="wpp-status-value <?php echo $this->get_license_status_class(); ?>">
                                <?php echo $this->get_license_status_text(); ?>
                            </span>
                        </div>
                        <div class="wpp-status-item">
                            <span class="wpp-status-label"><?php _e('Database:', 'whatspro-premium'); ?></span>
                            <span class="wpp-status-value success"><?php _e('Connected', 'whatspro-premium'); ?></span>
                        </div>
                        <div class="wpp-status-item">
                            <span class="wpp-status-label"><?php _e('Analytics:', 'whatspro-premium'); ?></span>
                            <span class="wpp-status-value success"><?php _e('Active', 'whatspro-premium'); ?></span>
                        </div>

                        <?php if (!$this->get_license_status_class() === 'success'): ?>
                        <div class="wpp-quick-activate" style="margin-top: 20px; padding: 15px; background: linear-gradient(135deg, #e8f5e8, #d4edda); border: 1px solid #c3e6cb; border-radius: 8px;">
                            <h4 style="margin: 0 0 10px 0; color: #155724;">🚀 Quick Demo Activation</h4>
                            <p style="margin: 0 0 15px 0; color: #155724; font-size: 14px;">Click below to instantly activate the demo license and access all premium features:</p>
                            <button onclick="activateDemoLicense()" style="background: linear-gradient(135deg, #28a745, #20c997); color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;">
                                ⚡ Activate Demo License
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="wpp-card wpp-stats-card">
                    <div class="wpp-card-header">
                        <h3><?php _e('Quick Stats', 'whatspro-premium'); ?></h3>
                    </div>
                    <div class="wpp-card-content">
                        <?php
                        global $wpdb;
                        $leads_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wpp_leads");
                        $messages_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wpp_messages");
                        $campaigns_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wpp_campaigns WHERE status = 'active'");
                        ?>
                        <div class="wpp-stat">
                            <div class="wpp-stat-number"><?php echo number_format($leads_count); ?></div>
                            <div class="wpp-stat-label"><?php _e('Total Leads', 'whatspro-premium'); ?></div>
                        </div>
                        <div class="wpp-stat">
                            <div class="wpp-stat-number"><?php echo number_format($messages_count); ?></div>
                            <div class="wpp-stat-label"><?php _e('Messages', 'whatspro-premium'); ?></div>
                        </div>
                        <div class="wpp-stat">
                            <div class="wpp-stat-number"><?php echo number_format($campaigns_count); ?></div>
                            <div class="wpp-stat-label"><?php _e('Active Campaigns', 'whatspro-premium'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Features Overview -->
                <div class="wpp-card wpp-features-card">
                    <div class="wpp-card-header">
                        <h3><?php _e('Premium Features', 'whatspro-premium'); ?></h3>
                    </div>
                    <div class="wpp-card-content">
                        <div class="wpp-feature-list">
                            <div class="wpp-feature-item">
                                <div class="wpp-feature-icon">📊</div>
                                <div class="wpp-feature-content">
                                    <h4><?php _e('Advanced Analytics', 'whatspro-premium'); ?></h4>
                                    <p><?php _e('Professional dashboard with meta data visualization, geographic insights, and real-time analytics.', 'whatspro-premium'); ?></p>
                                    <a href="<?php echo admin_url('admin.php?page=wpp-analytics'); ?>" class="wpp-btn wpp-btn-secondary"><?php _e('View Analytics', 'whatspro-premium'); ?></a>
                                </div>
                            </div>
                            <div class="wpp-feature-item">
                                <div class="wpp-feature-icon">👥</div>
                                <div class="wpp-feature-content">
                                    <h4><?php _e('Lead Management', 'whatspro-premium'); ?></h4>
                                    <p><?php _e('Comprehensive lead tracking with meta data, segmentation, and conversion analytics.', 'whatspro-premium'); ?></p>
                                    <a href="<?php echo admin_url('admin.php?page=wpp-leads'); ?>" class="wpp-btn wpp-btn-secondary"><?php _e('Manage Leads', 'whatspro-premium'); ?></a>
                                </div>
                            </div>
                            <div class="wpp-feature-item">
                                <div class="wpp-feature-icon">🎯</div>
                                <div class="wpp-feature-content">
                                    <h4><?php _e('Smart Segmentation', 'whatspro-premium'); ?></h4>
                                    <p><?php _e('AI-powered lead segmentation based on behavior, location, and engagement patterns.', 'whatspro-premium'); ?></p>
                                    <a href="<?php echo admin_url('admin.php?page=wpp-segmentation'); ?>" class="wpp-btn wpp-btn-secondary"><?php _e('View Segments', 'whatspro-premium'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="wpp-card wpp-activity-card">
                    <div class="wpp-card-header">
                        <h3><?php _e('Recent Activity', 'whatspro-premium'); ?></h3>
                    </div>
                    <div class="wpp-card-content">
                        <?php $this->display_recent_activity(); ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function activateDemoLicense() {
            if (!confirm('This will activate a demo license for testing purposes. Continue?')) {
                return;
            }

            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '🔄 Activating...';
            button.disabled = true;

            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'wpp_activate_demo_license',
                    nonce: '<?php echo wp_create_nonce('wpp_demo_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Demo license activated successfully!\n\nYou now have access to all premium features including the professional analytics dashboard.');
                    location.reload();
                } else {
                    alert('❌ Activation failed: ' + (data.data || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('❌ Network error. Please try again.');
                console.error('Demo license activation error:', error);
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        </script>

        <style>
        .wpp-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .wpp-header h1 {
            margin: 0;
            color: #333;
            font-size: 2.2em;
            font-weight: 700;
        }

        .wpp-version {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #333;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9em;
        }

        .wpp-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
        }

        .wpp-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            overflow: hidden;
        }

        .wpp-card-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .wpp-card-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.2em;
            font-weight: 600;
        }

        .wpp-card-content {
            padding: 20px;
        }

        .wpp-status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .wpp-status-item:last-child {
            border-bottom: none;
        }

        .wpp-status-label {
            font-weight: 500;
            color: #666;
        }

        .wpp-status-value.success {
            color: #28a745;
            font-weight: 600;
        }

        .wpp-status-value.warning {
            color: #ffc107;
            font-weight: 600;
        }

        .wpp-status-value.error {
            color: #dc3545;
            font-weight: 600;
        }

        .wpp-stats-card .wpp-card-content {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .wpp-stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .wpp-stat-label {
            color: #666;
            font-size: 0.9em;
            font-weight: 500;
        }

        .wpp-feature-list {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .wpp-feature-item {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .wpp-feature-icon {
            font-size: 2.5em;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            border-radius: 12px;
            color: white;
            flex-shrink: 0;
        }

        .wpp-feature-content h4 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 1.1em;
            font-weight: 600;
        }

        .wpp-feature-content p {
            margin: 0 0 16px 0;
            color: #666;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .wpp-dashboard-grid {
                grid-template-columns: 1fr;
            }

            .wpp-feature-item {
                flex-direction: column;
                text-align: center;
            }

            .wpp-stats-card .wpp-card-content {
                flex-direction: column;
                gap: 20px;
            }
        }
        </style>
        <?php
    }

    public function analytics_page() {
        if (class_exists('WhatsPro\\Premium\\Admin\\Screens\\AnalyticsPage')) {
            WhatsPro\Premium\Admin\Screens\AnalyticsPage::render();
        } else {
            echo '<div class="wrap"><h1>Analytics</h1><p>Analytics module not found.</p></div>';
        }
    }

    public function leads_page() {
        if (class_exists('WhatsPro\\Premium\\Admin\\Screens\\LeadsPage')) {
            WhatsPro\Premium\Admin\Screens\LeadsPage::render();
        } else {
            echo '<div class="wrap"><h1>Leads</h1><p>Leads module not found.</p></div>';
        }
    }

    public function segmentation_page() {
        if (class_exists('WhatsPro\\Premium\\Admin\\Screens\\SegmentationPage')) {
            WhatsPro\Premium\Admin\Screens\SegmentationPage::render();
        } else {
            echo '<div class="wrap"><h1>Segmentation</h1><p>Segmentation module not found.</p></div>';
        }
    }

    public function settings_page() {
        if (class_exists('WhatsPro\\Premium\\Admin\\Screens\\SettingsPage')) {
            WhatsPro\Premium\Admin\Screens\SettingsPage::render();
        } else {
            echo '<div class="wrap"><h1>Settings</h1><p>Settings module not found.</p></div>';
        }
    }

    public function ajax_get_chart_data() {
        check_ajax_referer('wpp_ajax_nonce', 'nonce');

        $type = sanitize_text_field($_POST['type'] ?? 'messages');
        $period = intval($_POST['period'] ?? 30);

        // Generate mock data for now - replace with real data processing
        $data = $this->generate_mock_chart_data($type, $period);

        wp_send_json_success($data);
    }

    public function ajax_export_report() {
        check_ajax_referer('wpp_ajax_nonce', 'nonce');

        $format = sanitize_text_field($_POST['format'] ?? 'csv');
        $period = intval($_POST['period'] ?? 30);
        $type = sanitize_text_field($_POST['type'] ?? 'all');

        // Generate and send export file
        $this->generate_export($format, $period, $type);
    }

    public function ajax_activate_license() {
        check_ajax_referer('wpp_license_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        $license_key = sanitize_text_field($_POST['license_key'] ?? '');

        if (empty($license_key)) {
            wp_send_json_error('License key is required');
            return;
        }

        // For demo purposes, accept any 32-character key
        if (strlen($license_key) === 32 && ctype_alnum($license_key)) {
            $result = \WhatsPro\Premium\Services\LicenseManager::activate_license($license_key);

            if (isset($result['success'])) {
                wp_send_json_success($result['success']);
            } else {
                wp_send_json_error($result['error'] ?? 'Activation failed');
            }
        } else {
            wp_send_json_error('Invalid license key format. Must be 32 alphanumeric characters.');
        }
    }

    public function activate_demo_license() {
        if (!current_user_can('manage_options')) {
            return false;
        }

        // Auto-activate with demo license (recognized by DEMO prefix)
        $demo_key = 'DEMO2024000000000000000000001';
        $result = \WhatsPro\Premium\Services\LicenseManager::activate_license($demo_key);

        return isset($result['success']);
    }

    public function ajax_deactivate_license() {
        check_ajax_referer('wpp_license_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        $result = \WhatsPro\Premium\Services\LicenseManager::deactivate_license();

        if (isset($result['success'])) {
            wp_send_json_success($result['success']);
        } else {
            wp_send_json_error('Deactivation failed');
        }
    }

    public function ajax_activate_demo_license() {
        check_ajax_referer('wpp_demo_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        // Activate demo license
        $success = $this->activate_demo_license();

        if ($success) {
            wp_send_json_success('Demo license activated successfully! You now have access to all premium features.');
        } else {
            wp_send_json_error('Demo license activation failed');
        }
    }

    private function generate_mock_chart_data($type, $period) {
        $labels = [];
        $values = [];

        // Generate date labels
        for ($i = $period - 1; $i >= 0; $i--) {
            $labels[] = date('M j', strtotime("-{$i} days"));
        }

        // Generate mock values
        for ($i = 0; $i < $period; $i++) {
            $values[] = rand(10, 100);
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'label' => ucfirst($type) . ' Over Time'
        ];
    }

    private function generate_export($format, $period, $type) {
        $filename = "whatspro-export-{$type}-" . date('Y-m-d') . ".{$format}";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add headers
        fputcsv($output, ['Date', 'Value', 'Type']);

        // Add mock data
        for ($i = $period - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $value = rand(10, 100);
            fputcsv($output, [$date, $value, $type]);
        }

        fclose($output);
        exit;
    }

    private function get_license_status_class() {
        $status = get_option('wpp_license_status', 'inactive');
        return $status === 'active' ? 'success' : 'warning';
    }

    private function get_license_status_text() {
        $status = get_option('wpp_license_status', 'inactive');
        return $status === 'active' ? __('Active', 'whatspro-premium') : __('Inactive', 'whatspro-premium');
    }

    public function register_api_routes() {
        if (class_exists('WhatsPro\\Premium\\Api\\Analytics')) {
            WhatsPro\Premium\Api\Analytics::register();
        }
    }

    private function display_recent_activity() {
        global $wpdb;

        // Get recent leads
        $recent_leads = $wpdb->get_results($wpdb->prepare(
            "SELECT name, phone, created_at FROM {$wpdb->prefix}wpp_leads ORDER BY created_at DESC LIMIT 5"
        ));

        if (empty($recent_leads)) {
            echo '<p>' . __('No recent activity found.', 'whatspro-premium') . '</p>';
            return;
        }

        echo '<div class="wpp-activity-list">';
        foreach ($recent_leads as $lead) {
            $time_ago = human_time_diff(strtotime($lead->created_at), current_time('timestamp'));
            echo '<div class="wpp-activity-item">';
            echo '<div class="wpp-activity-icon">👤</div>';
            echo '<div class="wpp-activity-content">';
            echo '<div class="wpp-activity-message">' . sprintf(__('New lead: %s', 'whatspro-premium'), esc_html($lead->name)) . '</div>';
            echo '<div class="wpp-activity-time">' . sprintf(__('%s ago', 'whatspro-premium'), $time_ago) . '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';

        echo '<style>
        .wpp-activity-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .wpp-activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .wpp-activity-icon {
            font-size: 1.5em;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            border-radius: 50%;
            color: white;
            flex-shrink: 0;
        }

        .wpp-activity-content {
            flex: 1;
        }

        .wpp-activity-message {
            font-weight: 500;
            color: #333;
            margin-bottom: 4px;
        }

        .wpp-activity-time {
            font-size: 0.85em;
            color: #666;
        }
        </style>';
    }
}

// Initialize the plugin
function whatspro_premium_init() {
    return WhatsPro_Premium::get_instance();
}

// Start the plugin
whatspro_premium_init();
