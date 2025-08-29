<?php
namespace WhatsPro\Premium\Admin\Screens;

use WhatsPro\Premium\Services\Segmentation;
use WhatsPro\Premium\Services\MetaExtractor;

class AnalyticsPage {
    public static function render() {
        // Check license
        if (!\WhatsPro\Premium\Services\LicenseManager::is_valid()) {
            echo '<div class="wrap"><h1>Analytics</h1><div class="card"><div class="premium-badge">Premium Feature</div><p>Please activate your license to access analytics.</p></div></div>';
            return;
        }

        $stats = self::get_analytics_data();
        $segmentation_stats = Segmentation::get_segmentation_stats();
        $meta_analytics = MetaExtractor::get_meta_analytics(30);

        ?>
        <div class="wrap">
            <div class="analytics-header">
                <h1>Professional Analytics Dashboard</h1>
                <div class="analytics-controls">
                    <select id="analytics-period" class="wpp-select">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="365">Last Year</option>
                    </select>
                    <label class="realtime-toggle">
                        <input type="checkbox" id="realtime-toggle">
                        <span class="toggle-slider"></span>
                        Real-time Updates
                    </label>
                    <button id="refresh-analytics" class="wpp-btn wpp-btn-secondary">
                        <svg viewBox="0 0 24 24" width="16" height="16">
                            <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Status Indicators -->
            <div class="status-indicators">
                <div id="realtime-indicator" class="status-item">Real-time: Disabled</div>
                <div id="analytics-loader" class="status-item loading" style="display: none;">
                    <div class="wpp-spinner"></div>
                    Loading data...
                </div>
                <div id="analytics-error" class="status-item error" style="display: none;"></div>
            </div>

            <!-- Key Metrics Overview -->
            <div class="metrics-overview">
                <div class="metric-card">
                    <div class="metric-icon">📊</div>
                    <div class="metric-content">
                        <div class="metric-value" id="total-leads"><?php echo number_format($meta_analytics['total_leads']); ?></div>
                        <div class="metric-label">Total Leads</div>
                        <div class="metric-change positive">+12.5%</div>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">🌍</div>
                    <div class="metric-content">
                        <div class="metric-value" id="unique-cities"><?php echo $meta_analytics['unique_cities']; ?></div>
                        <div class="metric-label">Cities Reached</div>
                        <div class="metric-change positive">+8.2%</div>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">📱</div>
                    <div class="metric-content">
                        <div class="metric-value" id="unique-sources"><?php echo $meta_analytics['unique_sources']; ?></div>
                        <div class="metric-label">Lead Sources</div>
                        <div class="metric-change positive">+15.3%</div>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">⚡</div>
                    <div class="metric-content">
                        <div class="metric-value" id="realtime-recent_leads">0</div>
                        <div class="metric-label">Recent Leads (1h)</div>
                        <div class="metric-change neutral">Live</div>
                    </div>
                </div>
            </div>

            <!-- Main Chart Section -->
            <div class="analytics-section">
                <div class="section-header">
                    <h2>Performance Overview</h2>
                    <div class="chart-controls">
                        <select id="chart-type" class="wpp-select">
                            <option value="messages">Messages Over Time</option>
                            <option value="leads">Leads Over Time</option>
                            <option value="geographic">Geographic Trends</option>
                            <option value="sources">Source Performance</option>
                        </select>
                    </div>
                </div>
                <div class="chart-container main-chart">
                    <canvas id="main-analytics-chart"></canvas>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <!-- Geographic Distribution -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Geographic Distribution</h3>
                        <div class="chart-info">Where your leads come from</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="geographic-chart"></canvas>
                    </div>
                </div>

                <!-- Lead Sources -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Lead Sources</h3>
                        <div class="chart-info">How leads find you</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="sources-chart"></canvas>
                    </div>
                </div>

                <!-- Name Patterns -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Name Analysis</h3>
                        <div class="chart-info">Lead name patterns</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="name-patterns-chart"></canvas>
                    </div>
                </div>

                <!-- Top Referrers -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Top Referrers</h3>
                        <div class="chart-info">Traffic sources</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="referrers-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Conversion Funnel -->
            <div class="analytics-section">
                <div class="section-header">
                    <h2>Conversion Funnel</h2>
                    <div class="funnel-info">Lead journey from first contact to conversion</div>
                </div>
                <div class="funnel-container">
                    <canvas id="conversion-funnel"></canvas>
                </div>
            </div>

            <!-- Meta Data Insights -->
            <div class="analytics-section">
                <div class="section-header">
                    <h2>Meta Data Insights</h2>
                    <div class="meta-info">Detailed information about your leads</div>
                </div>
                <div class="meta-insights-grid">
                    <div class="meta-card">
                        <h4>Recent Leads</h4>
                        <div class="meta-list">
                            <?php
                            $recent_meta = MetaExtractor::get_recent_meta();
                            foreach (array_slice($recent_meta, 0, 5) as $lead):
                            ?>
                                <div class="meta-item">
                                    <div class="meta-avatar"><?php echo strtoupper(substr($lead->name, 0, 1)); ?></div>
                                    <div class="meta-details">
                                        <div class="meta-name"><?php echo esc_html($lead->name); ?></div>
                                        <div class="meta-location"><?php echo esc_html($lead->city); ?> • <?php echo esc_html($lead->source); ?></div>
                                        <div class="meta-time"><?php echo human_time_diff(strtotime($lead->created_at)); ?> ago</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="meta-card">
                        <h4>Source Breakdown</h4>
                        <div class="source-breakdown">
                            <?php foreach ($meta_analytics['sources'] as $source): ?>
                                <div class="source-item">
                                    <div class="source-name"><?php echo esc_html($source->source); ?></div>
                                    <div class="source-count"><?php echo $source->count; ?> leads</div>
                                    <div class="source-bar">
                                        <div class="source-fill" style="width: <?php echo $meta_analytics['total_leads'] > 0 ? ($source->count / $meta_analytics['total_leads']) * 100 : 0; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="meta-card">
                        <h4>Geographic Insights</h4>
                        <div class="geo-insights">
                            <?php foreach (array_slice($meta_analytics['geographic'], 0, 5) as $geo): ?>
                                <div class="geo-item">
                                    <div class="geo-city"><?php echo esc_html($geo->city); ?></div>
                                    <div class="geo-count"><?php echo $geo->count; ?> leads</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export & Actions -->
            <div class="analytics-section">
                <div class="section-header">
                    <h2>Export & Reports</h2>
                    <div class="export-description">Download your analytics data in various formats</div>
                </div>
                <div class="export-actions">
                    <button class="wpp-btn export-btn" data-format="csv">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        Export CSV
                    </button>
                    <button class="wpp-btn export-btn" data-format="json">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path d="M5,3C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3H5M5,5H19V19H5V5Z"/>
                        </svg>
                        Export JSON
                    </button>
                    <button class="wpp-btn wpp-btn-secondary" onclick="refreshAnalytics()">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                        </svg>
                        Generate Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Load Analytics JavaScript -->
        <script src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/analytics.js'; ?>"></script>

        <style>
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .analytics-header h1 {
            margin: 0;
            color: #333;
            font-size: 2.2em;
            font-weight: 700;
        }

        .analytics-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .realtime-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 500;
            color: #666;
        }

        .toggle-slider {
            position: relative;
            width: 44px;
            height: 24px;
            background: #ccc;
            border-radius: 24px;
            transition: background 0.3s;
        }

        .toggle-slider:before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        #realtime-toggle:checked + .toggle-slider {
            background: #FFD700;
        }

        #realtime-toggle:checked + .toggle-slider:before {
            transform: translateX(20px);
        }

        .status-indicators {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .status-item {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-item.loading {
            background: rgba(255, 215, 0, 0.1);
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-item.error {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .metrics-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .metric-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .metric-icon {
            font-size: 2.5em;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            border-radius: 12px;
            color: white;
        }

        .metric-content {
            flex: 1;
        }

        .metric-value {
            font-size: 2.2em;
            font-weight: 700;
            color: #333;
            margin-bottom: 4px;
        }

        .metric-label {
            font-size: 0.9em;
            color: #666;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .metric-change {
            font-size: 0.8em;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 12px;
            display: inline-block;
        }

        .metric-change.positive {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .metric-change.negative {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .metric-change.neutral {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .analytics-section {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .section-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.5em;
            font-weight: 600;
        }

        .chart-controls {
            display: flex;
            gap: 12px;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }

        .chart-header {
            margin-bottom: 20px;
        }

        .chart-header h3 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 1.2em;
            font-weight: 600;
        }

        .chart-info {
            color: #666;
            font-size: 0.9em;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .main-chart {
            height: 400px;
        }

        .funnel-container {
            position: relative;
            height: 350px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .meta-insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        .meta-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e9ecef;
        }

        .meta-card h4 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 1.1em;
            font-weight: 600;
        }

        .meta-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .meta-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1em;
        }

        .meta-details {
            flex: 1;
        }

        .meta-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .meta-location {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 4px;
        }

        .meta-time {
            font-size: 0.8em;
            color: #999;
        }

        .source-breakdown {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .source-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .source-name {
            font-weight: 600;
            color: #333;
            font-size: 0.9em;
        }

        .source-count {
            font-size: 0.8em;
            color: #666;
        }

        .source-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .source-fill {
            height: 100%;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .geo-insights {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .geo-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .geo-city {
            font-weight: 500;
            color: #333;
        }

        .geo-count {
            font-size: 0.9em;
            color: #666;
        }

        .export-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .export-btn {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .export-btn svg {
            fill: #333;
        }

        @media (max-width: 768px) {
            .analytics-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .analytics-controls {
                flex-wrap: wrap;
                width: 100%;
            }

            .metrics-overview {
                grid-template-columns: 1fr;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .meta-insights-grid {
                grid-template-columns: 1fr;
            }

            .analytics-section {
                padding: 20px;
            }

            .export-actions {
                flex-direction: column;
            }
        }
        </style>
        <?php
    }

    private static function get_analytics_data() {
        global $wpdb;

        // Mock data - in production, this would query actual tables
        $messages_table = $wpdb->prefix . 'wpp_messages';
        $leads_table = $wpdb->prefix . 'wpp_leads';

        // Get total messages
        $total_messages = $wpdb->get_var("SELECT COUNT(*) FROM $messages_table") ?: 0;

        // Get total leads
        $total_leads = $wpdb->get_var("SELECT COUNT(*) FROM $leads_table") ?: 0;

        // Calculate conversion rate (leads from messages)
        $conversion_rate = $total_messages > 0 ? ($total_leads / $total_messages) * 100 : 0;

        // Mock changes (in production, compare with previous period)
        $messages_change = rand(-10, 20);
        $leads_change = rand(-5, 15);
        $conversion_change = rand(-8, 12);
        $response_change = rand(-15, -5); // Negative is good for response time

        // Average response time (mock)
        $avg_response_time = rand(2, 8);

        return [
            'total_messages' => $total_messages,
            'total_leads' => $total_leads,
            'conversion_rate' => $conversion_rate,
            'avg_response_time' => $avg_response_time,
            'messages_change' => $messages_change,
            'leads_change' => $leads_change,
            'conversion_change' => $conversion_change,
            'response_change' => $response_change
        ];
    }

    private static function get_recent_activity() {
        // Mock recent activity - in production, query actual activity logs
        return [
            [
                'icon' => '📱',
                'message' => 'New lead from WhatsApp: John Doe',
                'time' => '2 minutes ago'
            ],
            [
                'icon' => '📊',
                'message' => 'Campaign "Summer Sale" sent to 150 contacts',
                'time' => '15 minutes ago'
            ],
            [
                'icon' => '📈',
                'message' => 'Message open rate increased by 12%',
                'time' => '1 hour ago'
            ],
            [
                'icon' => '🏷️',
                'message' => 'Lead tagged as "High Priority"',
                'time' => '2 hours ago'
            ],
            [
                'icon' => '📋',
                'message' => 'Excel file imported: 75 new leads',
                'time' => '3 hours ago'
            ]
        ];
    }
}