<?php
namespace WhatsPro\Premium\Api;

use WhatsPro\Premium\Services\MetaExtractor;

class Analytics {
    public static function register() {
        register_rest_route('whatspro/v1', '/analytics/ping', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'ping'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('whatspro/v1', '/analytics/meta-summary', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_meta_summary'],
            'permission_callback' => [__CLASS__, 'check_permissions']
        ]);

        register_rest_route('whatspro/v1', '/analytics/geographic', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_geographic_data'],
            'permission_callback' => [__CLASS__, 'check_permissions']
        ]);

        register_rest_route('whatspro/v1', '/analytics/sources', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_sources_data'],
            'permission_callback' => [__CLASS__, 'check_permissions']
        ]);

        register_rest_route('whatspro/v1', '/analytics/realtime', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_realtime_data'],
            'permission_callback' => [__CLASS__, 'check_permissions']
        ]);

        register_rest_route('whatspro/v1', '/analytics/chart-data', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_chart_data'],
            'permission_callback' => [__CLASS__, 'check_permissions']
        ]);

        register_rest_route('whatspro/v1', '/analytics/export', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'export_analytics'],
            'permission_callback' => [__CLASS__, 'check_permissions']
        ]);
    }

    public static function ping() {
        return ['ok' => true, 'timestamp' => current_time('mysql')];
    }

    public static function check_permissions() {
        return current_user_can('manage_options') && \WhatsPro\Premium\Services\LicenseManager::is_valid();
    }

    public static function get_meta_summary($request) {
        $period = $request->get_param('period') ?: 30;
        $data = MetaExtractor::get_meta_analytics($period);

        return [
            'success' => true,
            'data' => $data,
            'period' => $period,
            'timestamp' => current_time('mysql')
        ];
    }

    public static function get_geographic_data($request) {
        $period = $request->get_param('period') ?: 30;
        $analytics = MetaExtractor::get_meta_analytics($period);

        // Format for geographic visualization
        $formatted_data = [
            'labels' => array_column($analytics['geographic'], 'city'),
            'values' => array_column($analytics['geographic'], 'count'),
            'total' => $analytics['total_leads'],
            'unique_cities' => $analytics['unique_cities']
        ];

        return [
            'success' => true,
            'data' => $formatted_data,
            'period' => $period
        ];
    }

    public static function get_sources_data($request) {
        $period = $request->get_param('period') ?: 30;
        $analytics = MetaExtractor::get_meta_analytics($period);

        // Format for source visualization
        $formatted_data = [
            'labels' => array_column($analytics['sources'], 'source'),
            'values' => array_column($analytics['sources'], 'count'),
            'total' => $analytics['total_leads'],
            'unique_sources' => $analytics['unique_sources']
        ];

        return [
            'success' => true,
            'data' => $formatted_data,
            'period' => $period
        ];
    }

    public static function get_realtime_data($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpp_leads';

        // Get data from last hour
        $hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $recent_leads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE created_at >= %s",
            $hour_ago
        ));

        $recent_messages = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wpp_messages WHERE created_at >= %s",
            $hour_ago
        ));

        return [
            'success' => true,
            'data' => [
                'recent_leads' => (int) $recent_leads,
                'recent_messages' => (int) $recent_messages,
                'timestamp' => current_time('mysql')
            ]
        ];
    }

    public static function get_chart_data($request) {
        $type = $request->get_param('type') ?: 'messages';
        $period = $request->get_param('period') ?: 7;

        $data = self::generate_chart_data($type, $period);

        return [
            'success' => true,
            'data' => $data,
            'type' => $type,
            'period' => $period
        ];
    }

    private static function generate_chart_data($type, $period) {
        global $wpdb;

        $date_format = $period <= 7 ? '%Y-%m-%d %H:00:00' : '%Y-%m-%d';
        $interval = $period <= 7 ? 'HOUR' : 'DAY';

        $start_date = date('Y-m-d H:i:s', strtotime("-{$period} days"));

        switch ($type) {
            case 'messages':
                $table = $wpdb->prefix . 'wpp_messages';
                $query = $wpdb->prepare(
                    "SELECT DATE_FORMAT(created_at, %s) as date, COUNT(*) as count
                     FROM $table WHERE created_at >= %s
                     GROUP BY date ORDER BY date",
                    $date_format, $start_date
                );
                $label = 'Messages';
                break;

            case 'leads':
                $table = $wpdb->prefix . 'wpp_leads';
                $query = $wpdb->prepare(
                    "SELECT DATE_FORMAT(created_at, %s) as date, COUNT(*) as count
                     FROM $table WHERE created_at >= %s
                     GROUP BY date ORDER BY date",
                    $date_format, $start_date
                );
                $label = 'Leads';
                break;

            case 'geographic':
                $table = $wpdb->prefix . 'wpp_leads';
                $query = $wpdb->prepare(
                    "SELECT city as date, COUNT(*) as count
                     FROM $table WHERE created_at >= %s AND city != ''
                     GROUP BY city ORDER BY count DESC LIMIT 10",
                    $start_date
                );
                $label = 'Leads by City';
                break;

            case 'sources':
                $table = $wpdb->prefix . 'wpp_leads';
                $query = $wpdb->prepare(
                    "SELECT source as date, COUNT(*) as count
                     FROM $table WHERE created_at >= %s AND source != ''
                     GROUP BY source ORDER BY count DESC",
                    $start_date
                );
                $label = 'Leads by Source';
                break;

            default:
                return ['labels' => [], 'values' => [], 'label' => 'Unknown'];
        }

        $results = $wpdb->get_results($query);

        return [
            'labels' => array_column($results, 'date'),
            'values' => array_column($results, 'count'),
            'label' => $label
        ];
    }

    public static function export_analytics($request) {
        $format = $request->get_param('format') ?: 'csv';
        $period = $request->get_param('period') ?: 30;
        $type = $request->get_param('type') ?: 'all';

        $data = MetaExtractor::get_meta_analytics($period);

        switch ($format) {
            case 'csv':
                return self::export_csv($data, $type);
            case 'json':
                return self::export_json($data);
            default:
                return ['error' => 'Unsupported format'];
        }
    }

    private static function export_csv($data, $type) {
        $filename = "whatspro-analytics-{$type}-" . date('Y-m-d') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add headers
        fputcsv($output, ['Metric', 'Value', 'Details']);

        // Add data based on type
        switch ($type) {
            case 'geographic':
                fputcsv($output, ['Total Leads', $data['total_leads'], '']);
                fputcsv($output, ['Unique Cities', $data['unique_cities'], '']);
                fputcsv($output, ['', '', '']);
                fputcsv($output, ['City', 'Count', 'Percentage']);
                foreach ($data['geographic'] as $item) {
                    $percentage = $data['total_leads'] > 0 ? round(($item->count / $data['total_leads']) * 100, 2) : 0;
                    fputcsv($output, [$item->city, $item->count, $percentage . '%']);
                }
                break;

            case 'sources':
                fputcsv($output, ['Total Leads', $data['total_leads'], '']);
                fputcsv($output, ['Unique Sources', $data['unique_sources'], '']);
                fputcsv($output, ['', '', '']);
                fputcsv($output, ['Source', 'Count', 'Percentage']);
                foreach ($data['sources'] as $item) {
                    $percentage = $data['total_leads'] > 0 ? round(($item->count / $data['total_leads']) * 100, 2) : 0;
                    fputcsv($output, [$item->source, $item->count, $percentage . '%']);
                }
                break;

            default:
                fputcsv($output, ['Total Leads', $data['total_leads'], '']);
                fputcsv($output, ['Unique Cities', $data['unique_cities'], '']);
                fputcsv($output, ['Unique Sources', $data['unique_sources'], '']);
        }

        fclose($output);
        exit;
    }

    private static function export_json($data) {
        $filename = "whatspro-analytics-" . date('Y-m-d') . ".json";

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo json_encode([
            'export_date' => date('Y-m-d H:i:s'),
            'data' => $data
        ], JSON_PRETTY_PRINT);
        exit;
    }
}